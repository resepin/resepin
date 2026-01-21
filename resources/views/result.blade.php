@extends('layouts.app')

@section('content')
<div class="container">
    
    <div class="text-center mb-5">
        <h6 class="text-uppercase text-muted fw-bold">Bahan Terdeteksi AI:</h6>
        <div class="d-flex justify-content-center flex-wrap gap-2 mt-2">
            @foreach($ingredients as $item)
                <span class="badge bg-success bg-opacity-10 text-success border border-success px-4 py-2 rounded-pill fs-6">
                    <i class="bi bi-check2-circle me-1"></i> {{ ucfirst($item) }}
                </span>
            @endforeach
        </div>
    </div>

    <div class="row g-4">
        @forelse($recipes as $meal)
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 hover-up">
                <div class="position-relative">
                    <img src="{{ $meal['image'] }}" class="card-img-top" alt="{{ $meal['title'] }}" style="height: 200px; object-fit: cover;">
                    <div class="position-absolute top-0 end-0 p-2">
                        @php
                            $isFav = Auth::check() && in_array($meal['id'], $favoriteIds);
                        @endphp
                    
                        <button 
                            class="btn btn-light rounded-circle shadow-sm p-2 d-flex align-items-center justify-content-center btn-fav" 
                            style="width: 40px; height: 40px;"
                            title="Simpan ke Favorit"
                            onclick="toggleFavorite(this)" 
                            data-id="{{ $meal['id'] }}"
                            data-title="{{ $meal['title'] }}"
                            data-image="{{ $meal['image'] }}">
                            
                            <i class="bi {{ $isFav ? 'bi-heart-fill text-danger' : 'bi-heart text-secondary' }} fs-5"></i>
                        </button>
                    </div>
                </div>
                
                <div class="card-body d-flex flex-column p-4">
                    <h5 class="card-title fw-bold fs-6 mb-3">{{ $meal['title'] }}</h5>
                    
                    <div class="mb-4 small">
                        <div class="text-success mb-1">
                            <i class="bi bi-check-circle-fill"></i> Pakai {{ $meal['usedIngredientCount'] }} bahan kamu
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-cart-x-fill"></i> Kurang {{ $meal['missedIngredientCount'] }} bahan lain
                        </div>
                    </div>

                    <div class="mt-auto">
                        <a href="{{ route('resep.show', $meal['id']) }}" class="btn btn-outline-dark w-100 rounded-pill">
                            Lihat Cara Masak
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="text-muted">
                <i class="bi bi-emoji-dizzy display-1"></i>
                <p class="mt-3 lead">Yah, tidak ada resep yang cocok.</p>
                <small>Mungkin filter kamu terlalu ketat? Coba hilangkan filter 'Pedas' atau 'Vegetarian'.</small>
            </div>
            <a href="{{ route('home') }}" class="btn btn-primary mt-3">Coba Lagi</a>
        </div>
        @endforelse
    </div>
    
    <div class="text-center mt-5">
        <a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Foto ulang</a>
    </div>  
</div>
@endsection