<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Render Settings with user-specific category data.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $fullname = '';
        if ($user) {
            $fullname = FullnameCapital($user->firstname ?? '', $user->lastname ?? '');
        }

        $expenseCategories = [];
        $incomeCategories = [];

        if ($user) {
            $expenseCategories = Category::where('created_by', $user->id)
                ->where('type', 'expense')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'name', 'type', 'created_at']);

            $incomeCategories = Category::where('created_by', $user->id)
                ->where('type', 'income')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'name', 'type', 'created_at']);
        }

        return Inertia::render('Settings', [
            'user' => $user ? $user->toArray() : null,
            'fullname' => $fullname,
            'expenseCategories' => $expenseCategories,
            'incomeCategories' => $incomeCategories,
        ]);
    }
}
