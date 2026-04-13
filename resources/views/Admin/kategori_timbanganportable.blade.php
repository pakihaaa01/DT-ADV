<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Judul halaman yang muncul di tab browser --}}
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

            {{-- Menu navigasi (menggunakan named routes Laravel) --}}
            <nav>
                <a href="{{ route('User.dashboard') }}">BERANDA</a>
                <a href="{{ route('admin.pricelist') }}">PRICELIST</a>
                <a href="{{ route('admin.carasewa') }}">CARA SEWA</a>
                <a href="{{ route('admin.hubungi') }}">HUBUNGI KAMI</a>
                <a href="{{ route('adminn.login') }}">ADMIN</a>
            </nav>
        </div>
    </header>

    {{-- MAIN: daftar produk pada kategori "Timbangan Portable" --}}
    <section class="produk-container">

        {{-- Header kategori: judul, deskripsi, dan ikon keranjang --}}
        <div class="produk-header">
            <div>
                <h2>Timbangan Portable</h2>
                <p>Mengecek berat tas atau perlengkapan dengan mudah.</p>
            </div>

            {{-- Ikon keranjang: menampilkan jumlah item dari session --}}
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

        {{-- Pesan sukses setelah menambahkan ke keranjang --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Grid produk --}}
        <div class="produk-grid">
            {{-- Loop item produk; jika kosong tampilkan pesan --}}
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
                        <div class="harga">Rp {{ number_format($item->harga, 0, ',', '.') }} / hari</div>

                        {{-- Form untuk menambah produk ke keranjang --}}
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
