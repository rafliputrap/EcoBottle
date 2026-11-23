<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;

class WalletController extends Controller
{
    // Menampilkan saldo Wallet pengguna
    public function balance(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Saldo Wallet berhasil diambil.',
            'data' => [
                'wallet_balance' => $user->wallet_balance,
            ],
        ]);
    }

    // Menampilkan riwayat reward Wallet
    public function history(Request $request)
    {
        $history = WalletTransaction::with([
            'transaction.machine',
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat Wallet berhasil diambil.',
            'data' => $history,
        ]);
    }
}
