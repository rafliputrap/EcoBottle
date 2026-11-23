<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BottleTypeController;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/bottle-types', [BottleTypeController::class, 'index']);
Route::get('/machines', [MachineController::class, 'index']);

// Simulator membuat transaksi setelah semua botol selesai dipindai
Route::post('/machine/transactions', [
    TransactionController::class,
    'createFromMachine'
]);

Route::middleware('auth:sanctum')->group(function () {

    // Autentikasi pengguna
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Riwayat transaksi pengguna
    Route::get('/transactions', [
        TransactionController::class,
        'index'
    ]);

    // Klaim transaksi menggunakan QR
    Route::post('/transactions/claim', [
        TransactionController::class,
        'claim'
    ]);

    // Wallet pengguna
    Route::get('/wallet', [
        WalletController::class,
        'balance'
    ]);

    // Riwayat reward Wallet
    Route::get('/wallet/history', [
        WalletController::class,
        'history'
    ]);
});
