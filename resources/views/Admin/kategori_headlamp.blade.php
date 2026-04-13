<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Judul halaman --}}
    <title>DT Adventure</title>

    {{-- File CSS utama --}}
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>

    {{-- HEADER: logo + navigasi --}}
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

    {{-- SECTION: daftar produk kategori --}}
    <section class="produk-container">

        {{-- Header kategori --}}
        <div class="produk-header">
            <div>
                <h2>Headlamp</h2>
                <p>Terang dan ringan, nyaman dipakai saat trekking malam.</p>
            </div>

            {{-- Ikon keranjang + jumlah item --}}
            <a href="{{ route('admin.keranjang') }}" class="cart-icon-link">
                🛒
                @php
                    $cartCount = session('cart') ? array_sum(array_column(session('cart'), 'jumlah')) : 0;
                @endphp

                @if ($cartCount > 0)
                    <span class="cart-count">{{ $cartCount }}</span>
                @endif
            </a>
        </div>

        {{-- Notifikasi sukses --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- GRID produk --}}
        <div class="produk-grid">

            {{-- Loop item --}}
            @forelse($items as $item)
                <div class="produk-card">

                    {{-- Gambar item --}}
                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_alat }}" width="100">

                    <div class="produk-info">

                        {{-- Nama alat --}}
                        <h3>{{ $item->nama_alat }}</h3>

                        {{-- Deskripsi alat --}}
                        <p>{{ $item->deskripsi }}</p>

                        {{-- Harga sewa --}}
                        <div class="harga">
                            Rp {{ number_format($item->harga, 0, ',', '.') }} / hari
                        </div>

                        {{-- Tombol tambah ke keranjang --}}
                        <form action="{{ route('tambah.keranjang', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="tambah-btn">Tambah</button>
                        </form>

                    </div>
                </div>

                {{-- Jika kategori kosong --}}
            @empty
                <p class="text-center">Belum ada alat pada kategori ini.</p>
            @endforelse

        </div>

    </section>

</body>

</html>
