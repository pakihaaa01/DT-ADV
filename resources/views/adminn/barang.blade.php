@extends('layouts.app')

@section('title', 'Manajemen Alat Camping')

@section('content')
    <div class="container py-5">

        <!-- Judul utama halaman -->
        <h2 class="fw-bold mb-4 text-center">Manajemen Alat Camping</h2>

        <!-- Tombol kembali ke dashboard -->
        <div class="mb-3 text-end">
            <a href="{{ route('User.dashboard') }}" class="btn btn-outline-primary">
                <i class="bi bi-telephone"></i>Ke Menu Dashboard
            </a>
        </div>

        <!-- Menampilkan pesan sukses jika data berhasil disimpan / diupdate -->
        @if (session('success'))
            <div class="alert alert-success text-center">{{ session('success') }}</div>
        @endif

        <!-- Menampilkan pesan kesalahan jika ada input yang salah -->
        @if ($errors->any())
            <div class="alert alert-danger text-center">
                <strong>{{ $errors->first() }}</strong>
            </div>
        @endif

        <!-- FORM TAMBAH ALAT BARU -->
        <!-- Bagian ini untuk admin menambah alat camping baru -->
        <div class="card mb-5 border-0 shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Tambah Alat Baru</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('adminn.barang.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Pilih kategori dan isi nama alat -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select name="kategori_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($kategori as $kat)
                                    <!-- Menampilkan daftar kategori dari database -->
                                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nama Alat</label>
                            <input type="text" name="nama_alat" class="form-control" required
                                placeholder="Contoh: Sleeping Bag">
                        </div>
                    </div>

                    <!-- Input stok, harga sewa, dan gambar -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Stok</label>
                            <input type="number" name="stok" class="form-control" min="0" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Harga Sewa (Rp)</label>
                            <input type="number" name="harga" class="form-control" min="0" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Gambar</label>
                            <!-- Gambar tidak wajib, boleh dikosongkan -->
                            <input type="file" name="gambar" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <!-- Input deskripsi alat -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tuliskan deskripsi alat..."></textarea>
                    </div>

                    <!-- Tombol untuk menyimpan alat baru -->
                    <button type="submit" class="btn btn-success w-100 fw-semibold">+ Tambah Alat</button>
                </form>
            </div>
        </div>

        <!-- TABEL DAFTAR ALAT YANG SUDAH ADA -->
        <div class="card border-0 shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Daftar Alat Camping</h5>
            </div>

            <div class="card-body">
                @if ($items->count())
                    <div class="table-responsive">
                        <table class="table-bordered table text-center align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Gambar</th>
                                    <th>Nama Alat</th>
                                    <th>Kategori</th>
                                    <th>Stok</th>
                                    <th>Harga Sewa</th>
                                    <th>Deskripsi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($items as $index => $item)
                                    <tr>
                                        <!-- Nomor urut -->
                                        <td>{{ $index + 1 }}</td>

                                        <!-- Menampilkan gambar alat -->
                                        <td>
                                            @if ($item->gambar)
                                                <img src="{{ asset('storage/' . $item->gambar) }}" width="80"
                                                    class="rounded">
                                            @else
                                                <small class="text-muted">Tidak ada</small>
                                            @endif
                                        </td>

                                        <!-- Informasi alat -->
                                        <td>{{ $item->nama_alat }}</td>
                                        <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
                                        <td>{{ $item->stok }}</td>
                                        <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                        <td>{{ $item->deskripsi }}</td>

                                        <!-- Tombol untuk edit & hapus -->
                                        <td>
                                            <!-- Tombol edit (menampilkan modal) -->
                                            <button type="button" class="btn btn-warning btn-sm mb-1"
                                                data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                                                Edit
                                            </button>

                                            <!-- Tombol hapus (dengan konfirmasi sebelum menghapus) -->
                                            <form action="{{ route('adminn.barang.destroy', $item->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin hapus item ini?')" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- MODAL EDIT DATA ALAT -->
                                    <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1"
                                        aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">

                                                <form action="{{ route('adminn.barang.update', $item->id) }}"
                                                    method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')

                                                    <!-- Judul modal -->
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            Edit: {{ $item->nama_alat }}
                                                        </h5>

                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <!-- Isi form edit -->
                                                    <div class="modal-body">
                                                        <!-- Edit kategori dan nama -->
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Kategori</label>
                                                                <select name="kategori_id" class="form-select" required>
                                                                    @foreach ($kategori as $kat)
                                                                        <option value="{{ $kat->id }}"
                                                                            {{ $kat->id == $item->kategori_id ? 'selected' : '' }}>
                                                                            {{ $kat->nama_kategori }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Nama Alat</label>
                                                                <input type="text" name="nama_alat"
                                                                    class="form-control" value="{{ $item->nama_alat }}"
                                                                    required>
                                                            </div>
                                                        </div>

                                                        <!-- Edit stok, harga, gambar -->
                                                        <div class="row">
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label">Stok</label>
                                                                <input type="number" name="stok" class="form-control"
                                                                    value="{{ $item->stok }}" min="0" required>
                                                            </div>

                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label">Harga Sewa (Rp)</label>
                                                                <input type="number" name="harga"
                                                                    class="form-control" value="{{ $item->harga }}"
                                                                    min="0" required>
                                                            </div>

                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label">Gambar (opsional)</label>
                                                                <input type="file" name="gambar"
                                                                    class="form-control">

                                                                <!-- Menampilkan gambar lama -->
                                                                @if ($item->gambar)
                                                                    <img src="{{ asset('storage/' . $item->gambar) }}"
                                                                        width="100" class="mt-2 rounded">
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Edit deskripsi -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Deskripsi</label>
                                                            <textarea name="deskripsi" class="form-control" rows="3">{{ $item->deskripsi }}</textarea>
                                                        </div>
                                                    </div>

                                                    <!-- Tombol simpan perubahan -->
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Simpan
                                                            Perubahan</button>
                                                    </div>

                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                    <!-- END MODAL EDIT -->
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Jika tidak ada alat -->
                    <p class="text-muted text-center">Belum ada alat camping yang ditambahkan.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
