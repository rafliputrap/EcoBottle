<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RewardController extends Controller
{
    /**
     * Menampilkan daftar reward yang aktif.
     */
    public function index()
    {
        $rewards = Reward::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar reward berhasil diambil.',
            'data' => $rewards,
        ]);
    }

    /**
     * Menukarkan saldo Rupiah dengan reward.
     */
    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'reward_id' => 'required|integer|exists:rewards,id',
        ]);

        try {
            $result = DB::transaction(function () use ($request, $validated) {

                $user = $request->user()
                    ->newQuery()
                    ->whereKey($request->user()->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $reward = Reward::where('id', $validated['reward_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$reward->is_active) {
                    throw new \RuntimeException(
                        'Reward sedang tidak tersedia.'
                    );
                }

                if ($reward->price <= 0) {
                    throw new \RuntimeException(
                        'Harga reward tidak valid.'
                    );
                }

                if ($user->wallet_balance < $reward->price) {
                    throw new \RuntimeException(
                        'Saldo Rupiah kamu tidak mencukupi.'
                    );
                }

                // Kurangi saldo pengguna.
                $user->wallet_balance -= $reward->price;
                $user->save();

                // Catat transaksi debit wallet.
                $walletTransaction = WalletTransaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => null,
                    'amount' => $reward->price,
                    'type' => 'debit',
                    'description' => 'Penukaran reward: ' . $reward->name,
                ]);

                // Simpan riwayat penukaran reward.
                $redemption = RewardRedemption::create([
                    'user_id' => $user->id,
                    'reward_id' => $reward->id,
                    'wallet_transaction_id' => $walletTransaction->id,
                    'reward_name' => $reward->name,
                    'amount' => $reward->price,
                    'status' => 'completed',
                ]);

                return [
                    'redemption' => $redemption,
                    'reward' => $reward,
                    'wallet_balance' => $user->wallet_balance,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Reward berhasil ditukarkan.',
                'data' => $result,
            ], 201);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('Reward redemption failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menukarkan reward.',
            ], 500);
        }
    }

    /**
     * Menampilkan riwayat penukaran reward.
     */
    public function history(Request $request)
    {
        $history = RewardRedemption::with([
            'reward',
            'walletTransaction',
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat penukaran reward berhasil diambil.',
            'data' => $history,
        ]);
    }
}
