<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipeAlat;
use App\Models\Keranjang;

/**
 * Controller untuk mengelola keranjang belanja (cart)
 * Berbasis session (guest maupun user login)
 */
class KeranjangController extends Controller
{
    /**
     * Menampilkan halaman keranjang
     * Mengambil semua item keranjang berdasarkan session ID
     */
    public function index(Request $request)
    {
        // Ambil ID session saat ini
        $sessionId = $request->session()->getId();

        // Ambil semua item keranjang milik session ini
        $cart = Keranjang::where('session_id', $sessionId)->get();

        // Hitung total harga (harga x jumlah)
        $total = $cart->sum(fn($i) => $i->harga * $i->jumlah);

        // Tampilkan view keranjang
        return view('Admin.keranjang', compact('cart', 'total'));
    }

    /**
     * Menambahkan alat ke dalam keranjang
     * Jika alat sudah ada di keranjang → jumlah ditambah
     * Jika belum → buat record baru
     */
    public function tambah(Request $request, $id)
    {
        // Ambil session ID
        $sessionId = $request->session()->getId();

        // Ambil data alat berdasarkan ID (404 jika tidak ada)
        $item = TipeAlat::findOrFail($id);

        // Cek apakah alat ini sudah ada di keranjang session ini
        $existing = Keranjang::where('session_id', $sessionId)
            ->where('tipe_alat_id', $item->id)
            ->first();

        if ($existing) {
            // Jika sudah ada → tambah jumlah 1
            $existing->increment('jumlah', 1);
        } else {
            // Jika belum ada → buat item keranjang baru
            // ✅ FIXED: session_id is now inside the array
            Keranjang::create([
                'session_id'   => $sessionId, 
                'tipe_alat_id' => $item->id,
                'nama_alat'    => $item->nama_alat,
                'gambar'       => $item->gambar,
                'harga'        => $item->harga,
                'jumlah'       => 1,
            ]);
        }

        // Kembali ke halaman sebelumnya dengan pesan sukses
        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Menghapus satu item dari keranjang
     * Dibatasi hanya untuk session pemilik item
     */
    public function hapus(Request $request, $id)
    {
        // Ambil session ID
        $sessionId = $request->session()->getId();

        // Ambil item keranjang (404 jika tidak ada)
        $item = Keranjang::findOrFail($id);

        // Cegah user menghapus item milik session lain
        if ($item->session_id !== $sessionId) {
            abort(403, 'Aksi tidak diperbolehkan.');
        }

        // Hapus item dari keranjang
        $item->delete();

        // Redirect ke halaman keranjang (bukan back)
        return redirect()->route('admin.keranjang')
            ->with('success', 'Item berhasil dihapus!');
    }

    /**
     * Mengubah jumlah item di keranjang (AJAX)
     * Minimal jumlah adalah 1
     */ 
    public function updateQty(Request $request, $id)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1'
        ]);

        $item = Keranjang::find($id);

        if ($item) {
            $item->jumlah = $request->jumlah;
            $item->save();

            return response()->json(['success' => true, 'pesan' => 'Jumlah berhasil diupdate']);
        }

        return response()->json(['success' => false, 'pesan' => 'Item tidak ditemukan'], 404);
    }
    public function updateJumlah(Request $request, $id)
    {
        // Validasi input jumlah
        $request->validate([
            'jumlah' => 'required|integer|min:1'
        ]);

        // Ambil session ID
        $sessionId = $request->session()->getId();

        // Ambil item keranjang
        $item = Keranjang::findOrFail($id);

        // Pastikan item milik session ini
        if ($item->session_id !== $sessionId) {
            abort(403);
        }

        // Update jumlah item
        $item->jumlah = $request->jumlah;
        $item->save();

        return back()->with('success', 'Jumlah item diperbarui.');
    }

    /**
     * Mengosongkan seluruh keranjang milik session ini
     */
    public function clear(Request $request)
    {
        // Ambil session ID
        $sessionId = $request->session()->getId();

        // Hapus semua item keranjang berdasarkan session
        Keranjang::where('session_id', $sessionId)->delete();

        return back()->with('success', 'Keranjang dibersihkan.');
    }
}