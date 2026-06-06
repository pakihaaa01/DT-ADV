<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class PesananController extends Controller
{
    public function store(Request $request)
    {
        // 1. Tambahkan validasi untuk file bukti_pembayaran
        $request->validate([
            'user_id' => 'required',
            'nama' => 'required',
            'whatsapp' => 'required',
            'email' => 'required',
            'metode_pembayaran' => 'required',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
            'total_harga' => 'required|numeric',
            'items' => 'required|array',
            'items.*.tipe_alat_id' => 'required',
            'items.*.qty' => 'required|integer',
            'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg|max:5120' 
        ]);

        DB::beginTransaction();
        try {
            $mulai = Carbon::parse($request->tgl_mulai);
            $selesai = Carbon::parse($request->tgl_selesai);
            $durasiHari = $mulai->diffInDays($selesai);
            
            if ($durasiHari < 1) {
                $durasiHari = 1;
            }

            // 2. Logika Status Dinamis
            $status = 'Menunggu Konfirmasi';
            if ($request->metode_pembayaran == 'Cash') {
                $status = 'Menunggu Pengambilan';
            } elseif ($request->metode_pembayaran == 'QRIS') {
                $status = 'Menunggu Verifikasi';
            }

            // 3. Proses Upload Bukti Pembayaran ke Folder Server
            $buktiBayarPath = null;
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $namaFile = time() . '_' . $file->getClientOriginalName();
                // Simpan ke folder public/storage/bukti_pembayaran
                $file->move(public_path('storage/bukti_pembayaran'), $namaFile);
                $buktiBayarPath = 'storage/bukti_pembayaran/' . $namaFile;
            }

            // 4. Simpan ke Database
            $pesananId = DB::table('pesanan')->insertGetId([
                'user_id'           => $request->user_id,
                'nama'              => $request->nama,
                'whatsapp'          => $request->whatsapp,
                'email'             => $request->email,
                'hari'              => $durasiHari,
                'tanggal_mulai'     => $request->tgl_mulai,
                'tanggal_kembali'   => $request->tgl_selesai,
                'session_id'        => null,
                'metode_pembayaran' => $request->metode_pembayaran,
                'total_harga'       => $request->total_harga,
                'status'            => $status, // Gunakan status dinamis
                'bukti_pembayaran'  => $buktiBayarPath, // Simpan path gambar
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            foreach ($request->items as $item) {
                $product = DB::table('tipe_alat')->where('id', $item['tipe_alat_id'])->first();
                $harga = $product->harga ?? 0;
                $namaAlat = $product->nama_alat ?? ($product->nama ?? 'Alat');

                DB::table('pesanan_items')->insert([
                    'pesanan_id' => $pesananId,
                    'product_id' => $item['tipe_alat_id'],
                    'nama_alat'  => $namaAlat,
                    'jumlah'     => $item['qty'],
                    'harga'      => $harga,
                    'subtotal'   => $harga * $item['qty'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
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