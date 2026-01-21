<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Resep.in</title>
    
    <link rel="icon" href="{{ asset('img/resepin-logo-white.png') }}" type="image/x-icon">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    @if(file_exists(public_path('css/app.css')))
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @endif

    <style>
        body { font-family: 'Poppins', sans-serif; }
        
        /* SOLUSI AGAR TIDAK GESER KE KANAN */
        html {
            overflow-y: scroll; /* Memaksa scrollbar selalu muncul */
        }

        .oranye { color: #fd7e14; font-weight: bold; }
        .navbar-nav .nav-link { font-weight: 500; color: #555; }
        .navbar-nav .nav-link:hover { color: #fd7e14; }
        .btn-primary { background-color: #fd7e14; border-color: #fd7e14; }
        .btn-primary:hover { background-color: #e36a0e; border-color: #e36a0e; }
    </style>
  </head>
  <body>

    <nav class="navbar navbar-expand-lg fixed-top bg-white shadow-sm py-3">
      <div class="container">
        
        <a class="navbar-brand fs-4 fw-bold d-flex align-items-center" href="{{ route('home') }}">
            <img src="{{ asset('img/resepin-logo-white.png') }}" alt="Logo" style="height: 40px; margin-right: 10px;">
            <span>Resep<span class="oranye">.in</span></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Beranda</a></li>
                @guest
                    <li class="nav-item ms-lg-3"><a href="{{ route('login') }}" class="nav-link">Masuk</a></li>
                    <li class="nav-item ms-lg-2"><a href="{{ route('register') }}" class="btn btn-primary text-white px-4 rounded-pill">Daftar</a></li>
                @else
                    <li class="nav-item ms-lg-3">
                        <a href="{{ route('favorites.index') }}" class="nav-link">Favorit Saya</a>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow mt-2">
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
      </div>
    </nav>

    <main class="py-4" style="margin-top: 100px;">
        @yield('content')
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
        <div id="favToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    <span id="toast-icon"></span> <span id="toast-text">Notifikasi</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('image-preview');
                output.src = reader.result;
                output.style.display = 'block';
                document.getElementById('upload-placeholder').style.display = 'none';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>

    <script>
        function toggleFavorite(btn) {
            let id = btn.getAttribute('data-id');
            let title = btn.getAttribute('data-title');
            let image = btn.getAttribute('data-image');
            
            let tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (!tokenMeta) return; 
            let token = tokenMeta.getAttribute('content');
            
            // Efek Loading
            btn.style.opacity = "0.7";
            btn.style.pointerEvents = "none";

            fetch("{{ route('favorites.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": token
                },
                body: JSON.stringify({
                    spoonacular_id: id,
                    title: title,
                    image: image
                })
            })
            .then(response => {
                // === BAGIAN PERUBAHAN UTAMA ===
                if (response.status === 401) {
                    // Dulu: window.location.href = ... (Redirect)
                    // Sekarang: Tampilkan Pesan Warning saja
                    showToast("Login untuk menyimpan resep!", "warning");
                    throw new Error("Unauthorized"); // Hentikan proses selanjutnya
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    
                    // Ganti Icon Hati (Merah/Abu)
                    if (btn.classList.contains('btn-fav')) {
                        let icon = btn.querySelector('i');
                        if (data.action === 'added') {
                            icon.className = 'bi bi-heart-fill text-danger fs-5';
                        } else {
                            icon.className = 'bi bi-heart text-secondary fs-5';
                            if (btn.classList.contains('btn-remove-card')) {
                                btn.closest('.col-md-6').remove();
                            }
                        }
                    } else if (btn.classList.contains('btn-detail-fav')) {
                        let icon = btn.querySelector('i');
                        let textSpan = btn.querySelector('span');
                        if (data.action === 'added') {
                            btn.className = 'btn btn-outline-danger w-100 rounded-pill py-2 fw-bold btn-detail-fav';
                            icon.className = 'bi bi-heart-fill me-2';
                            textSpan.innerText = 'Disimpan di Favorit';
                        } else {
                            btn.className = 'btn btn-danger w-100 rounded-pill py-2 fw-bold shadow-sm btn-detail-fav';
                            icon.className = 'bi bi-heart me-2';
                            textSpan.innerText = 'Simpan ke Favorit';
                        }
                    }

                    // Tampilkan Toast Sukses/Hapus
                    let type = (data.action === 'removed') ? 'secondary' : 'success';
                    showToast(data.message, type);
                }
            })
            .catch(error => {
                if (error.message !== "Unauthorized") {
                    console.error('Error:', error);
                }
            })
            .finally(() => {
                btn.style.opacity = "1";
                btn.style.pointerEvents = "auto";
            });
        }

        // FUNGSI BANTUAN UNTUK MENAMPILKAN TOAST WARNA-WARNI
        function showToast(message, type) {
            let toastEl = document.getElementById('favToast');
            let toastText = document.getElementById('toast-text');
            let toastIcon = document.getElementById('toast-icon');
            
            if(toastEl) {
                toastText.innerText = message;
                
                // Reset Class Warna
                toastEl.classList.remove('bg-success', 'bg-warning', 'bg-secondary', 'bg-danger');
                
                // Set Warna & Ikon Baru
                if (type === 'success') {
                    toastEl.classList.add('bg-success');
                    toastIcon.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>';
                } else if (type === 'warning') {
                    toastEl.classList.add('bg-warning', 'text-dark'); // Kuning teks hitam
                    toastEl.classList.remove('text-white');           // Hapus teks putih khusus warning
                    toastIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
                } else if (type === 'secondary') {
                    toastEl.classList.add('bg-secondary');
                    toastIcon.innerHTML = '<i class="bi bi-trash-fill me-2"></i>';
                }

                // Kembalikan teks putih jika bukan warning
                if (type !== 'warning') {
                    toastEl.classList.add('text-white');
                    toastEl.classList.remove('text-dark');
                }

                let toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        }
    </script>
    
    @stack('scripts')
  </body>
</html>