<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse|Response
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create default categories for new user
        $this->createDefaultCategories($user);

        event(new Registered($user));

        // Redirect to login with success flash message (green toast via Inertia flash)
        return redirect(route('login'))->with('success', 'Registration successful! Please log in.');
    }

    /**
     * Create default categories for a new user.
     */
    private function createDefaultCategories(User $user): void
    {
        $defaultExpenseCategories = ['Food', 'Transport', 'Utilities'];
        $defaultIncomeCategories = ['Salary', 'Interest'];

        foreach ($defaultExpenseCategories as $categoryName) {
            Category::create([
                'name' => $categoryName,
                'type' => 'expense',
                'created_by' => $user->id,
            ]);
        }

        foreach ($defaultIncomeCategories as $categoryName) {
            Category::create([
                'name' => $categoryName,
                'type' => 'income',
                'created_by' => $user->id,
            ]);
        }
    }
}
