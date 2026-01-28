<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard now handled by ExpenseController@index (dashboard shows current summary)
    Route::get('/dashboard', [ExpenseController::class, 'index'])
        ->name('dashboard');

    Route::get('/report', fn () => Inertia::render('Report'))
        ->name('report');
    Route::post('/report/generate', [ReportController::class, 'generate'])
        ->name('report.generate');

    Route::get('/calendar', fn () => Inertia::render('Calendar'))
        ->name('calendar');

    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings');

    // Category management for expense/income types
    Route::get('/categories', [CategoryController::class, 'index'])
        ->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])
        ->name('categories.store');
    Route::patch('/categories/{id}', [CategoryController::class, 'update'])
        ->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])
        ->name('categories.destroy');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::get('/edit-profile', [ProfileController::class, 'editProfile'])
        ->name('profile.edit-profile');

    Route::get('/change-password', [ProfileController::class, 'changePassword'])
        ->name('profile.change-password');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    // Save theme preference
    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])
        ->name('profile.theme');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Expenses & Income
    |--------------------------------------------------------------------------
    */

    // NOTE: GET /expense will be reserved later for the entries list. Keep POST /expense for store.

    Route::post('/expense', [ExpenseController::class, 'store'])
        ->name('expense.store');

    // Bulk delete selected expenses (ids[])
    Route::post('/expense/bulk-delete', [ExpenseController::class, 'bulkDelete'])
        ->name('expense.bulkDelete');

    Route::patch('/expense/{id}', [ExpenseController::class, 'update'])
        ->name('expense.update');

    // Public UI pages for expense and income lists
    Route::get('/expense', [\App\Http\Controllers\ExpenseController::class, 'list'])
        ->name('expense');

    Route::get('/income', [\App\Http\Controllers\IncomeController::class, 'list'])
        ->name('income');

    Route::post('/income', [IncomeController::class, 'store'])
        ->name('income.store');
    
    // Bulk delete selected incomes (ids[])
    Route::post('/income/bulk-delete', [IncomeController::class, 'bulkDelete'])
        ->name('income.bulkDelete');

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/api/daily-trend', [ReportController::class, 'dailyTrend'])
        ->name('api.daily-trend');
});

require __DIR__.'/auth.php';

// Load local-only testing helpers (kept separate for safety)
if (app()->environment('local')) {
    require __DIR__.'/testing.php';
}

Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    return 'Laravel caches cleared!';
});