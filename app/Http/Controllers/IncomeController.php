<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class IncomeController extends Controller
{
    /**
     * Render the income list page with the user's incomes.
     */
    public function list(Request $request)
    {
        $user = $request->user();
        $fullname = '';
        if ($user) {
            $fullname = FullnameCapital($user->firstname ?? '', $user->lastname ?? '');
        }

        $incomes = [];
        $pagination = null;
        $totalIncomes = 0;
        $totalExpenses = 0;

        if ($user) {
            try {
                $totalIncomes = (float) \DB::table('incomes')->where('client_id', $user->id)->sum('amount');
                $totalExpenses = (float) \DB::table('expenses')->where('client_id', $user->id)->sum('amount');

                $perPage = (int) $request->get('per_page', 5);
                $allowedSorts = ['created_at', 'transaction_date', 'name', 'category', 'amount'];
                $sortBy = $request->get('sort_by', 'transaction_date');
                if (!in_array($sortBy, $allowedSorts, true)) $sortBy = 'transaction_date';
                $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

                // compute running balance (cumulative sum) always ordered oldest->newest
                $runningSql = 'SUM(amount) OVER (ORDER BY transaction_date ASC, created_at ASC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) as running_balance';

                if ($perPage <= 0) {
                    $rows = \DB::table('incomes')
                        ->selectRaw('id, created_at, transaction_date, name, category, amount, note, ' . $runningSql)
                        ->where('client_id', $user->id)
                        ->orderBy($sortBy, $sortDir)
                        ->get();

                    $incomes = array_map(function ($r) {
                        return [
                            'id' => $r->id,
                            'created_at' => $r->created_at,
                            'transaction_date' => $r->transaction_date,
                            'name' => $r->name,
                            'category' => $r->category,
                            'amount' => (float) $r->amount,
                            'note' => $r->note,
                            'running_balance' => isset($r->running_balance) ? (float) $r->running_balance : null,
                        ];
                    }, $rows->all());
                    $pagination = null;
                } else {
                    $p = \DB::table('incomes')
                        ->selectRaw('id, created_at, transaction_date, name, category, amount, note, ' . $runningSql)
                        ->where('client_id', $user->id)
                        ->orderBy($sortBy, $sortDir)
                        ->paginate($perPage);

                    $incomes = array_map(fn($r) => [
                        'id' => $r->id,
                        'created_at' => $r->created_at,
                        'transaction_date' => $r->transaction_date,
                        'name' => $r->name,
                        'category' => $r->category,
                        'amount' => (float) $r->amount,
                        'note' => $r->note,
                        'running_balance' => isset($r->running_balance) ? (float) $r->running_balance : null,
                    ], $p->items());

                    $pagination = [
                        'current_page' => $p->currentPage(),
                        'last_page' => $p->lastPage(),
                        'per_page' => $p->perPage(),
                        'total' => $p->total(),
                        'from' => $p->firstItem(),
                        'to' => $p->lastItem(),
                    ];
                }
            } catch (\Throwable $e) {
                $incomes = [];
            }
        }

        // Get user's income categories
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

        return Inertia::render('Income', [
            'user' => $user ? $user->toArray() : null,
            'fullname' => $fullname,
            'incomes' => $incomes,
            'incomeCategories' => $incomeCategories,
            'totalIncomes' => $totalIncomes,
            'totalIncome' => $totalIncomes,
            'overallBalance' => ($totalIncomes ?? 0) - ($totalExpenses ?? 0),
            'pagination' => $pagination,
            'sort_by' => $sortBy ?? 'transaction_date',
            'sort_dir' => $sortDir ?? 'desc',
        ]);
    }
    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'category' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $income = Income::create([
            'name' => $data['name'] ?? null,
            'amount' => $data['amount'],
            // store category as JSON array for consistency with expenses
            'category' => is_array($data['category']) ? json_encode($data['category']) : json_encode([$data['category']]),
            'transaction_date' => $data['date'],
            'note' => $data['note'] ?? null,
            'client_id' => $request->user() ? $request->user()->id : null,
        ]);

        // If this is an API/json caller, return JSON. For Inertia/form submissions return back with flash.
        if ($request->wantsJson()) {
            return response()->json($income, 201);
        }

        return back()->with('success', 'Income added');
    }

    /**
     * Bulk delete incomes by ids[] for the authenticated user.
     */
    public function bulkDelete(Request $request)
    {
        $user = $request->user();
        if (!$user) return back()->with('error', 'Not authenticated');

        $data = $request->all();
        $ids = $data['ids'] ?? [];
        if (!is_array($ids) || empty($ids)) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'No ids provided'], 422);
            }
            return back()->with('error', 'No items selected');
        }

        try {
            $deleted = \DB::table('incomes')
                ->where('client_id', $user->id)
                ->whereIn('id', $ids)
                ->delete();

            if ($request->wantsJson()) {
                return response()->json(['deleted' => $deleted]);
            }

            return back()->with('success', "Deleted {$deleted} income(s)");
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to delete selected incomes');
        }
    }
}
