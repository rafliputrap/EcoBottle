
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('transaction_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        // Jangan mengubah kembali menjadi NOT NULL secara otomatis,
        // karena mungkin sudah ada transaksi penukaran Reward.
    }
};
