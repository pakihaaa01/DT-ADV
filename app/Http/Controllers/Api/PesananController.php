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
            $mulai = Carbon::parse($request->tgl_mulai);
            $selesai = Carbon::parse($request->tgl_selesai);
            $durasiHari = $mulai->diffInDays($selesai);
            
            if ($durasiHari < 1) {
                $durasiHari = 1;
            }

            $pesananId = DB::table('pesanan')->insertGetId([
                'user_id'           => $request->user_id,
                'nama'              => 'User Mobile',
                'whatsapp'          => '-',
                'email'             => '-',
                'hari'              => $durasiHari,
                'tanggal_mulai'     => $request->tgl_mulai,
                'tanggal_kembali'   => $request->tgl_selesai,
                'session_id'        => null,
                'metode_pembayaran' => 'Cash',
                'status'            => 'pending',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            foreach ($request->items as $item) {
                DB::table('detail_pesanan')->insert([
                    'pesanan_id'   => $pesananId,
                    'tipe_alat_id' => $item['tipe_alat_id'],
                    'qty'          => $item['qty'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
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