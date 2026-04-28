<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mengecek apakah kolom 'harga' belum ada di tabel 'keranjang' sebelum menambahkannya
        if (!Schema::hasColumn('keranjang', 'harga')) {
            Schema::table('keranjang', function (Blueprint $table) {
                $table->decimal('harga', 10, 2)->notNull()->after('gambar');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Mengecek apakah kolom 'harga' ada sebelum menghapusnya saat rollback
        if (Schema::hasColumn('keranjang', 'harga')) {
            Schema::table('keranjang', function (Blueprint $table) {
                $table->dropColumn('harga');
            });
        }
    }
};