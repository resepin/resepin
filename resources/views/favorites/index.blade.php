@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">
            Favorit Saya
        </h2>
        <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill">
            <i class="bi bi-plus-lg me-1"></i> Cari Resep Baru
        </a>
    </div>

    @if($favorites->count() > 0)
        <div class="row g-4">
            @foreach($favorites as $fav)
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-up">
                    <div class="position-relative">
                        <img src="{{ $fav->image }}" class="card-img-top" alt="{{ $fav->title }}" style="height: 200px; object-fit: cover;">
                        
                        <div class="position-absolute top-0 end-0 p-2">
                            <button 
                                onclick="toggleFavorite(this)"
                                data-id="{{ $fav->spoonacular_id }}"
                                data-title="{{ $fav->title }}"
                                data-image="{{ $fav->image }}"
                                class="btn btn-light text-danger rounded-circle shadow-sm p-2 btn-fav btn-remove-card" 
                                title="Hapus dari Favorit">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body d-flex flex-column p-4">
                        <h5 class="card-title fw-bold fs-6 mb-3 text-truncate">{{ $fav->title }}</h5>
                        
                        <div class="mt-auto">
                            <a href="{{ route('resep.show', $fav->spoonacular_id) }}" class="btn btn-primary w-100 rounded-pill text-white">
                                Lihat Resep
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-basket display-1 text-muted opacity-25"></i>
            </div>
            <h4 class="fw-bold text-muted">Belum ada resep favorit</h4>
            <p class="text-secondary">Simpan resep yang kamu suka agar mudah dicari nanti.</p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-3 px-4 rounded-pill">Mulai Scan Bahan</a>
        </div>
    @endif
</div>
@endsection