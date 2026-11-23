<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Machine;
use App\Models\BottleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with(['machine', 'bottleType'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => ['required', 'integer', 'exists:machines,id'],
            'bottle_type_id' => ['required', 'integer', 'exists:bottle_types,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $machine = Machine::findOrFail($validated['machine_id']);
        $bottleType = BottleType::findOrFail($validated['bottle_type_id']);
        if (! $bottleType->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis botol sedang tidak aktif.',
            ], 422);
        }

        if ($machine->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Mesin sedang tidak aktif.',
            ], 422);
        }

        $unitPrice = (int) $bottleType->reward;
        $quantity = $validated['quantity'];

        $transaction = DB::transaction(function () use (
            $request,
            $machine,
            $bottleType,
            $unitPrice,
            $quantity
        ) {
            return Transaction::create([
                'user_id' => $request->user()->id,
                'machine_id' => $machine->id,
                'bottle_type_id' => $bottleType->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $unitPrice * $quantity,
                'status' => 'completed',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dibuat.',
            'data' => $transaction,
        ], 201);
    }
}
