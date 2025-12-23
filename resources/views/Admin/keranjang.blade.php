<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="cart-page">
        <div class="cart-header">
            <!-- Gunakan history.back() supaya mengandalkan riwayat browser -->
            <a href="#" class="back-btn" onclick="history.back(); return false;">
                <span class="arrow">←</span> Kembali
            </a>
            <h2>Keranjang </h2>
        </div>

        <div class="cart-container">
            <h2>Keranjang Saya ({{ count($cart) }})</h2>

            @if (count($cart) > 0)
                @foreach ($cart as $item)
                    <div class="cart-item">
                        <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_alat }}" width="100">
                        <div class="cart-info">
                            <h4>{{ $item->nama_alat }}</h4>
                            <p>Rp {{ number_format($item->harga, 0, ',', '.') }}</p>
                            <p>Jumlah: {{ $item->jumlah }}</p>
                        </div>

                        <form id="delete-form-{{ $item->id }}" action="{{ route('keranjang.hapus', $item->id) }}"
                            method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <!-- gunakan data-id, button type button sehingga tidak submit form langsung -->
                            <button type="button" class="btn-hapus" data-id="{{ $item->id }}">
                                Hapus
                            </button>
                        </form>
                    </div>
                @endforeach
            @else
                <p>Keranjang masih kosong.</p>
            @endif

            <div style="text-align: right; width: 100%; margin-top: 20px;">
                <a href="{{ route('admin.isidata') }}" style="text-decoration:none; display:inline-block;">
                    <button class="checkout-btn" style="width:auto; padding:10px 30px; display:inline-block;">
                        Checkout
                    </button>
                </a>
            </div>
        </div>
    </div>

    <!-- JS: definisikan confirmDelete sekali -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delegation atau per-button binding
            document.querySelectorAll('.btn-hapus').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        text: "Item ini akan dihapus dari keranjangmu.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('delete-form-' + id).submit();
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>
