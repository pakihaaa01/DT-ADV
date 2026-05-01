<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            
            // 🔥 1. Tambahkan ->nullable() di sini agar Guest Checkout berhasil!
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // 🔥 2. Tambahkan pesanan_id untuk menyambungkan pembayaran dengan pesanan
            $table->foreignId('pesanan_id')->constrained('pesanan')->onDelete('cascade');
            
            $table->string('kode_pembayaran')->unique();
            $table->decimal('jumlah', 15, 2);
            $table->string('metode_pembayaran');
            $table->enum('status', ['pending', 'lunas', 'gagal'])->default('pending');
            $table->dateTime('tanggal_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};