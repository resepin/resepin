@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-8 col-lg-6 text-center">
            
            @if(session('error'))
                <div class="alert alert-danger mb-4 text-start">
                    <strong>Oops!</strong> {{ session('error') }}
                </div>
            @endif

            <h1 class="display-4 fw-bold mb-3">Ada bahan apa<br>di kulkas?</h1>
            <p class="lead text-muted mb-5">
                Upload foto bahan makananmu, biar <span class="navbar-brand fw-bold">Resep<span class="oranye">.in</span></span> carikan resepnya.
            </p>

            <form action="{{ route('analyze') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="card p-3 mb-3 shadow-sm border-0">
                    <input type="file" name="image" id="file-input" class="d-none" accept="image/*" onchange="previewImage(event)" required>
                    
                    <label for="file-input" class="upload-area cursor-pointer" style="cursor: pointer;">
                        <div id="upload-placeholder" class="py-4">
                            <i class="bi bi-cloud-arrow-up text-secondary display-1"></i>
                            <h5 class="mt-3 fw-bold text-dark">Klik untuk Upload Foto</h5>
                        </div>
                        
                        <img id="image-preview" src="#" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 15px; display: none; margin: 0 auto;">
                    </label>
                </div>

                <div class="card p-4 mb-4 shadow-sm border-0 text-start">
                    <h6 class="fw-bold mb-3">⚙️ Filter & Preferensi</h6>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="form-check p-2 border rounded bg-light">
                                <input class="form-check-input ms-1" type="checkbox" name="is_halal" value="1" id="halal">
                                <label class="form-check-label fw-bold small ms-2" for="halal">Halal</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check p-2 border rounded bg-light">
                                <input class="form-check-input ms-1" type="checkbox" name="no_spicy" value="1" id="spicy">
                                <label class="form-check-label fw-bold small ms-2" for="spicy">🌶️ Gak Pedas</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">🚫 Bahan yang TIDAK mau dimasak:</label>
                        <input type="text" name="custom_exclude" class="form-control" placeholder="Contoh: shrimp, peanut (pisahkan koma)">
                        <div class="form-text text-muted" style="font-size: 12px;">Tulis bahan alergi atau yang tidak kamu suka.</div>
                    </div>

                    <label class="form-label fw-bold small">🥗 Jenis Diet (Boleh pilih banyak):</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="diets[]" value="vegetarian" id="dietVeg">
                                <label class="form-check-label small" for="dietVeg">Vegetarian</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="diets[]" value="vegan" id="dietVegan">
                                <label class="form-check-label small" for="dietVegan">Vegan</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="diets[]" value="gluten free" id="dietGluten">
                                <label class="form-check-label small" for="dietGluten">Gluten Free</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="diets[]" value="dairy free" id="dietDairy">
                                <label class="form-check-label small" for="dietDairy">Dairy Free</label>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" id="submit-btn" class="btn btn-primary btn-lg w-100 py-3 shadow-lg fw-bold">
                    🔍 Cari Resep
                </button>
                
                <!-- Status indicator untuk eager loading -->
                <div id="loading-status" class="mt-3 text-center d-none">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <div id="loading-spinner" class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span id="loading-text" class="text-muted small">Memproses...</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // ============================================
    // 🚀 EAGER LOADING - Sub-100ms "Cari Resep"
    // ============================================
    // Strategi: Panggil API OTOMATIS saat upload gambar
    // Sehingga saat klik "Cari Resep" → data sudah ready!
    
    let cachedData = null;       // Hasil akhir (ingredients + recipes)
    let isProcessing = false;    // Sedang proses?
    let currentFile = null;      // File yang sedang diproses

    function previewImage(event) {
        const file = event.target.files[0];
        if (!file) return;

        currentFile = file;

        // Tampilkan preview gambar
        const reader = new FileReader();
        reader.onload = function() {
            document.getElementById('image-preview').src = reader.result;
            document.getElementById('image-preview').style.display = 'block';
            document.getElementById('upload-placeholder').style.display = 'none';
        };
        reader.readAsDataURL(file);

        // 🔥 LANGSUNG PROSES OTOMATIS!
        startEagerLoading(file);
    }

    async function startEagerLoading(file) {
        // Reset state
        cachedData = null;
        isProcessing = true;
        
        const status = document.getElementById('loading-status');
        const spinner = document.getElementById('loading-spinner');
        const text = document.getElementById('loading-text');
        const btn = document.getElementById('submit-btn');
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Tampilkan status
        status.classList.remove('d-none');
        spinner.classList.remove('d-none');
        text.textContent = '🔍 AI sedang mengenali bahan...';
        btn.innerHTML = '⏳ Tunggu sebentar...';

        try {
            // ========== STEP 1: Azure AI ==========
            const formData = new FormData();
            formData.append('image', file);

            const aiResponse = await fetch('/api/analyze-image', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const aiData = await aiResponse.json();

            if (!aiResponse.ok || !aiData.success) {
                throw new Error(aiData.error || 'Gagal menganalisis gambar');
            }

            const ingredients = aiData.ingredients;
            text.textContent = '✅ ' + ingredients.length + ' bahan ditemukan! Mencari resep...';

            // ========== STEP 2: Spoonacular ==========
            const filters = getFilters();
            filters.ingredients = ingredients;

            const recipeResponse = await fetch('/api/search-recipes', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(filters)
            });

            const recipeData = await recipeResponse.json();

            if (!recipeResponse.ok || !recipeData.success) {
                throw new Error(recipeData.error || 'Gagal mencari resep');
            }

            // ========== SUKSES! ==========
            cachedData = recipeData;
            isProcessing = false;
            
            spinner.classList.add('d-none');
            text.innerHTML = '✅ <strong>' + (recipeData.recipes?.length || 0) + ' resep siap!</strong> Klik tombol di bawah.';
            btn.innerHTML = '🚀 Lihat Resep (Instan!)';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');

        } catch (err) {
            isProcessing = false;
            spinner.classList.add('d-none');
            text.innerHTML = '⚠️ ' + err.message + ' <a href="#" onclick="retryEagerLoading()">Coba lagi</a>';
            btn.innerHTML = '🔍 Cari Resep';
        }
    }

    function retryEagerLoading() {
        if (currentFile) {
            startEagerLoading(currentFile);
        }
        return false;
    }

    function getFilters() {
        return {
            is_halal: document.getElementById('halal').checked,
            no_spicy: document.getElementById('spicy').checked,
            custom_exclude: document.querySelector('input[name="custom_exclude"]').value,
            diets: [...document.querySelectorAll('input[name="diets[]"]:checked')].map(e => e.value)
        };
    }

    // Re-fetch recipes saat filter berubah (dengan debounce)
    let filterDebounce = null;
    function onFilterChange() {
        if (!cachedData || !cachedData.ingredients) return;
        
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(async () => {
            const text = document.getElementById('loading-text');
            const spinner = document.getElementById('loading-spinner');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            spinner.classList.remove('d-none');
            text.textContent = '🔄 Mengupdate filter...';

            try {
                const filters = getFilters();
                filters.ingredients = cachedData.ingredients;

                const response = await fetch('/api/search-recipes', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(filters)
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    cachedData = data;
                    text.innerHTML = '✅ <strong>' + (data.recipes?.length || 0) + ' resep</strong> dengan filter baru!';
                }
            } catch (e) {
                text.textContent = '⚠️ Gagal update filter';
            }
            spinner.classList.add('d-none');
        }, 500);
    }

    // Pasang listener ke semua filter
    document.getElementById('halal').addEventListener('change', onFilterChange);
    document.getElementById('spicy').addEventListener('change', onFilterChange);
    document.querySelector('input[name="custom_exclude"]').addEventListener('input', onFilterChange);
    document.querySelectorAll('input[name="diets[]"]').forEach(el => el.addEventListener('change', onFilterChange));

    // ========== FORM SUBMIT HANDLER ==========
    document.querySelector('form').addEventListener('submit', function(e) {
        // Jika data sudah ready → INSTANT REDIRECT! (<100ms)
        if (cachedData && cachedData.success) {
            e.preventDefault();
            
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Buat hidden form untuk POST data
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/api/show-cached';
            form.innerHTML = `
                <input type="hidden" name="_token" value="${csrf}">
                <input type="hidden" name="cached_data" value='${JSON.stringify(cachedData)}'>
            `;
            document.body.appendChild(form);
            form.submit();
            return;
        }

        // Jika masih processing → tampilkan pesan
        if (isProcessing) {
            e.preventDefault();
            alert('Tunggu sebentar, AI masih menganalisis gambar...');
            return;
        }

        // Tidak ada cache (user belum upload) → submit normal
        const btn = document.getElementById('submit-btn');
        btn.innerHTML = '⏳ Memproses...';
        btn.disabled = true;
    });
</script>
@endsection