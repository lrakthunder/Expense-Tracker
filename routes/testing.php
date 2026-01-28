<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// Only register these test helpers in the local environment
if (! app()->environment('local')) {
    return;
}

/**
 * Helper to quickly validate DB connectivity and return a JSON 503 if unreachable.
 */
$checkDb = function () {
    try {
        DB::connection()->getPdo();
    } catch (\Throwable $e) {
        logger()->warning('Testing route aborted: DB not available - '.$e->getMessage());
        return response()->json(['error' => 'database unavailable'], 503);
    }
    return null;
};

/**
 * Helper to ensure default categories exist for a user
 */
$ensureDefaultCategories = function ($user) {
    $defaultExpenseCategories = ['Food', 'Transport', 'Utilities'];
    $defaultIncomeCategories = ['Salary', 'Interest'];
    
    foreach ($defaultExpenseCategories as $categoryName) {
        \App\Models\Category::firstOrCreate(
            ['name' => $categoryName, 'type' => 'expense', 'created_by' => $user->id],
            ['name' => $categoryName, 'type' => 'expense', 'created_by' => $user->id]
        );
    }
    
    foreach ($defaultIncomeCategories as $categoryName) {
        \App\Models\Category::firstOrCreate(
            ['name' => $categoryName, 'type' => 'income', 'created_by' => $user->id],
            ['name' => $categoryName, 'type' => 'income', 'created_by' => $user->id]
        );
    }
};

// Test-only login: creates the test user and logs them in
Route::post('/test/login', function () use ($checkDb, $ensureDefaultCategories) {
    if ($resp = $checkDb()) return $resp;
    $user = \App\Models\User::where('email', 'testuser@example.com')->firstOrCreate(
        ['email' => 'testuser@example.com'],
        [
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser',
            'password' => bcrypt('TestPass_123'),
        ]
    );
    
    // Ensure default categories exist for this user
    $ensureDefaultCategories($user);
    
    auth()->login($user);
    return response()->json(['authenticated' => true, 'user' => $user]);
})->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

// Create user without logging in (for UI login checks)
Route::post('/test/create-user', function () use ($checkDb, $ensureDefaultCategories) {
    if ($resp = $checkDb()) return $resp;
    $user = \App\Models\User::where('email', 'testuser@example.com')->firstOrCreate(
        ['email' => 'testuser@example.com'],
        [
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser',
            'password' => bcrypt('TestPass_123'),
        ]
    );
    
    // Ensure default categories exist for this user
    $ensureDefaultCategories($user);
    
    return response()->json(['created' => true, 'user' => $user]);
})->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

// Fetch user info for UI prefill (agreed_to_terms)
Route::get('/test/user', function () use ($checkDb) {
    if ($resp = $checkDb()) return $resp;
    $login = request()->query('email') ?: request()->query('username');
    if (! $login) {
        return response()->json(['error' => 'missing email or username'], 400);
    }
    
    // Check if the input is an email or username
    $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    $user = \App\Models\User::where($fieldType, $login)->first();
    
    if (! $user) {
        return response()->json(null, 404);
    }
    return response()->json([
        'email' => $user->email,
        'username' => $user->username,
        'agreed_to_terms' => (bool) $user->agreed_to_terms,
        'agreed_at' => $user->agreed_at,
    ]);
});

// Test-only endpoints for getting last created income/expense
Route::get('/test/incomes/last', function () use ($checkDb) {
    if ($resp = $checkDb()) return $resp;
    return \App\Models\Income::latest('created_at')->first();
});

Route::get('/test/expenses/last', function () use ($checkDb) {
    if ($resp = $checkDb()) return $resp;
    return \App\Models\Expense::latest('created_at')->first();
});

// Create clean test user and login
Route::post('/test/create-clean-user', function () use ($checkDb, $ensureDefaultCategories) {
    if ($resp = $checkDb()) return $resp;
    $email = request()->input('email', 'cleanuser@test.local');
    $name = request()->input('name', 'Clean Test User');
    
    // Delete existing user with this email if present
    $existing = \App\Models\User::where('email', $email)->first();
    if ($existing) {
        \DB::table('expenses')->where('client_id', $existing->id)->delete();
        \DB::table('incomes')->where('client_id', $existing->id)->delete();
        \DB::table('categories')->where('created_by', $existing->id)->delete();
        $existing->delete();
    }
    
    // Parse name into first_name and last_name
    $nameParts = explode(' ', trim($name), 2);
    $firstName = $nameParts[0] ?? 'Clean';
    $lastName = $nameParts[1] ?? 'User';
    $username = 'clean_' . time();
    
    $user = \App\Models\User::create([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'username' => $username,
        'email' => $email,
        'password' => bcrypt('TestPass_123'),
    ]);
    
    $ensureDefaultCategories($user);
    auth()->login($user);
    
    return response()->json(['authenticated' => true, 'user' => $user]);
})->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

