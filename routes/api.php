<?php

use Illuminate\Support\Facades\Route;
use App\Models\TipeAlat;
use App\Http\Controllers\Api\PesananController;

Route::get('/test', function () {
    return response()->json(['message' => 'API jalan']);
});

Route::get('/tipe-alat', function () {
    return TipeAlat::all();
});

Route::post('/pesanan', [PesananController::class, 'store']);