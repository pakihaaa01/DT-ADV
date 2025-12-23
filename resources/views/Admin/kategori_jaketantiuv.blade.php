<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Judul halaman --}}
    <title>DT Adventure</title>

    {{-- Memuat file CSS utama --}}
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>

    {{-- HEADER: logo & navigasi utama --}}
    <header>
        <div class="header-container">

            {{-- Logo aplikasi --}}
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

    {{-- SECTION: daftar produk kategori --}}
    <section class="produk-container">

        {{-- Header kategori + ikon keranjang --}}
        <div class="produk-header">
            <div>
                <h2>Jaket Anti UV</h2>
                <p>Jaket outdoor stylish dan tahan cuaca.</p>
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

        {{-- Pesan sukses setelah tambah produk --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- GRID: daftar item dalam kategori --}}
        <div class="produk-grid">

            {{-- Looping semua item --}}
            @forelse($items as $item)
                <div class="produk-card">

                    {{-- Gambar item --}}
                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_alat }}" width="100">

                    <div class="produk-info">

                        {{-- Nama item --}}
                        <h3>{{ $item->nama_alat }}</h3>

                        {{-- Deskripsi singkat --}}
                        <p>{{ $item->deskripsi }}</p>

                        {{-- Harga sewa --}}
                        <div class="harga">
                            Rp {{ number_format($item->harga_sewa, 0, ',', '.') }} / hari
                        </div>

                        {{-- Tombol tambah ke keranjang --}}
                        <form action="{{ route('tambah.keranjang', $item->id) }}" method=
