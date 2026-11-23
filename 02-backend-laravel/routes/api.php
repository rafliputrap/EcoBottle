
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BottleTypeController;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\RewardController;

// ==========================================
// AUTHENTICATION
// ==========================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// ==========================================
// PUBLIC DATA
// ==========================================

Route::get('/bottle-types', [BottleTypeController::class, 'index']);
Route::get('/machines', [MachineController::class, 'index']);


// ==========================================
// MACHINE TRANSACTION
// Simulator membuat transaksi setelah
// semua botol selesai dipindai.
// ==========================================

Route::post('/machine/transactions', [
    TransactionController::class,
    'createFromMachine'
]);


// ==========================================
// AUTHENTICATED ROUTES
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    // --------------------------------------
    // USER AUTHENTICATION
    // --------------------------------------

    Route::get('/user', [AuthController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);


    // --------------------------------------
    // TRANSACTION HISTORY
    // --------------------------------------

    Route::get('/transactions', [
        TransactionController::class,
        'index'
    ]);


    // --------------------------------------
    // CLAIM TRANSACTION USING QR CODE
    // --------------------------------------

    Route::post('/transactions/claim', [
        TransactionController::class,
        'claim'
    ]);


    // --------------------------------------
    // WALLET
    // --------------------------------------

    Route::get('/wallet', [
        WalletController::class,
        'balance'
    ]);

    Route::get('/wallet/history', [
        WalletController::class,
        'history'
    ]);


    // --------------------------------------
    // REWARD
    // --------------------------------------

    // Mengambil daftar reward
    Route::get('/rewards', [
        RewardController::class,
        'index'
    ]);

    // Menukarkan reward menggunakan saldo Wallet
    Route::post('/rewards/redeem', [
        RewardController::class,
        'redeem'
    ]);

    // Melihat riwayat penukaran reward
    Route::get('/reward-redemptions', [
        RewardController::class,
        'history'
    ]);

});
