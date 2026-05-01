<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // 🔥 HAPUS ATAU COMMENT BARIS INI:
            // $table->foreignId('pesanan_id')->...
            
            // BIARKAN BARIS INI (jika ada kolom bukti transfer):
            $table->string('bukti')->nullable(); 
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // 🔥 HAPUS ATAU COMMENT JUGA DI SINI:
            // $table->dropForeign(['pesanan_id']);
            // $table->dropColumn('pesanan_id');
            
            $table->dropColumn('bukti');
        });
    }
};
