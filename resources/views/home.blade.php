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

                <button type="submit" class="btn btn-primary btn-lg w-100 py-3 shadow-lg fw-bold">
                    🔍 Cari Resep
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('image-preview');
            var placeholder = document.getElementById('upload-placeholder');
            
            // Tampilkan gambar, sembunyikan placeholder icon
            output.src = reader.result;
            output.style.display = 'block';
            placeholder.style.display = 'none';
        };
        // Baca file yang diupload
        if(event.target.files[0]){
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endsection