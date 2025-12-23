<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // Simpan data pesanan baru
    public function store(Request $request): RedirectResponse
    {
        // Validasi input untuk memastikan data minimal yang diperlukan tersedia
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'hari' => 'required|integer|min:1',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
        ]);

        // Pastikan 'hari' sebagai integer
        $hari = (int) $data['hari'];

        // Hitung tanggal kembali berdasarkan tanggal mulai + jumlah hari
        $tanggalMulai = Carbon::parse($data['tanggal_mulai'])->startOfDay();
        $tanggalKembali = $tanggalMulai->copy()->addDays($hari);
        $data['tanggal_kembali'] = $tanggalKembali->toDateString();

        // Sambungkan pesanan ini ke session saat ini sehingga kita tahu keranjang milik siapa
        $data['session_id'] = $request->session()->getId();

        // Simpan pesanan ke database (tabel pesanan)
        $pesanan = Pesanan::create($data);

        // Simpan ID pesanan ke session agar mudah diakses selama proses checkout
        $request->session()->put('pesanan_id', $pesanan->id);

        // Redirect ke halaman checkout dengan pesan sukses
        return redirect()->route('admin.checkout', ['pesanan_id' => $pesanan->id])
            ->with('success', 'Pesanan berhasil disimpan. ID: ' . $pesanan->id);
    }


    // Tampilkan halaman checkout
    public function checkout(Request $request): View
    {
        // Ambil ID session untuk mencari keranjang yang sesuai
        $sessionId = $request->session()->getId();

        // Ambil semua item keranjang yang terkait session ini
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Keranjang> $cart */
        $cart = Keranjang::where('session_id', $sessionId)->get();

        // Hitung subtotal per hari: jumlah setiap item dikalikan harga per item
        $subtotalPerDay = $cart->sum(function (Keranjang $item): float {
            return (float) $item->harga * (int) $item->jumlah;
        });

        // Coba ambil pesanan dari query parameter atau dari session jika tersedia
        /** @var Pesanan|null $pesanan */
        $pesanan = null;
        if ($request->has('pesanan_id')) {
            $pesanan = Pesanan::find($request->query('pesanan_id'));
        } elseif ($request->session()->has('pesanan_id')) {
            $pesanan = Pesanan::find($request->session()->get('pesanan_id'));
        }

        // Jika pesanan menyewa lebih dari 1 hari, kalikan subtotal per hari
        $total = $subtotalPerDay;
        if ($pesanan && (int) $pesanan->hari > 1) {
            $total = $subtotalPerDay * (int) $pesanan->hari;
        }

        // Render halaman checkout (view: Admin.checkout) dan kirim data yang diperlukan
        return view('Admin.checkout', compact('cart', 'subtotalPerDay', 'total', 'pesanan'));
    }


    // Proses pembuatan pesanan & pembayaran
    public function placeOrder(Request $request): RedirectResponse
    {
        // Validasi input penting untuk proses pembayaran
        $request->validate([
            'pesanan_id' => 'required|integer|exists:pesanan,id',
            'metode_pembayaran' => 'required|string|in:Cash,QRIS',
            'bukti' => 'nullable|image|max:2048',
        ]);

        // Ambil pesanan yang dimaksud; jika tidak ada, tampilkan 404
        /** @var Pesanan $pesanan */
        $pesanan = Pesanan::findOrFail($request->pesanan_id);

        // Gunakan session_id yang disimpan di pesanan, atau fallback ke session saat ini
        $sessionId = $pesanan->session_id ?? $request->session()->getId();

        // Ambil isi keranjang berdasarkan session
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Keranjang> $cart */
        $cart = Keranjang::where('session_id', $sessionId)->get();

        // Jika keranjang kosong, batalkan proses
        if ($cart->isEmpty()) {
            return back()->with('error', 'Keranjang kosong. Tidak ada item untuk dipesan.');
        }

        // Mulai transaksi DB agar semua operasi sukses atau digagalkan bersamaan
        DB::beginTransaction();
        try {
            // Hitung subtotal per hari dan total (mengalikan hari sewa)
            $subtotalPerDay = $cart->sum(fn(Keranjang $item): float => (float)$item->harga * (int)$item->jumlah);
            $total = $subtotalPerDay * max(1, (int) $pesanan->hari);

            // Buat kode pembayaran unik (untuk referensi pelanggan/admin)
            $kode = 'PAY-' . strtoupper(Str::random(8));

            // Simpan record pembayaran
            $pembayaran = Pembayaran::create([
                'user_id' => Auth::id() ?? null,
                'pesanan_id' => $pesanan->id,
                'jumlah' => $total,
                'metode_pembayaran' => $request->metode_pembayaran,
                'kode_pembayaran' => $kode,
                'status' => 'pending',
            ]);

            // Jika ada file bukti (misal: foto QRIS), unggah dan simpan path di pembayaran
            if ($request->hasFile('bukti')) {
                $path = $request->file('bukti')->store('bukti_pembayaran', 'public');
                $pembayaran->update(['bukti' => $path]);
            }

            // Simpan snapshot item pesanan ke tabel terkait
            $itemsData = $cart->map(function (Keranjang $c): array {
                return [
                    'product_id' => $c->tipe_alat_id,
                    'nama_alat' => $c->nama_alat,
                    'jumlah' => $c->jumlah,
                    'harga' => $c->harga,
                    'subtotal' => (float) $c->harga * (int) $c->jumlah, // subtotal per hari
                ];
            })->toArray();

            // Gunakan relasi pesanan->items untuk membuat banyak record sekaligus
            $pesanan->items()->createMany($itemsData);

            // Setelah snapshot dibuat, kosongkan keranjang untuk session ini
            Keranjang::where('session_id', $sessionId)->delete();

            // Tentukan status pesanan berdasarkan metode pembayaran
            $status = ($request->metode_pembayaran === 'Cash')
                ? 'Menunggu Pengambilan'
                : 'Menunggu Verifikasi';

            // Update pesanan dengan informasi metode pembayaran dan status akhir
            $pesanan->update([
                'metode_pembayaran' => $request->metode_pembayaran,
                'status' => $status,
            ]);

            // Hapus id pesanan dari session karena proses checkout sudah selesai
            $request->session()->forget('pesanan_id');

            // Commit transaksi: semua perubahan disimpan permanen
            DB::commit();

            // Redirect ke halaman detail pesanan dengan pesan sukses termasuk kode pembayaran
            return redirect()->route('admin.detailpesanan', ['id' => $pesanan->id])
                ->with('success', 'Pesanan berhasil dibuat. Kode pembayaran: ' . $pembayaran->kode_pembayaran);
        } catch (\Throwable $e) {
            // Jika terjadi kesalahan, batalkan semua perubahan di database
            DB::rollBack();

            // Catat log error untuk debugging developer
            Log::error('Error placeOrder: ' . $e->getMessage());

            // Kembalikan ke halaman sebelumnya dengan pesan error yang ramah
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    // Tampilkan detail pesanan (nota)
    public function detail(int $id): View
    {
        // Ambil pesanan berikut relasi items dan pembayaran
        /** @var Pesanan $pesanan */
        $pesanan = Pesanan::with(['items', 'pembayaran'])->findOrFail($id);

        // Hitung subtotal per hari dari snapshot item di tabel pesanan_items
        $subtotalPerDay = $pesanan->items->sum(function ($item) {
            return $item->subtotal;
        });

        // Total adalah subtotal per hari dikalikan jumlah hari sewa
        $total = $subtotalPerDay * max(1, (int) $pesanan->hari);

        // Ambil pembayaran terakhir (jika ada)
        $pembayaran = $pesanan->pembayaran()->latest()->first();

        // Render view nota dan kirim data yang dibutuhkan
        return view('Admin.detailpesanan', [
            'pesanan' => $pesanan,
            'cart' => $pesanan->items,
            'subtotalPerDay' => $subtotalPerDay,
            'total' => $total,
            'pembayaran' => $pembayaran,
        ]);
    }
}
