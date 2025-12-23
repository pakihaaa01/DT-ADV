<?php

namespace App\Http\Controllers;

use App\Models\TipeAlat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManajemenAlatController extends Controller
{
    // TAMPILKAN DAFTAR ALAT & KATEGORI
    // Mengambil semua kategori dan tipe alat untuk ditampilkan pada
    // halaman manajemen barang. Cocok untuk halaman admin yang menampilkan
    // tabel/list produk.
    public function index()
    {
        // Ambil semua kategori (untuk dropdown/filter)
        $kategori = \App\Models\KategoriAlat::all();

        // Ambil semua tipe alat beserta relasi kategori, urut berdasarkan yang terbaru
        $items = \App\Models\TipeAlat::with('kategori')->latest()->get();

        return view('adminn.barang', compact('kategori', 'items'));
    }


    // SIMPAN ALAT BARU
    // Validasi input dari form tambah barang, simpan gambar jika ada,
    // lalu buat record baru di tabel tipe_alat.
    public function store(Request $request)
    {
        // Validasi input: pastikan data yang wajib ada sudah benar
        $request->validate([
            'kategori_id' => 'required|exists:kategori_alat,id',
            'nama_alat' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'harga_sewa' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Jika ada file gambar, simpan ke storage (public)
        $path = $request->hasFile('gambar')
            ? $request->file('gambar')->store('gambar_alat', 'public')
            : null;

        // Buat record tipe alat baru
        TipeAlat::create([
            'kategori_id' => $request->kategori_id,
            'nama_alat' => $request->nama_alat,
            'stok' => $request->stok,
            'harga_sewa' => $request->harga_sewa,
            'deskripsi' => $request->deskripsi,
            'gambar' => $path,
        ]);

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan!');
    }


    // PERBARUI DATA ALAT
    // Memperbarui data alat yang sudah ada. Jika ada gambar baru,
    // gambar lama akan dihapus dari penyimpanan dan diganti.
    public function update(Request $request, $id)
    {
        // Cari item atau tampilkan 404 jika tidak ditemukan
        $item = TipeAlat::findOrFail($id);

        // Validasi input update
        $request->validate([
            'kategori_id' => 'required|exists:kategori_alat,id',
            'nama_alat' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'harga_sewa' => 'required|numeric|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'deskripsi' => 'nullable|string',
        ]);

        // Jika ada file gambar baru, simpan dulu dan hapus file lama jika ada
        if ($request->hasFile('gambar')) {
            // Simpan gambar baru di storage/app/public/gambar
            $path = $request->file('gambar')->store('gambar', 'public');

            // Hapus gambar lama jika masih ada di disk public
            if ($item->gambar && Storage::disk('public')->exists($item->gambar)) {
                Storage::disk('public')->delete($item->gambar);
            }

            // Set path gambar baru ke model
            $item->gambar = $path;
        }

        // Perbarui field lain
        $item->kategori_id = $request->kategori_id;
        $item->nama_alat = $request->nama_alat;
        $item->stok = $request->stok;
        $item->harga_sewa = $request->harga_sewa;
        $item->deskripsi = $request->deskripsi;
        $item->save();

        return redirect()->route('adminn.barang.index')->with('success', 'Data alat berhasil diperbarui.');
    }


    // HAPUS ALAT
    // Menghapus record tipe alat dan menghapus file gambar yang terkait jika ada.
    public function destroy($id)
    {
        $item = TipeAlat::findOrFail($id);

        // Jika ada gambar tersimpan, hapus dari disk public
        if ($item->gambar && Storage::disk('public')->exists($item->gambar)) {
            Storage::disk('public')->delete($item->gambar);
        }

        // Hapus record dari database
        $item->delete();

        return redirect()->back()->with('success', 'Barang berhasil dihapus!');
    }
}
