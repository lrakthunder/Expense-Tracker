<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'report_name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        // Validate date range does not exceed 1 year (365 days)
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        if ($start->diffInDays($end) > 365) {
            return response()->json([
                'message' => 'Date range cannot exceed 1 year (365 days). Please select a shorter period.'
            ], 422);
        }

        $start = $start->startOfDay();
        $end = $end->endOfDay();

        // Fetch expenses and incomes within range
        $expenses = DB::table('expenses')
            ->where('client_id', $user->id)
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('transaction_date')
            ->get(['id', 'name', 'category', 'amount', 'transaction_date', 'note', 'created_at']);

        $incomes = DB::table('incomes')
            ->where('client_id', $user->id)
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('transaction_date')
            ->get(['id', 'name', 'category', 'amount', 'transaction_date', 'note', 'created_at']);

        $totalExpenses = (float) $expenses->sum('amount');
        $totalIncome = (float) $incomes->sum('amount');
        $netBalance = $totalIncome - $totalExpenses;

        // Average daily expense over the selected range
        $days = max(1, $start->diffInDays($end) + 1);
        $avgDailyExpense = $totalExpenses / $days;

        // Category breakdown
        $byCategory = [];
        foreach ($expenses as $exp) {
            $label = $this->normalizeCategoryLabel($exp->category);
            if (!isset($byCategory[$label])) {
                $byCategory[$label] = 0.0;
            }
            $byCategory[$label] += (float) $exp->amount;
        }
        arsort($byCategory);
        $topCategory = $totalExpenses > 0 && count($byCategory)
            ? [
                'name' => array_key_first($byCategory),
                'value' => reset($byCategory),
                'percent' => round((reset($byCategory) / $totalExpenses) * 100, 1),
            ]
            : null;

        // Daily trend (expenses and income per day)
        $daily = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();
            $daily[$date] = [
                'date' => $date,
                'expenses' => 0.0,
                'income' => 0.0,
            ];
            $cursor->addDay();
        }

        foreach ($expenses as $exp) {
            $d = (string) $exp->transaction_date;
            if (isset($daily[$d])) {
                $daily[$d]['expenses'] += (float) $exp->amount;
            }
        }
        foreach ($incomes as $inc) {
            $d = (string) $inc->transaction_date;
            if (isset($daily[$d])) {
                $daily[$d]['income'] += (float) $inc->amount;
            }
        }

        // Transactions combined with running balance
        $transactions = [];
        foreach ($expenses as $exp) {
            $transactions[] = [
                'type' => 'expense',
                'date' => (string) $exp->transaction_date,
                'description' => $exp->name ?: 'Expense',
                'category' => $this->normalizeCategoryLabel($exp->category),
                'amount' => -(float) $exp->amount,
                'note' => $exp->note,
            ];
        }
        foreach ($incomes as $inc) {
            $transactions[] = [
                'type' => 'income',
                'date' => (string) $inc->transaction_date,
                'description' => $inc->name ?: 'Income',
                'category' => $this->normalizeCategoryLabel($inc->category),
                'amount' => (float) $inc->amount,
                'note' => $inc->note,
            ];
        }

        usort($transactions, function ($a, $b) {
            if ($a['date'] === $b['date']) return 0;
            return $a['date'] < $b['date'] ? -1 : 1;
        });

        $running = 0.0;
        foreach ($transactions as &$tx) {
            $running += $tx['amount'];
            $tx['running_balance'] = $running;
        }
        unset($tx);

        // Insights (simple heuristics)
        $insights = [];
        if ($topCategory) {
            $insights[] = "Top category is {$topCategory['name']} at {$topCategory['percent']}% of spending.";
        }
        $maxDayExpense = null;
        foreach ($daily as $d) {
            if (!$maxDayExpense || $d['expenses'] > $maxDayExpense['expenses']) {
                $maxDayExpense = $d;
            }
        }
        if ($maxDayExpense && $maxDayExpense['expenses'] > 0) {
            $insights[] = "Highest daily expense was on {$maxDayExpense['date']} amounting to " . number_format($maxDayExpense['expenses'], 2);
        }
        if ($netBalance >= 0) {
            $insights[] = 'Net balance is positive for this period.';
        } else {
            $insights[] = 'Net balance is negative; review spending categories.';
        }

        return response()->json([
            'reportTitle' => $data['report_name'] ?: 'Expense Report',
            'dateRange' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'generatedAt' => now()->toIso8601String(),
            'user' => [
                'fullname' => $user
                    ? (trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->name ?? ''))
                    : '',
            ],
            'summary' => [
                'totalExpenses' => $totalExpenses,
                'totalIncome' => $totalIncome,
                'netBalance' => $netBalance,
                'avgDailyExpense' => $avgDailyExpense,
                'topCategory' => $topCategory,
            ],
            'categoryBreakdown' => [
                'labels' => array_keys($byCategory),
                'values' => array_values($byCategory),
            ],
            'dailyTrend' => array_values($daily),
            'transactions' => $transactions,
            'insights' => $insights,
        ]);
    }

    /**
     * Normalize a category value from the DB into a clean label string.
     */
    private function normalizeCategoryLabel($cat)
    {
        if ($cat === null) return '-';
        if (is_array($cat)) {
            $s = implode(', ', $cat);
            return trim($s) === '' ? '-' : $s;
        }
        if (is_string($cat)) {
            $trimmed = trim($cat);
            if ((str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) || (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}'))) {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (is_array($decoded)) {
                        return trim(implode(', ', $decoded)) ?: '-';
                    }
                    return (string) $decoded ?: '-';
                }
            }
            $s = trim($trimmed, " \t\n\r\0\x0B'\"[]");
            $s = preg_replace('/[^A-Za-z0-9\-, ]+/', '', $s);
            $s = preg_replace('/\s+/', ' ', $s);
            $s = trim($s);
            return $s === '' ? '-' : $s;
        }
        $s = trim((string) $cat);
        $s = preg_replace('/[^A-Za-z0-9\-, ]+/', '', $s);
        return $s === '' ? '-' : $s;
    }

    /**
     * Get daily trend data (income and expenses per day).
     */
    public function dailyTrend(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->endOfDay();

        // Fetch expenses and incomes within range
        $expenses = DB::table('expenses')
            ->where('client_id', $user->id)
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->get(['transaction_date', 'amount']);

        $incomes = DB::table('incomes')
            ->where('client_id', $user->id)
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->get(['transaction_date', 'amount']);

        // Build daily trend dictionary
        $daily = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();
            $daily[$date] = [
                'expenses' => 0.0,
                'income' => 0.0,
            ];
            $cursor->addDay();
        }

        foreach ($expenses as $exp) {
            $d = (string) $exp->transaction_date;
            if (isset($daily[$d])) {
                $daily[$d]['expenses'] += (float) $exp->amount;
            }
        }

        foreach ($incomes as $inc) {
            $d = (string) $inc->transaction_date;
            if (isset($daily[$d])) {
                $daily[$d]['income'] += (float) $inc->amount;
            }
        }

        return response()->json($daily);
    }
}
