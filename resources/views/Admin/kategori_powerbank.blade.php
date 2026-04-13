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

    {{-- HEADER: logo & navigasi --}}
    <header>
        <div class="header-container">

            {{-- Logo utama --}}
            <div class="logo-container">
                <img src="{{ asset('logo.png') }}" alt="DT Adventure Logo" class="logo">
            </div>

            {{-- Navigasi halaman --}}
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
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- SECTION: daftar produk kategori Powerbank --}}
    <section class="produk-container">

        {{-- Header kategori + ikon keranjang --}}
        <div class="produk-header">
            <div>
                <h2>Powerbank</h2>
                <p>Cadangan daya praktis untuk gadget saat di gunung.</p>
            </div>

            {{-- Ikon keranjang + jumlah item --}}
            <a href="{{ route('admin.keranjang') }}" class="cart-icon-link">
                🛒

                @php
                    $cartCount = session('cart') ? array_sum(array_column(session('cart'), 'jumlah')) : 0;
                @endphp

                {{-- Tampilkan angka jika ada item --}}
                @if ($cartCount > 0)
                    <span class="cart-count">{{ $cartCount }}</span>
                @endif
            </a>
        </div>

        {{-- Grid produk --}}
        <div class="produk-grid">

            {{-- Loop item produk --}}
            @forelse($items as $item)
                <div class="produk-card">

                    {{-- Gambar item --}}
                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_alat }}" width="100">

                    <div class="produk-info">

                        {{-- Nama produk --}}
                        <h3>{{ $item->nama_alat }}</h3>

                        {{-- Deskripsi produk --}}
                        <p>{{ $item->deskripsi }}</p>

                        {{-- Harga sewa per hari --}}
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
