<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Judul halaman yang tampil di tab browser --}}
    <title>DT Adventure</title>

    {{-- Memuat stylesheet utama dari folder public --}}
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>

    {{-- HEADER: logo dan navigasi utama --}}
    <header>
        <div class="header-container">

            {{-- Logo situs --}}
            <div class="logo-container">
                <img src="{{ asset('logo.png') }}" alt="DT Adventure Logo" class="logo">
            </div>

            {{-- Menu navigasi (named routes Laravel) --}}
            <nav>
                <a href="{{ route('User.dashboard') }}">BERANDA</a>
                <a href="{{ route('admin.pricelist') }}">PRICELIST</a>
                <a href="{{ route('admin.carasewa') }}">CARA SEWA</a>
                <a href="{{ route('admin.hubungi') }}">HUBUNGI KAMI</a>
                <a href="{{ route('adminn.login') }}">ADMIN</a>
            </nav>
        </div>
    </header>

    {{-- SECTION: daftar sub-kategori kebutuhan tracking --}}
    <section class="produk-container">

        {{-- Header kategori: judul, deskripsi singkat, dan ikon keranjang --}}
        <div class="produk-header">
            <div>
                <h2>Kebutuhan Tracking</h2>
                <p>Semua yang kamu butuhkan saat tracking ada disini, dari carrier hingga kacamata gunung.</p>
            </div>

            {{-- Ikon keranjang: menampilkan jumlah item di session (jika ada) --}}
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

        {{-- GRID: daftar sub-kategori sebagai card-link --}}
        <div class="produk-grid">

            {{-- Sub-kategori: Carrier --}}
            <a href="{{ route('admin.kategori_carrier') }}" class="card-link">
                <div class="product-card">
                    <img src="{{ asset('carrier.jpg') }}" alt="Carrier">
                    <p>Carrier</p>
                </div>
            </a>

            {{-- Sub-kategori: Daypack --}}
            <a href="{{ route('admin.kategori_daypack') }}" class="card-link">
                <div class="product-card">
                    <img src="{{ asset('daypack.jpg') }}" alt="Daypack">
                    <p>Daypack</p>
                </div>
            </a>

            {{-- Sub-kategori: Hydropack --}}
            <a href="{{ route('admin.kategori_hydropack') }}" class="card-link">
                <div class="product-card">
                    <img src="{{ asset('hydropack.jpg') }}" alt="Hydropack">
                    <p>Hydropack</p>
                </div>
            </a>

            {{-- Sub-kategori: Tracking Pole --}}
            <a href="{{ route('admin.kategori_trackingpole') }}" class="card-link">
                <div class="product-card">
                    <img src="{{ asset('trackingpole.jpg') }}" alt="Tracking Pole">
                    <p>Tracking Pole</p>
                </div>
            </a>

            {{-- Sub-kategori: Headlamp --}}
            <a href="{{ route('admin.kategori_headlamp') }}" class="card-link">
                <div class="product-card">
                    <img src="{{ asset('headlamp.jpg') }}" alt="Headlamp">
                    <p>Headlamp</p>
                </div>
            </a>

            {{-- Sub-kategori: Powerbank --}}
            <a href="{{ route('admin.kategori_powerbank') }}" class="card-link">
                <div class="product-card">
                    <img src="{{ asset('powerbank.jpg') }}" alt="Powerbank">
                    <p>Powerbank</p>
                </div>
            </a>

            {{-- Sub-kategori: Kacamata Gunung --}}
            <a href="{{ route('admin.kategori_kacamatagunung') }}" class="card-link">
                <div class="product-card">
                    <img src="{{ asset('kacamatagunung.jpg') }}" alt="Kacamata Gunung">
                    <p>Kacamata Gunung</p>
                </div>
            </a>

            {{-- Sub-kategori: Timbangan Portable --}}
            <a href="{{ route('admin.kategori_timbanganportable') }}" class="card-link">
                <div class="product-card">
                    <img src="{{ asset('timbanganportable.jpg') }}" alt="Timbangan Portable">
                    <p>Timbangan Portable</p>
                </div>
            </a>

        </div>
    </section>

</body>

</html>
