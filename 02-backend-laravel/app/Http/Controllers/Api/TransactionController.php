<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Machine;
use App\Models\BottleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\WalletTransaction;

class TransactionController extends Controller
{
    // Menampilkan riwayat transaksi pengguna
    public function index(Request $request)
    {
        $transactions = Transaction::with([
            'machine',
            'items.bottleType'
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    // Membuat transaksi dari hasil scan simulator
    public function createFromMachine(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => [
                'required',
                'integer',
                'exists:machines,id'
            ],

            'items' => [
                'required',
                'array',
                'min:1'
            ],

            'items.*.bottle_type_id' => [
                'required',
                'integer',
                'exists:bottle_types,id'
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:1000'
            ],
        ]);

        $machine = Machine::findOrFail($validated['machine_id']);

        if ($machine->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Mesin sedang tidak aktif.',
            ], 422);
        }

        $transaction = DB::transaction(function () use ($validated, $machine) {

            $totalAmount = 0;
            $totalQuantity = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $bottleType = BottleType::findOrFail(
                    $item['bottle_type_id']
                );

                if (! $bottleType->is_active) {
                    throw new \RuntimeException(
                        'Jenis botol ' . $bottleType->name . ' tidak aktif.'
                    );
                }

                $quantity = $item['quantity'];
                $unitPrice = (int) $bottleType->reward;
                $subtotal = $quantity * $unitPrice;

                $totalQuantity += $quantity;
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'bottle_type_id' => $bottleType->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $transaction = Transaction::create([
                'user_id' => null,
                'machine_id' => $machine->id,
                'bottle_type_id' => null,
                'quantity' => $totalQuantity,
                'unit_price' => null,
                'total_amount' => $totalAmount,
                'status' => 'pending_claim',
                'qr_token' => Str::random(64),
            ]);

            $transaction->items()->createMany($itemsData);

            return $transaction;
        });

        return response()->json([
            'success' => true,
            'message' => 'Transaksi mesin berhasil dibuat.',
            'data' => $transaction->load([
                'machine',
                'items.bottleType'
            ]),
        ], 201);
    }

    // Pengguna mengklaim transaksi melalui QR dari APK

    public function claim(Request $request)
    {
        $validated = $request->validate([
            'qr_token' => [
                'required',
                'string',
            ],
        ]);

        $result = DB::transaction(function () use ($request, $validated) {

            $transaction = Transaction::where(
                'qr_token',
                $validated['qr_token']
            )
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                return [
                    'status' => 'not_found',
                ];
            }

            if ($transaction->status !== 'pending_claim') {
                return [
                    'status' => 'already_claimed',
                ];
            }

            $user = \App\Models\User::where(
                'id',
                $request->user()->id
            )
                ->lockForUpdate()
                ->firstOrFail();

            $amount = (int) $transaction->total_amount;

            if ($amount <= 0) {
                return [
                    'status' => 'invalid_amount',
                ];
            }

            // Hubungkan transaksi dengan pengguna
            $transaction->update([
                'user_id' => $user->id,
                'status' => 'completed',
                'claimed_at' => now(),
            ]);

            // Tambahkan reward ke saldo pengguna
            $user->increment('wallet_balance', $amount);

            // Catat riwayat reward
            WalletTransaction::create([
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'type' => 'credit',
                'description' => 'Reward EcoBottle transaksi #' . $transaction->id,
            ]);

            return [
                'status' => 'success',
                'transaction' => $transaction,
            ];
        });

        if ($result['status'] === 'not_found') {
            return response()->json([
                'success' => false,
                'message' => 'QR transaksi tidak ditemukan.',
            ], 404);
        }

        if ($result['status'] === 'already_claimed') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah diklaim sebelumnya.',
            ], 422);
        }

        if ($result['status'] === 'invalid_amount') {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah reward tidak valid.',
            ], 422);
        }

        $user = $request->user()->fresh();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil diklaim. Reward masuk ke Wallet.',
            'data' => [
                'transaction' => $result['transaction']->load([
                    'machine',
                    'items.bottleType',
                ]),
                'wallet_balance' => $user->wallet_balance,
                'reward_added' => $result['transaction']->total_amount,
            ],
        ]);
    }
}
