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
    // =========================
    // 1. SIMPAN DATA PESANAN
    // =========================
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'hari' => 'required|integer|min:1',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
        ]);

        $hari = (int) $data['hari'];

        $tanggalMulai = Carbon::parse($data['tanggal_mulai']);
        $data['tanggal_kembali'] = $tanggalMulai->copy()->addDays($hari)->toDateString();

        $data['session_id'] = $request->session()->getId();

        $pesanan = Pesanan::create($data);

        $request->session()->put('pesanan_id', $pesanan->id);

        return redirect()->route('admin.checkout', ['pesanan_id' => $pesanan->id]);
    }

    // =========================
    // 2. HALAMAN CHECKOUT
    // =========================
    public function checkout(Request $request): View
    {
        $sessionId = $request->session()->getId();

        $cart = Keranjang::where('session_id', $sessionId)->get();

        $subtotalPerDay = $cart->sum(fn($item) => $item->harga * $item->jumlah);

        $pesanan = Pesanan::find(
            $request->query('pesanan_id') ?? $request->session()->get('pesanan_id')
        );

        $total = $pesanan
            ? $subtotalPerDay * max(1, $pesanan->hari)
            : $subtotalPerDay;

        return view('User.checkout', compact('cart', 'subtotalPerDay', 'total', 'pesanan'));
    }

    // =========================
    // 3. PROSES PESANAN
    // =========================
    public function placeOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'pesanan_id' => 'required|exists:pesanan,id',
            'metode_pembayaran' => 'required|in:Cash,QRIS',
            'bukti' => 'nullable|image|max:2048',
        ]);

        $pesanan = Pesanan::findOrFail($request->pesanan_id);

        $sessionId = $pesanan->session_id ?? $request->session()->getId();

        $cart = Keranjang::where('session_id', $sessionId)->get();

        if ($cart->isEmpty()) {
            return back()->with('error', 'Keranjang kosong.');
        }

        DB::beginTransaction();

        try {
            // Hitung total
            $subtotal = $cart->sum(fn($i) => $i->harga * $i->jumlah);
            $total = $subtotal * max(1, $pesanan->hari);

            // Simpan pembayaran
            $pembayaran = Pembayaran::create([
                'user_id' => Auth::id(),
                'pesanan_id' => $pesanan->id,
                'jumlah' => $total,
                'metode_pembayaran' => $request->metode_pembayaran,
                'kode_pembayaran' => 'PAY-' . strtoupper(Str::random(6)),
                'status' => 'pending',
            ]);

            // Upload bukti
            if ($request->hasFile('bukti')) {
                $path = $request->file('bukti')->store('bukti', 'public');
                $pembayaran->update(['bukti' => $path]);
            }

            // Simpan item pesanan
            $items = $cart->map(fn($c) => [
                'product_id' => $c->tipe_alat_id,
                'nama_alat' => $c->nama_alat,
                'jumlah' => $c->jumlah,
                'harga' => $c->harga,
                'subtotal' => $c->harga * $c->jumlah,
            ]);

            $pesanan->items()->createMany($items->toArray());

            // Hapus keranjang
            Keranjang::where('session_id', $sessionId)->delete();

            // Update status
            $pesanan->update([
                'metode_pembayaran' => $request->metode_pembayaran,
                'status' => $request->metode_pembayaran === 'Cash'
                    ? 'Menunggu Pengambilan'
                    : 'Menunggu Verifikasi'
            ]);

            $request->session()->forget('pesanan_id');

            DB::commit();

            // 🔥 INI YANG PENTING
            return redirect()->route('admin.detailpesanan', $pesanan->id);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return back()->with('error', 'Gagal membuat pesanan');
        }
    }

    // =========================
    // 4. DETAIL PESANAN
    // =========================
    public function detail($id): View
    {
        $pesanan = Pesanan::with(['items', 'pembayaran'])->findOrFail($id);

        $subtotal = $pesanan->items->sum('subtotal');
        $total = $subtotal * max(1, $pesanan->hari);

        return view('Admin.detailpesanan', compact('pesanan', 'total'));
    }
}