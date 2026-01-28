<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $fullname = '';
        if ($user) {
            $fullname = FullnameCapital($user->firstname ?? '', $user->lastname ?? '');
        }
        // aggregate expenses by category and by month for dashboard charts (only for the current client)
        if (!$user) {
            $expensesByCategory = [];
            $monthly = [];
        } else {
            try {
                $expensesByCategory = \DB::table('expenses')
                    ->where('client_id', $user->id)
                    ->select('category', \DB::raw('SUM(amount) as total'))
                    ->groupBy('category')
                    ->pluck('total', 'category')
                    ->toArray();

                // normalize category keys (parse JSON arrays, strip surrounding quotes/brackets/spaces)
                $normalized = [];
                foreach ($expensesByCategory as $cat => $total) {
                    $label = $this->normalizeCategoryLabel($cat);
                    // aggregate totals for normalized labels
                    if (!isset($normalized[$label])) $normalized[$label] = 0.0;
                    $normalized[$label] += (float) $total;
                }
                $expensesByCategory = $normalized;

                $monthly = \DB::table('expenses')
                    ->where('client_id', $user->id)
                    ->select(\DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as month"), \DB::raw('SUM(amount) as total'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->map(function ($r) { return ['month' => $r->month, 'total' => (float) $r->total]; })
                    ->toArray();
                // compute dashboard stats
                $today = now()->toDateString();
                $startOfMonth = now()->startOfMonth()->toDateString();
                $endOfMonth = now()->endOfMonth()->toDateString();
                $startOfLastMonth = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
                $endOfLastMonth = now()->subMonthNoOverflow()->endOfMonth()->toDateString();

                $todaySpending = (float) \DB::table('expenses')
                    ->where('client_id', $user->id)
                    ->whereDate('transaction_date', $today)
                    ->sum('amount');

                $thisMonthSpending = (float) \DB::table('expenses')
                    ->where('client_id', $user->id)
                    ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                    ->sum('amount');

                $lastMonthSpending = (float) \DB::table('expenses')
                    ->where('client_id', $user->id)
                    ->whereBetween('transaction_date', [$startOfLastMonth, $endOfLastMonth])
                    ->sum('amount');

                // compute overall income and expenses for this client (overall remaining budget)
                $totalIncome = (float) \DB::table('incomes')
                    ->where('client_id', $user->id)
                    ->sum('amount');

                $totalExpenses = (float) \DB::table('expenses')
                    ->where('client_id', $user->id)
                    ->sum('amount');

                $remainingBudget = $totalIncome - $totalExpenses; // overall income - expenses
                // Show change versus last month: positive means this month is higher than last month
                $comparedToLastMonth = $thisMonthSpending - $lastMonthSpending; // thismonth - lastmonth
            } catch (\Exception $e) {
                // if DB not available or error, provide empty data to avoid breaking the view
                $expensesByCategory = [];
                $monthly = [];
                $todaySpending = 0;
                $thisMonthSpending = 0;
                $remainingBudget = 0;
                $comparedToLastMonth = 0;
            }
        }

        // Get user's expense categories for add expense modal
        $expenseCategories = [];
        if ($user) {
            $expenseCategories = Category::where('created_by', $user->id)
                ->where('type', 'expense')
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
            // Always append "Other" at the end
            $expenseCategories[] = 'Other';
        }

        // Get user's income categories for add income modal
        $incomeCategories = [];
        if ($user) {
            $incomeCategories = Category::where('created_by', $user->id)
                ->where('type', 'income')
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
            // Always append "Other" at the end
            $incomeCategories[] = 'Other';
        }

        return Inertia::render('Dashboard', [
            'user' => $user ? $user->toArray() : null,
            'fullname' => $fullname,
            'expensesByCategory' => $expensesByCategory,
            'expenseCategories' => $expenseCategories,
            'incomeCategories' => $incomeCategories,
            'monthlyExpenses' => $monthly,
            'stats' => [
                'today' => $todaySpending ?? 0,
                'thisMonth' => $thisMonthSpending ?? 0,
                'remainingBudget' => $remainingBudget ?? 0,
                'comparedToLastMonth' => $comparedToLastMonth ?? 0,
            ],
        ]);
    }

    /**
     * Normalize a category value from the DB into a clean label string.
     * Handles JSON-encoded arrays like '["Other"]', plain quoted strings, and arrays.
     */
    private function normalizeCategoryLabel($cat)
    {
        if ($cat === null) return '-';
        // if it's already an array (unlikely from DB), join
        if (is_array($cat)) {
            $s = implode(', ', $cat);
            return trim($s) === '' ? '-' : $s;
        }

        // try to decode JSON string (arrays or values)
        if (is_string($cat)) {
            $trimmed = trim($cat);
            // attempt JSON decode if looks like JSON array/object
            if ((str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) || (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}'))) {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (is_array($decoded)) {
                        return trim(implode(', ', $decoded)) ?: '-';
                    }
                    return (string) $decoded ?: '-';
                }
            }

            // remove surrounding quotes/brackets and extra characters
            $s = trim($trimmed, " \t\n\r\0\x0B'\"[]");
            $s = preg_replace('/[^A-Za-z0-9\-, ]+/', '', $s);
            $s = preg_replace('/\s+/', ' ', $s);
            $s = trim($s);
            return $s === '' ? '-' : $s;
        }

        // fallback to string cast
        $s = trim((string) $cat);
        $s = preg_replace('/[^A-Za-z0-9\-, ]+/', '', $s);
        return $s === '' ? '-' : $s;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'category' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $expense = \App\Models\Expense::create([
            'name' => $data['name'] ?? null,
            'amount' => $data['amount'],
            'client_id' => $user ? $user->id : null,
            // category column is JSON in the DB; store as JSON array for consistency
            'category' => json_encode([$data['category']]),
            'transaction_date' => $data['date'],
            'note' => $data['note'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json($expense, 201);
        }

        return back()->with('success', 'Expense added');
    }

    /**
     * Render the expense list page with the user's expenses.
     */
    public function list(Request $request)
    {
        $user = $request->user();
        $fullname = '';
        if ($user) {
            $fullname = FullnameCapital($user->firstname ?? '', $user->lastname ?? '');
        }
        $expenses = [];
        if ($user) {
            try {
                // totals
                $totalExpenses = (float) \DB::table('expenses')
                    ->where('client_id', $user->id)
                    ->sum('amount');

                $totalIncome = (float) \DB::table('incomes')
                    ->where('client_id', $user->id)
                    ->sum('amount');

                // Paginate and compute running cumulative using SQL window function if supported
                // Default per_page to 5
                $perPage = (int) $request->get('per_page', 5);

                // Sorting (whitelist to avoid SQL injection)
                $allowedSorts = ['created_at', 'transaction_date', 'name', 'category', 'amount'];
                $sortBy = $request->get('sort_by', 'created_at');
                if (!in_array($sortBy, $allowedSorts, true)) {
                    $sortBy = 'transaction_date';
                }
                $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

                // If per_page <= 0 we treat it as "All" and return every row (no pagination meta)
                if ($perPage <= 0) {
                    $rows = \DB::table('expenses')
                        ->where('client_id', $user->id)
                        ->orderBy($sortBy, $sortDir)
                        ->get();

                    $expenses = array_map(function ($r) {
                        return [
                            'id' => $r->id,
                            'created_at' => $r->created_at,
                            'transaction_date' => $r->transaction_date,
                            'name' => $r->name,
                            'category' => $this->normalizeCategoryLabel($r->category),
                            'amount' => (float) $r->amount,
                            'note' => $r->note,
                        ];
                    }, $rows->all());

                    $pagination = null;
                    
                } else {
                    try {
                        // Always compute window SUM in ascending date order (oldest→newest) so the cumulative
                        // builds from the beginning. This ensures the first displayed row (regardless of sort)
                        // shows the full cumulative and most-negative remaining balance.
                        $windowOrder = 'transaction_date ASC, created_at ASC';

                        $query = \DB::table('expenses')
                            ->selectRaw("id, created_at, transaction_date, name, category, amount, note, SUM(amount) OVER (ORDER BY {$windowOrder} ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) as cumulative")
                            ->where('client_id', $user->id)
                            ->orderBy($sortBy, $sortDir);

                        $paginator = $query->paginate($perPage);

                        // Map items to include remaining (totalIncome - cumulative)
                        $items = array_map(function ($r) use ($totalIncome) {
                            return [
                                'id' => $r->id,
                                'created_at' => $r->created_at,
                                'transaction_date' => $r->transaction_date,
                                'name' => $r->name,
                                'category' => $this->normalizeCategoryLabel($r->category),
                                'amount' => (float) $r->amount,
                                'note' => $r->note,
                                'cumulative' => isset($r->cumulative) ? (float) $r->cumulative : 0,
                                'remaining' => isset($r->cumulative) ? ($totalIncome - (float) $r->cumulative) : $totalIncome,
                            ];
                        }, $paginator->items());

                        // convert paginator to array-like structure for Inertia
                        $expenses = $items;
                        // also attach pagination meta so front-end can render controls if needed
                        $pagination = [
                            'current_page' => $paginator->currentPage(),
                            'last_page' => $paginator->lastPage(),
                            'per_page' => $paginator->perPage(),
                            'total' => $paginator->total(),
                            'from' => $paginator->firstItem(),
                            'to' => $paginator->lastItem(),
                        ];

                    } catch (\Throwable $e) {
                        // Likely DB doesn't support window functions; compute cumulative/remaining in PHP so pagination still shows correct running balance

                        // Precompute cumulative amounts in chronological order (oldest first)
                        $allRows = \DB::table('expenses')
                            ->select('id', 'transaction_date', 'created_at', 'amount')
                            ->where('client_id', $user->id)
                            ->orderByRaw('transaction_date ASC, created_at ASC')
                            ->get();

                        $cumulativeById = [];
                        $remainingById = [];
                        $running = 0.0;
                        foreach ($allRows as $row) {
                            $running += (float) $row->amount;
                            $cumulativeById[$row->id] = $running;
                            $remainingById[$row->id] = $totalIncome - $running;
                        }

                        // Paginate in the requested sort order
                        $p = \DB::table('expenses')
                            ->where('client_id', $user->id)
                            ->orderBy($sortBy, $sortDir)
                            ->paginate($perPage);

                        $expenses = array_map(function ($r) use ($cumulativeById, $remainingById) {
                            $cumulative = $cumulativeById[$r->id] ?? 0.0;
                            $remaining = $remainingById[$r->id] ?? 0.0;

                            return [
                                'id' => $r->id,
                                'created_at' => $r->created_at,
                                'transaction_date' => $r->transaction_date,
                                'name' => $r->name,
                                'category' => $r->category,
                                'amount' => (float) $r->amount,
                                'note' => $r->note,
                                'cumulative' => $cumulative,
                                'remaining' => $remaining,
                            ];
                        }, $p->items());

                        $pagination = [
                            'current_page' => $p->currentPage(),
                            'last_page' => $p->lastPage(),
                            'per_page' => $p->perPage(),
                            'total' => $p->total(),
                            'from' => $p->firstItem(),
                            'to' => $p->lastItem(),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // keep empty list on error
                $expenses = [];
                $totalExpenses = 0;
                $totalIncome = 0;
                $pagination = null;
            }
        }

        // Get user's expense categories
        $expenseCategories = [];
        if ($user) {
            $expenseCategories = Category::where('created_by', $user->id)
                ->where('type', 'expense')
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
            // Always append "Other" at the end
            $expenseCategories[] = 'Other';
        }

        return Inertia::render('Expense', [
            'user' => $user ? $user->toArray() : null,
            'fullname' => $fullname,
            'expenses' => $expenses,
            'expenseCategories' => $expenseCategories,
            'totalExpenses' => $totalExpenses ?? 0,
            'totalIncome' => $totalIncome ?? 0,
            'overallIncome' => $totalIncome ?? 0,
            'overallBalance' => ($totalIncome ?? 0) - ($totalExpenses ?? 0),
            'pagination' => $pagination ?? null,
            'sort_by' => $sortBy ?? 'created_at',
            'sort_dir' => $sortDir ?? 'desc',
        ]);
    }

    /**
     * Bulk delete expenses by ids for the authenticated user.
     */
    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['string'],
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // normalize ids (uuid strings) and drop empties
        $ids = array_values(array_filter($data['ids'], fn($v) => !empty($v)));
        if (empty($ids)) {
            return back()->with('error', 'No expenses selected for deletion');
        }

        // Only delete expenses that belong to this client
        $deleted = \DB::table('expenses')
            ->whereIn('id', $ids)
            ->where('client_id', $user->id)
            ->delete();

        if ($request->wantsJson()) {
            return response()->json(['deleted' => $deleted]);
        }

        return back()->with('success', "$deleted expense(s) deleted");
    }

    /**
     * Update an expense item for the authenticated user.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'category' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        $updated = \DB::table('expenses')
            ->where('id', $id)
            ->where('client_id', $user->id)
            ->update([
                'name' => $data['name'] ?? null,
                'amount' => $data['amount'],
                // ensure we write valid JSON into the JSON column
                'category' => json_encode([$data['category']]),
                'transaction_date' => $data['date'],
                'note' => $data['note'] ?? null,
                'updated_at' => now(),
            ]);

        if ($request->wantsJson()) {
            return response()->json(['updated' => $updated]);
        }

        return back()->with('success', 'Expense updated');
    }
}
