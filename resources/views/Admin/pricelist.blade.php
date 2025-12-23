<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Judul halaman pada tab browser --}}
    <title>Pricelist | DT Adventure</title>

    {{-- Memuat file CSS utama dari folder public --}}
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>

    {{-- HEADER: logo dan navigasi --}}
    <header>
        <div class="header-container">

            {{-- Logo situs --}}
            <div class="logo-container">
                <img src="{{ asset('logo.png') }}" alt="DT Adventure Logo" class="logo">
            </div>

            {{-- Navigasi menuju halaman penting --}}
            <nav>
                <a href="{{ route('User.dashboard') }}">BERANDA</a>
                <a href="{{ route('admin.pricelist') }}">PRICELIST</a>
                <a href="{{ route('admin.carasewa') }}">CARA SEWA</a>
                <a href="{{ route('admin.hubungi') }}">HUBUNGI KAMI</a>
                <a href="{{ route('adminn.login') }}">ADMIN</a>
            </nav>

        </div>
    </header>

    {{-- SECTION: Judul pricelist dan deskripsi --}}
    <section class="kategori">
        <h1>PRICELIST</h1>

        <p>
            Temukan berbagai perlengkapan petualangan terbaik untuk mendukung setiap langkahmu menuju puncak tertinggi.
        </p>

        {{-- Style khusus tombol keranjang (inline CSS) --}}
        <style>
            .cart-press {
                transition: transform 0.2s ease, background 0.2s ease;
            }

            .cart-press:hover {
                transform: scale(1.05);
                background: rgba(255, 255, 255, 0.25);
            }

            .cart-press:active {
                transform: scale(0.94);
            }
        </style>

        {{-- Tombol untuk menuju keranjang penyewaan --}}
        <div class="cart-press"
            style="display:inline-block;
                   padding:10px 20px;
                   background:rgba(255,255,255,0.15);
                   border-radius:12px;
                   margin-top:20px;">

            {{-- Link menuju halaman keranjang --}}
            <a href="{{ route('admin.keranjang') }}" class="cart-icon-link">

                Keranjang Saya 🛒

                {{-- Hitung total item di keranjang (mengambil dari session Laravel) --}}
                @php
                    $cartCount = session('cart') ? array_sum(array_column(session('cart'), 'jumlah')) : 0;
                @endphp

                {{-- Jika ada isi keranjang, tampilkan jumlahnya --}}
                @if ($cartCount > 0)
                    <span class="cart-count">{{ $cartCount }}</span>
                @endif
            </a>

        </div>

    </section>

    {{-- SECTION: Daftar kategori produk --}}
    <section class="pricelist-container">

        {{-- Kategori: Tenda & Alat Kamp --}}
        <a href="{{ route('admin.kategori_tenda') }}" class="card-link">
            <div class="product-card">
                <img src="{{ asset('tenda.jpg') }}" alt="Tenda dan Alat Kamp">
                <h3>Tenda dan Alat Kamp</h3>
            </div>
        </a>

        {{-- Kategori: Kebutuhan Tracking --}}
        <a href="{{ route('admin.kategori_kebutuhantracking') }}" class="card-link">
            <div class="product-card">
                <img src="{{ asset('kebutuhantracking.jpg') }}" alt="Kebutuhan Tracking">
                <h3>Kebutuhan Tracking</h3>
            </div>
        </a>

        {{-- Kategori: Peralatan Masak --}}
        <a href="{{ route('admin.kategori_peralatanmasak') }}" class="card-link">
            <div class="product-card">
                <img src="{{ asset('peralatanmasak.jpg') }}" alt="Peralatan Masak">
                <h3>Peralatan Masak</h3>
            </div>
        </a>

        {{-- Kategori: Outfit Outdoor --}}
        <a href="{{ route('admin.kategori_outfitoutdoor') }}" class="card-link">
            <div class="product-card">
                <img src="{{ asset('outfitoutdoor.jpg') }}" alt="Outfit Outdoor">
                <h3>Outfit Outdoor</h3>
            </div>
        </a>

        {{-- Kategori: Perlengkapan Tambahan --}}
        <a href="{{ route('admin.kategori_perlengkapantambahan') }}" class="card-link">
            <div class="product-card">
                <img src="{{ asset('perlengkapantambahan.jpg') }}" alt="Perlengkapan Tambahan">
                <h3>Perlengkapan Tambahan</h3>
            </div>
        </a>

    </section>

    {{-- FOOTER --}}
    <footer>
        <p>&copy; 2025 DT Adventure. All rights reserved.</p>
    </footer>

</body>

</html>
