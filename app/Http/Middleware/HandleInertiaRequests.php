<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'fullname' => $this->getUserFullname($request),
            // Share Laravel session flash data with Inertia so controllers can use back()->with('success', 'message')
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * Get the full name of the authenticated user
     */
    private function getUserFullname(Request $request): string
    {
        $user = $request->user();
        if (!$user) {
            return '';
        }
        
        return FullnameCapital($user->firstname ?? '', $user->lastname ?? '');
    }
}
