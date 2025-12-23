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

    {{-- HEADER: logo & navigasi utama --}}
    <header>
        <div class="header-container">

            {{-- Logo situs --}}
            <div class="logo-container">
                <img src="{{ asset('logo.png') }}" alt="DT Adventure Logo" class="logo">
            </div>

            {{-- Navigasi --}}
            <nav>
                <a href="{{ route('User.dashboard') }}">BERANDA</a>
                <a href="{{ route('admin.pricelist') }}">PRICELIST</a>
                <a href="{{ route('admin.carasewa') }}">CARA SEWA</a>
                <a href="{{ route('admin.hubungi') }}">HUBUNGI KAMI</a>
                <a href="{{ route('adminn.login') }}">ADMIN</a>
            </nav>
        </div>
    </header>

    {{-- SECTION: daftar produk kategori Carrier --}}
    <section class="produk-container">

        {{-- Header kategori: judul, deskripsi, dan ikon keranjang --}}
        <div class="produk-header">
            <div>
                <h2>Carrier</h2>
                <p>Solusi penyimpanan utama saat mendaki, kuat, ergonomis, dan muat banyak barang.</p>
            </div>

            {{-- Ikon keranjang: tampilkan jumlah item jika ada --}}
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

        {{-- Notifikasi sukses setelah menambah ke keranjang --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Grid produk --}}
        <div class="produk-grid">
            {{-- Loop produk; jika kosong tampilkan pesan --}}
            @forelse($items as $item)
                <div class="produk-card">
                    {{-- Gambar produk (disimpan di storage) --}}
                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_alat }}" width="100">

                    <div class="produk-info">
                        {{-- Nama produk --}}
                        <h3>{{ $item->nama_alat }}</h3>

                        {{-- Deskripsi singkat --}}
                        <p>{{ $item->deskripsi }}</p>

                        {{-- Harga sewa per hari --}}
                        <div class="harga">Rp {{ number_format($item->harga_sewa, 0, ',', '.') }} / hari</div>

                        {{-- Form tambah ke keranjang --}}
                        <form action="{{ route('tambah.keranjang', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="tambah-btn">Tambah</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-center">Belum ada alat pada kategori ini.</p>
            @endforelse
        </div>

    </section>

</body>

</html>
