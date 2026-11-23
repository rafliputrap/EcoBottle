
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('reward_id')
                ->nullable()
                ->constrained('rewards')
                ->nullOnDelete();

            $table->foreignId('wallet_transaction_id')
                ->unique()
                ->constrained('wallet_transactions')
                ->restrictOnDelete();

            // Snapshot agar riwayat tetap terbaca
            // meskipun nama reward diubah.
            $table->string('reward_name');

            $table->unsignedBigInteger('amount');

            $table->string('status')->default('completed');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
    }
};
