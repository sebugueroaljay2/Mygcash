<?php

namespace App\Http\Controllers;

// use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\GcashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class ApiController extends Controller
{
    // public function index(Request $request)
    // {
    //     $transactions = GcashTransaction::with(['transaction_type', 'charge_type'])
    //         ->latest()
    //         ->paginate(5);

    //     return response()->json($transactions);
    // }
    // public function index(Request $request)
    // {
    //     $search = $request->query('search');
    //     $types = $request->query('transaction_types', []); // array from checkbox

    //     $transactions = GcashTransaction::with(['transaction_type', 'charge_type'])
    //         ->when($search, function ($query, $search) {
    //             $query->where(function ($q) use ($search) {
    //                 $q->where('name', 'like', "{$search}%")
    //                     ->orWhere('reference_number', 'like', "{$search}%");
    //             });
    //         })
    //         ->when(count($types), function ($query) use ($types) {
    //             $query->whereHas('transaction_type', function ($q) use ($types) {
    //                 $q->whereIn('name', $types); // match any checked types
    //             });
    //         })
    //         ->latest()
    //         ->paginate(10);

    //     return response()->json($transactions);
    // }

    // public function index(Request $request)
    // {
    //     $search = $request->query('search');
    //     $types = $request->query('transaction_types', []); // array from checkbox

    //     $transactionsQuery = GcashTransaction::with(['transaction_type', 'charge_type'])
    //         ->when($search, function ($query, $search) {
    //             $query->where(function ($q) use ($search) {
    //                 $q->where('name', 'like', "{$search}%")
    //                     ->orWhere('reference_number', 'like', "{$search}%");
    //             });
    //         })
    //         ->when(count($types), function ($query) use ($types) {
    //             $query->whereHas('transaction_type', function ($q) use ($types) {
    //                 $q->whereIn('name', $types); // match any checked types
    //             });
    //         });

    //     $paginated = $transactionsQuery->latest()->paginate(10);

    //     // Compute income statistics
    //     $incomeQuery = clone $transactionsQuery; // Reuse same filters

    //     $dailyIncome = (clone $incomeQuery)->whereDate('created_at', Carbon::today())->sum('amount');
    //     $weeklyIncome = (clone $incomeQuery)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount');
    //     $monthlyIncome = (clone $incomeQuery)->whereMonth('created_at', now()->month)->sum('amount');

    //     return response()->json([
    //         ...$paginated->toArray(),
    //         'daily_income' => $dailyIncome,
    //         'weekly_income' => $weeklyIncome,
    //         'monthly_income' => $monthlyIncome,
    //     ]);
    // }


    

    public function index(Request $request)
    {
        $search = $request->query('search');
        $types = $request->query('transaction_types', []);

        $transactionsQuery = GcashTransaction::with(['transaction_type', 'charge_type'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "{$search}%")
                        ->orWhere('reference_number', 'like', "{$search}%");
                });
            })
            ->when(count($types), function ($query) use ($types) {
                $query->whereHas('transaction_type', function ($q) use ($types) {
                    $q->whereIn('name', $types);
                });
            });

        $paginated = $transactionsQuery->latest()->paginate(10);

        // Compute charge-based income
        $baseIncomeQuery = GcashTransaction::with('charge_type')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "{$search}%")
                        ->orWhere('reference_number', 'like', "{$search}%");
                });
            })
            ->when(count($types), function ($query) use ($types) {
                $query->whereHas('transaction_type', function ($q) use ($types) {
                    $q->whereIn('name', $types);
                });
            });

        // Helper to get sum of charges by date range
        $sumCharges = function ($query) {
            return $query->get()->sum(function ($tx) {
                return optional($tx->charge_type)->charges ?? 0;
            });
        };

        $dailyIncome = $sumCharges(
            (clone $baseIncomeQuery)->whereDate('created_at', Carbon::today('Asia/Manila'))
        );

        $weeklyIncome = $sumCharges(
            (clone $baseIncomeQuery)->whereBetween('created_at', [Carbon::now('Asia/Manila')->startOfWeek(), Carbon::now('Asia/Manila')->endOfWeek()])
        );

        $monthlyIncome = $sumCharges(
            (clone $baseIncomeQuery)->whereMonth('created_at', Carbon::now('Asia/Manila')->month)
        );

        return response()->json([
            ...$paginated->toArray(),
            'daily_income' => $dailyIncome,
            'weekly_income' => $weeklyIncome,
            'monthly_income' => $monthlyIncome,
        ]);
    }
    public function store(Request $request)
    {
        $validator = FacadesValidator::make($request->all(), [
            'name' => [ 'regex:/^[A-Za-z\s]+$/'],
            'amount' => 'required|numeric|min:1',
            'transaction_type_id' => 'required|integer',
            'charge_type_id' => 'required|integer',
            'reference_number' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validated = $validator->validated();
        $transaction = GcashTransaction::create($validated);

        return response()->json($transaction, 201);
    }

    public function update(Request $request, GcashTransaction $transaction)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'amount' => 'required|numeric',
            'transaction_type_id' => 'required|integer',
            'charge_type_id' => 'required|integer',
            'reference_number' => 'nullable|string',
        ]);

        $transaction->update($validated);

        return response()->json($transaction);
    }

    public function destroy($id)
    {
        $transaction = GcashTransaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully']);
    }

    public function deleteAll()
    {
        GcashTransaction::truncate();

        return response()->json([
            'message' => 'All transactions deleted successfully'
        ]);
    }

     public function stats()
    {
        // Sum charge from related charge_type (foreign key)
        $dailyIncome = GcashTransaction::whereDate('created_at', Carbon::today('Asia/Manila'))
            ->with('charge_type')
            ->get()
            ->sum(fn($tx) => $tx->charge_type->charges ?? 0);

        $weeklyIncome = GcashTransaction::whereBetween('created_at', [
            Carbon::now('Asia/Manila')->startOfWeek(),
            Carbon::now('Asia/Manila')->endOfWeek(),
        ])
            ->with('charge_type')
            ->get()
            ->sum(fn($tx) => $tx->charge_type->charges ?? 0);

        $monthlyIncome = GcashTransaction::whereMonth('created_at', Carbon::now('Asia/Manila')->month)
            ->with('charge_type')
            ->get()
            ->sum(fn($tx) => $tx->charge_type->charges ?? 0);

        return response()->json([
            'daily_income' => $dailyIncome,
            'weekly_income' => $weeklyIncome,
            'monthly_income' => $monthlyIncome,
        ]);
    }

    public function getDailyTransactionCount()
    {
        $today = Carbon::today('Asia/Manila');

        $cashinCount = GcashTransaction::whereDate('created_at', $today)
            ->whereHas('transaction_type', fn($q) => $q->where('name', 'Cash In'))
            ->count();

        $cashoutCount = GcashTransaction::whereDate('created_at', $today)
            ->whereHas('transaction_type', fn($q) => $q->where('name', 'Cash Out'))
            ->count();

        return response()->json([
            'cashin_count' => $cashinCount,
            'cashout_count' => $cashoutCount,
            'date' => $today->toDateString(),
        ]);
    }
}
