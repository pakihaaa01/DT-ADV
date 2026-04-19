<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DT Adventure</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>

    {{-- HEADER --}}
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="{{ asset('logo.png') }}" alt="DT Adventure Logo" class="logo">
            </div>
            <nav>
                <a href="{{ route('User.dashboard') }}">BERANDA</a>
                <a href="{{ route('admin.pricelist') }}">PRICELIST</a>
                <a href="{{ route('admin.carasewa') }}">CARA SEWA</a>
                <a href="{{ route('admin.hubungi') }}">HUBUNGI KAMI</a>
                <a href="{{ route('adminn.login') }}">ADMIN</a>
            </nav>
        </div>
    </header>

    {{-- SECTION PRODUK --}}
    <section class="produk-container">

        <div class="produk-header">
            <div>
                {{-- Header 2 --}}
                <h2>Daypack</h2>
                <p>Mudah dibawa, cukup untuk perlengkapan sehari-hari digunung.</p>
            </div>

            {{-- IKON KERANJANG DINAMIS (LANGSUNG DARI DATABASE) --}}
            <a href="{{ route('admin.keranjang') }}" class="cart-icon-link">🛒
                @php
                    $sessionId = request()->session()->getId();
                    $cartCount = \App\Models\Keranjang::where('session_id', $sessionId)->sum('jumlah');
                @endphp
                <span class="cart-badge">{{ $cartCount }}</span>
            </a>
        </div>

        {{-- GRID PRODUK --}}
        <div class="produk-grid">
            @forelse($items as $item)
                <div class="produk-card">
                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_alat }}" width="100">
                    <div class="produk-info">
                        <h3>{{ $item->nama_alat }}</h3>
                        <p>{{ $item->deskripsi }}</p>
                        <div class="harga">
                            Rp {{ number_format($item->harga, 0, ',', '.') }} / hari
                        </div>
                        <button class="tambah-btn" onclick="tambahKeranjang({{ $item->id }})">Tambah</button>
                    </div>
                </div>
            @empty
                <p class="text-center">Belum ada alat pada kategori ini.</p>
            @endforelse
        </div>

    </section>

    {{-- JAVASCRIPT AJAX --}}
    <script>
    function tambahKeranjang(id) {
        fetch(`/tambah-ke-keranjang/${id}`, { 
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error("Gagal request ke server");
            return res.json();
        })
        .then(data => {
            if(data.success) {
                updateCartBadge();
                alert("Produk berhasil ditambahkan ke keranjang!");
            }
        })
        .catch(err => {
            console.error("Terjadi kesalahan:", err);
            alert("Gagal menambahkan ke keranjang.");
        });
    }

    function updateCartBadge() {
        fetch('/cart/count')
        .then(res => res.json())
        .then(data => {
            const badge = document.querySelector('.cart-badge');
            if (badge) badge.innerText = data.count;
        })
        .catch(err => console.error("Gagal update badge:", err));
    }
    </script>

</body>
</html>