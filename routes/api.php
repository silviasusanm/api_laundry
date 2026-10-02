<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LayananController;
use App\Http\Controllers\Api\ManagementController;
use App\Http\Controllers\Api\PesananController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['success' => true, 'service' => 'Bersih Laundry API']));
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/layanan', [LayananController::class, 'index']);
Route::get('/layanan/{layanan}', [LayananController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::post('/layanan', [LayananController::class, 'store']);
    Route::put('/layanan/{layanan}', [LayananController::class, 'update']);
    Route::delete('/layanan/{layanan}', [LayananController::class, 'destroy']);

    Route::get('/pesanan', [PesananController::class, 'index']);
    Route::post('/pesanan', [PesananController::class, 'store']);
    Route::get('/pesanan/{pesanan}', [PesananController::class, 'show']);
    Route::patch('/pesanan/{pesanan}/status', [PesananController::class, 'updateStatus']);
    Route::patch('/pesanan/{pesanan}/berat', [PesananController::class, 'confirmWeight']);
    Route::post('/pesanan/{pesanan}/pembayaran', [PesananController::class, 'pay']);

    Route::get('/pelanggan', [ManagementController::class, 'customers']);
    Route::put('/pelanggan/{pelanggan}', [ManagementController::class, 'updateCustomer']);
    Route::delete('/pelanggan/{pelanggan}', [ManagementController::class, 'deleteCustomer']);
    Route::get('/users', [ManagementController::class, 'users']);
    Route::post('/users', [ManagementController::class, 'createUser']);
    Route::patch('/users/{user}', [ManagementController::class, 'updateUser']);
    Route::get('/laporan', [ManagementController::class, 'report']);
    Route::get('/notifikasi', [ManagementController::class, 'notifications']);
    Route::patch('/notifikasi/{notifikasi}/dibaca', [ManagementController::class, 'markNotificationRead']);
});
