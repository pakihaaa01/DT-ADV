<?php

use Illuminate\Support\Facades\Route;
use App\Models\TipeAlat;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PesananController;


Route::get('/test', function () {
    return response()->json(['message' => 'API jalan']);
});

// AUTH
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/google/login', [AuthController::class, 'loginGoogle']);
    Route::post('/google/verify-token', [AuthController::class, 'verifyTokenIdGoogle']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// PESANAN
Route::prefix('pesanan')
    ->middleware('auth:sanctum')
    ->group(function () {

        Route::get('/', [PesananController::class, 'index']);
        Route::post('/', [PesananController::class, 'store']);
    });

// TIPE ALAT
Route::get('/tipe-alat', function () {
    return TipeAlat::all();
});