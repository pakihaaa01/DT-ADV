<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class PesananController extends Controller
{
    /**
     * History pesanan user login
     */
    public function index()
    {
        try {
            $userId = auth()->id();

            $pesanan = DB::table('pesanan')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($pesanan as $item) {
                $item->items = DB::table('pesanan_items')
                    ->where('pesanan_id', $item->id)
                    ->get();

                $item->pembayaran = DB::table('pembayaran')
                    ->where('pesanan_id', $item->id)
                    ->first();
            }

            return response()->json([
                'success' => true,
                'message' => 'Data riwayat pesanan berhasil diambil',
                'data' => $pesanan
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buat pesanan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'whatsapp' => 'required',
            'email' => 'required|email',
            'metode_pembayaran' => 'required|in:Cash,QRIS',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
            'total_harga' => 'required|numeric',

            'items' => 'required|array|min:1',
            'items.*.tipe_alat_id' => 'required',
            'items.*.qty' => 'required|integer|min:1',

            'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        DB::beginTransaction();

        try {

            $userId = auth()->id();

            $mulai = Carbon::parse($request->tgl_mulai);
            $selesai = Carbon::parse($request->tgl_selesai);

            $durasiHari = $mulai->diffInDays($selesai);

            if ($durasiHari < 1) {
                $durasiHari = 1;
            }

            $statusPesanan = match ($request->metode_pembayaran) {
                'Cash' => 'Menunggu Pengambilan',
                'QRIS' => 'Menunggu Verifikasi',
                default => 'Menunggu Konfirmasi'
            };

            /**
             * Upload bukti pembayaran
             */
            $buktiBayarPath = null;

            if ($request->hasFile('bukti_pembayaran')) {

                $file = $request->file('bukti_pembayaran');

                $namaFile =
                    time() . '_' .
                    Str::slug(pathinfo(
                        $file->getClientOriginalName(),
                        PATHINFO_FILENAME
                    )) .
                    '.' .
                    $file->getClientOriginalExtension();

                $file->move(
                    public_path('storage/bukti_pembayaran'),
                    $namaFile
                );

                $buktiBayarPath =
                    'storage/bukti_pembayaran/' . $namaFile;
            }

            /**
             * Simpan pesanan
             */
            $pesananId = DB::table('pesanan')->insertGetId([
                'user_id' => $userId,
                'nama' => $request->nama,
                'whatsapp' => $request->whatsapp,
                'email' => $request->email,
                'hari' => $durasiHari,
                'tanggal_mulai' => $request->tgl_mulai,
                'tanggal_kembali' => $request->tgl_selesai,
                'session_id' => null,
                'status' => $statusPesanan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /**
             * Simpan item pesanan
             */
            foreach ($request->items as $item) {

                $alat = DB::table('tipe_alat')
                    ->where('id', $item['tipe_alat_id'])
                    ->first();

                if (!$alat) {
                    throw new Exception('Produk tidak ditemukan');
                }

                if ($alat->stok < $item['qty']) {
                    throw new Exception(
                        'Stok ' .
                        ($alat->nama_alat ?? 'alat') .
                        ' tidak mencukupi'
                    );
                }

                DB::table('pesanan_items')->insert([
                    'pesanan_id' => $pesananId,
                    'product_id' => $item['tipe_alat_id'],
                    'nama_alat' => $alat->nama_alat,
                    'jumlah' => $item['qty'],
                    'harga' => $alat->harga,
                    'subtotal' => $alat->harga * $item['qty'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /**
                 * Kurangi stok
                 */
                DB::table('tipe_alat')
                    ->where('id', $item['tipe_alat_id'])
                    ->decrement('stok', $item['qty']);
            }

            /**
             * Simpan pembayaran
             */
            DB::table('pembayaran')->insert([
                'user_id' => $userId,
                'pesanan_id' => $pesananId,
                'jumlah' => $request->total_harga,
                'metode_pembayaran' => $request->metode_pembayaran,
                'kode_pembayaran' => 'PAY-' . strtoupper(Str::random(6)),
                'status' => 'pending',
                'bukti' => $buktiBayarPath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
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