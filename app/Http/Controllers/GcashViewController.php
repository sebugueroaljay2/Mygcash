<?php

namespace App\Http\Controllers;

use App\Models\ChargeType;
use App\Models\GcashTransaction;
use App\Models\TransactionType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GcashViewController extends Controller
{
    // public function index()
    // {


    //     return Inertia::render('Gcash/Gcashv2', [
    //         'transaction_types' => TransactionType::all(),
    //         'charge_types' => ChargeType::all(),
    //         'allTransactions' => GcashTransaction::orderByDesc('id')->with('transaction_type', 'charge_type')->latest()->paginate(5),
    //     ]);
    // }

    public function index()
    {
        $transactions = GcashTransaction::orderByDesc('id')
            ->with('transaction_type', 'charge_type')
            ->latest()
            ->paginate(5);

        // Fix: replace null URLs with empty string
        $cleanedLinks = collect($transactions->links())->map(function ($link) {
            $link['url'] = $link['url'] ?? '';
            return $link;
        });

        return Inertia::render('Gcash/Gcashv2', [
            'transaction_types' => TransactionType::all(),
            'charge_types' => ChargeType::all(),
            'allTransactions' => [
                ...$transactions->toArray(),
                'links' => $cleanedLinks,
            ]
        ]);
    }
}
