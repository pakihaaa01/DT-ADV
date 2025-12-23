<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Judul halaman di tab browser --}}
    <title>DT Adventure</title>

    {{-- Memuat file CSS utama --}}
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>

    {{-- HEADER: logo & navigasi --}}
    <header>
        <div class="header-container">

            {{-- Logo situs --}}
            <div class="logo-container">
                <img src="{{ asset('logo.png') }}" alt="DT Adventure Logo" class="logo">
            </div>

            {{-- Navigasi utama --}}
            <nav>
                <a href="{{ route('User.dashboard') }}">BERANDA</a>
                <a href="{{ route('admin.pricelist') }}">PRICELIST</a>
                <a href="{{ route('admin.carasewa') }}">CARA SEWA</a>
                <a href="{{ route('admin.hubungi') }}">HUBUNGI KAMI</a>
                <a href="{{ route('adminn.login') }}">ADMIN</a>
            </nav>
        </div>
    </header>

    {{-- Notifikasi sukses setelah tambah ke keranjang --}}
    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- SECTION: daftar produk kategori tenda & alat tidur --}}
    <section class="produk-container">

        {{-- Header kategori --}}
        <div class="produk-header">
            <div>
                <h2>Tenda dan Alat Tidur</h2>
                <p>Istirahat seru di tengah alam dengan pilihan tenda dan alat tidur terbaik.</p>
            </div>

            {{-- Ikon menuju halaman keranjang --}}
            <a href="{{ route('admin.keranjang') }}" class="cart-icon-link">🛒</a>
        </div>

        {{-- Grid produk --}}
        <div class="produk-grid">

            {{-- Loop setiap item dalam kategori --}}
            @forelse($items as $item)
                <div class="produk-card">

                    {{-- Gambar item diambil dari storage --}}
                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_alat }}" width="100">

                    <div class="produk-info">

                        {{-- Nama alat --}}
                        <h3>{{ $item->nama_alat }}</h3>

                        {{-- Deskripsi alat --}}
                        <p>{{ $item->deskripsi }}</p>

                        {{-- Harga sewa / hari --}}
                        <div class="harga">
                            Rp {{ number_format($item->harga_sewa, 0, ',', '.') }} / hari
                        </div>

                        {{-- Tombol tambah ke keranjang --}}
                        <form action="{{ route('tambah.keranjang', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="tambah-btn">Tambah</button>
                        </form>

                    </div>
                </div>

                {{-- Jika tidak ada item --}}
            @empty
                <p class="text-center">Belum ada alat pada kategori ini.</p>
            @endforelse

        </div>

    </section>

</body>

</html>
