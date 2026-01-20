@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <a href="javascript:history.back()" class="btn btn-light rounded-pill mb-4 shadow-sm">
        <i class="bi bi-arrow-left me-2"></i>Kembali
    </a>

    <div class="row g-5">
        <div class="col-lg-5">
            <div class="sticky-top" style="top: 100px;">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                    <img src="{{ $meal['image'] }}" class="img-fluid" alt="{{ $meal['title'] }}">
                </div>
                
                <h1 class="fw-bold mb-3">{{ $meal['title'] }}</h1>
                
                <div class="d-flex gap-2 mb-4">
                    <span class="badge bg-warning text-dark px-3 py-2">
                        <i class="bi bi-clock me-1"></i> {{ $meal['readyInMinutes'] }} Menit
                    </span>
                    <span class="badge bg-info text-dark px-3 py-2">
                        <i class="bi bi-people me-1"></i> {{ $meal['servings'] }} Porsi
                    </span>
                </div>

                @if(isset($meal['sourceUrl']))
                <div class="d-grid">
                    <a href="{{ $meal['sourceUrl'] }}" target="_blank" class="btn btn-dark rounded-pill py-3">
                        <i class="bi bi-link-45deg me-2"></i> Lihat Sumber Asli
                    </a>
                </div>
                @endif
            </div>
        </div>

        <div class="col-lg-7">
            
            <div class="card border-0 bg-white shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold text-primary mb-4"><i class="bi bi-basket me-2"></i>Bahan-bahan Lengkap</h5>
                <ul class="list-group list-group-flush">
                    @foreach($meal['extendedIngredients'] as $ing)
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-medium">{{ $ing['nameClean'] ?? $ing['name'] }}</span>
                            <div class="small text-muted" style="font-size: 0.85rem;">{{ $ing['original'] }}</div>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $ing['amount'] }} {{ $ing['unit'] }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="card border-0 bg-white shadow-sm rounded-4 p-4">
                <h5 class="fw-bold text-primary mb-4"><i class="bi bi-fire me-2"></i>Instruksi Memasak</h5>
                
                <div class="instructions-content text-muted" style="line-height: 1.8;">
                    @if(!empty($meal['instructions']))
                        {!! $meal['instructions'] !!} @else
                        <p>Maaf, instruksi detail tidak tersedia untuk resep ini. Silakan kunjungi sumber aslinya.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection