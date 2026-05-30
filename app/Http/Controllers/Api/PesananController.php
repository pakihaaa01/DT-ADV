<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PesananController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Data yang Masuk
        $request->validate([
            'user_id' => 'required',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
            'total_harga' => 'required|numeric',
            'items' => 'required|array',
            'items.*.tipe_alat_id' => 'required',
            'items.*.qty' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            // 2. Simpan ke Tabel Utama (pesanan)
            // Sesuaikan nama kolom dengan struktur database Laravel kamu
            $pesananId = DB::table('pesanan')->insertGetId([
                'user_id' => $request->user_id,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'total_harga' => $request->total_harga,
                'status' => 'pending', // Status awal
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Simpan Detail Barang ke Tabel (detail_pesanan / transaksi_detail)
            foreach ($request->items as $item) {
                DB::table('detail_pesanan')->insert([
                    'pesanan_id' => $pesananId,
                    'tipe_alat_id' => $item['tipe_alat_id'],
                    'qty' => $item['qty'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Opsional: Kurangi stok produk di tabel tipe_alat jika diperlukan
                // DB::table('tipe_alat')->where('id', $item['tipe_alat_id'])->decrement('stok', $item['qty']);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat!',
                'pesanan_id' => $pesananId
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }
}