// Cleanup endpoints
Route::post('/test/cleanup/income', function () use ($checkDb) {
    if ($resp = $checkDb()) return $resp;
    // Safety: only allow explicit opt-in via env and only delete incomes for the test user
    if (! env('ALLOW_TEST_CLEANUP', false)) {
        return response()->json(['deleted' => false, 'reason' => 'cleanup_disabled'], 403);
    }
    $testEmail = 'lrakthunder@gmail.com';
    $testUser = \App\Models\User::where('email', $testEmail)->first();
    if (! $testUser) return response()->json(['deleted' => false, 'reason' => 'test_user_not_found']);
    $cacheKey = "test_cleanup_income_done_{$testUser->id}";
    if (Cache::get($cacheKey)) {
        return response()->json(['deleted' => false, 'reason' => 'already_run']);
    }

    $inc = \App\Models\Income::where('client_id', $testUser->id)->latest('created_at')->first();
    if ($inc) {
        $deletedId = $inc->id;
        $inc->delete();
        Cache::put($cacheKey, true, 3600);
        return response()->json(['deleted' => true, 'id' => $deletedId]);
    }
    return response()->json(['deleted' => false]);
})->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

Route::post('/test/cleanup/expense', function () use ($checkDb) {
    if ($resp = $checkDb()) return $resp;
    // Safety: only allow explicit opt-in via env and only delete expenses for the test user
    if (! env('ALLOW_TEST_CLEANUP', false)) {
        return response()->json(['deleted' => false, 'reason' => 'cleanup_disabled'], 403);
    }
    $testEmail = 'lrakthunder@gmail.com';
    $testUser = \App\Models\User::where('email', $testEmail)->first();
    if (! $testUser) return response()->json(['deleted' => false, 'reason' => 'test_user_not_found']);
    $cacheKey = "test_cleanup_expense_done_{$testUser->id}";
    if (Cache::get($cacheKey)) {
        return response()->json(['deleted' => false, 'reason' => 'already_run']);
    }

    $exp = \App\Models\Expense::where('client_id', $testUser->id)->latest('created_at')->first();
    if ($exp) {
        $deletedId = $exp->id;
        $exp->delete();
        Cache::put($cacheKey, true, 3600);
        return response()->json(['deleted' => true, 'id' => $deletedId]);
    }
    return response()->json(['deleted' => false]);
})->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

Route::post('/test/cleanup/user', function () use ($checkDb) {
    if ($resp = $checkDb()) return $resp;
    // Safety: only allow explicit opt-in via env and only run once
    if (! env('ALLOW_TEST_CLEANUP', false)) {
        return response()->json(['deleted' => false, 'reason' => 'cleanup_disabled'], 403);
    }
    $email = request()->input('email', 'lrakthunder@gmail.com');
    $user = \App\Models\User::where('email', $email)->first();
    if (! $user) return response()->json(['deleted' => false, 'reason' => 'not_found']);
    $cacheKey = "test_cleanup_user_done_{$user->id}";
    if (Cache::get($cacheKey)) {
        return response()->json(['deleted' => false, 'reason' => 'already_run']);
    }

    if (auth()->check() && auth()->id() === $user->id) {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
    }
    $user->delete();
    Cache::put($cacheKey, true, 3600);
    return response()->json(['deleted' => true]);
    
    return response()->json(['deleted' => false]);
})->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

// Debug: Check what categories exist for authenticated user
Route::get('/test/debug/categories', function () use ($checkDb) {
    if ($resp = $checkDb()) return $resp;
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'not_authenticated'], 401);
    }
    
    $expenseCategories = \App\Models\Category::where('created_by', $user->id)
        ->where('type', 'expense')
        ->orderBy('name')
        ->pluck('name')
        ->toArray();
    $expenseCategories[] = 'Other';

    $incomeCategories = \App\Models\Category::where('created_by', $user->id)
        ->where('type', 'income')
        ->orderBy('name')
        ->pluck('name')
        ->toArray();
    $incomeCategories[] = 'Other';

    return response()->json([
        'user_id' => $user->id,
        'user_email' => $user->email,
        'expense_categories' => $expenseCategories,
        'income_categories' => $incomeCategories,
        'total_expense_count' => count($expenseCategories),
        'total_income_count' => count($incomeCategories),
    ]);
});

