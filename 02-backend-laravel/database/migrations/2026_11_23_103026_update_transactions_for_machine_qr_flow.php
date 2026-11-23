<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Menyesuaikan tabel transaksi
        Schema::table('transactions', function (Blueprint $table) {
            // Transaksi boleh dibuat mesin sebelum pengguna login
            $table->foreignId('user_id')->nullable()->change();

            // Rincian jenis botol dipindahkan ke transaction_items
            $table->foreignId('bottle_type_id')
                ->nullable()
                ->change();

            // Harga satuan tidak wajib untuk transaksi banyak jenis botol
            $table->unsignedInteger('unit_price')
                ->nullable()
                ->change();

            // Status awal transaksi menunggu klaim pengguna
            $table->string('status')
                ->default('pending_claim')
                ->change();

            // Token unik untuk QR Code
            $table->string('qr_token')
                ->nullable()
                ->unique();

            // Waktu transaksi diklaim melalui APK
            $table->timestamp('claimed_at')->nullable();
        });

        // 2. Membuat tabel rincian jenis botol
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->cascadeOnDelete();

            $table->foreignId('bottle_type_id')
                ->constrained('bottle_types')
                ->restrictOnDelete();

            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('subtotal');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn(['qr_token', 'claimed_at']);
        });
    }
};
