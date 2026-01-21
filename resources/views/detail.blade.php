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
                    <img src="{{ $meal['image'] }}" class="img-fluid w-100" alt="{{ $meal['title'] }}" style="object-fit: cover;">
                </div>
                
                <h1 class="fw-bold mb-2">{{ $meal['title'] }}</h1>

                <div class="mb-4">
                    @php
                        $isFavDetail = Auth::check() && \App\Models\Favorite::where('user_id', Auth::id())->where('spoonacular_id', $meal['id'])->exists();
                    @endphp
                
                    <button 
                        onclick="toggleFavorite(this)" 
                        data-id="{{ $meal['id'] }}"
                        data-title="{{ $meal['title'] }}"
                        data-image="{{ $meal['image'] }}"
                        class="btn {{ $isFavDetail ? 'btn-outline-danger' : 'btn-danger shadow-sm' }} w-100 rounded-pill py-2 fw-bold btn-detail-fav"
                    >
                        <i class="bi {{ $isFavDetail ? 'bi-heart-fill' : 'bi-heart' }} me-2"></i> 
                        <span>{{ $isFavDetail ? 'Disimpan di Favorit' : 'Simpan ke Favorit' }}</span>
                    </button>
                </div>
                
                <div class="mb-3">
                    @if($meal['vegetarian']) 
                        <span class="badge rounded-pill bg-success mb-1"><i class="bi bi-flower1"></i> Vegetarian</span> 
                    @endif
                    @if($meal['vegan']) 
                        <span class="badge rounded-pill bg-success mb-1"><i class="bi bi-leaf"></i> Vegan</span> 
                    @endif
                    @if($meal['glutenFree']) 
                        <span class="badge rounded-pill bg-warning text-dark mb-1">Gluten Free</span> 
                    @endif
                    @if($meal['dairyFree']) 
                        <span class="badge rounded-pill bg-info text-dark mb-1">Dairy Free</span> 
                    @endif
                    @if($meal['veryHealthy']) 
                        <span class="badge rounded-pill bg-primary mb-1">Very Healthy</span> 
                    @endif
                    @if($meal['cheap']) 
                        <span class="badge rounded-pill bg-secondary mb-1">Ekonomis</span> 
                    @endif
                </div>

                <div class="d-flex gap-2 mb-4">
                    <span class="badge bg-warning text-dark px-3 py-2">
                        <i class="bi bi-clock me-1"></i> {{ $meal['readyInMinutes'] }} Menit
                    </span>
                    <span class="badge bg-info text-dark px-3 py-2">
                        <i class="bi bi-people me-1"></i> {{ $meal['servings'] }} Porsi
                    </span>
                </div>

                @if(isset($meal['nutrition']['nutrients']))
                <div class="card border-0 bg-light rounded-4 p-3 mb-4 shadow-sm">
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-activity me-2"></i>Informasi Gizi (Per Porsi)</h6>
                    
                    @php
                        // Helper sederhana untuk mengambil data nutrisi
                        $nutrients = collect($meal['nutrition']['nutrients']);
                        $calories = $nutrients->where('name', 'Calories')->first();
                        $protein = $nutrients->where('name', 'Protein')->first();
                        $fat = $nutrients->where('name', 'Fat')->first();
                        $carbs = $nutrients->where('name', 'Carbohydrates')->first();
                    @endphp

                    <div class="row text-center g-2">
                        <div class="col-3">
                            <div class="bg-white rounded p-2 h-100 border">
                                <div class="h4 fw-bold text-danger mb-0">{{ round($calories['amount'] ?? 0) }}</div>
                                <div class="small text-muted" style="font-size: 10px;">{{ $calories['unit'] ?? 'kcal' }}</div>
                                <div class="small fw-bold">Kalori</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white rounded p-2 h-100 border">
                                <div class="h5 fw-bold text-primary mb-0">{{ round($protein['amount'] ?? 0) }}</div>
                                <div class="small text-muted" style="font-size: 10px;">{{ $protein['unit'] ?? 'g' }}</div>
                                <div class="small fw-bold">Protein</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white rounded p-2 h-100 border">
                                <div class="h5 fw-bold text-warning mb-0">{{ round($fat['amount'] ?? 0) }}</div>
                                <div class="small text-muted" style="font-size: 10px;">{{ $fat['unit'] ?? 'g' }}</div>
                                <div class="small fw-bold">Lemak</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white rounded p-2 h-100 border">
                                <div class="h5 fw-bold text-success mb-0">{{ round($carbs['amount'] ?? 0) }}</div>
                                <div class="small text-muted" style="font-size: 10px;">{{ $carbs['unit'] ?? 'g' }}</div>
                                <div class="small fw-bold">Karbo</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(isset($meal['sourceUrl']))
                <div class="d-grid">
                    <a href="{{ $meal['sourceUrl'] }}" target="_blank" class="btn btn-dark rounded-pill py-3 shadow hover-scale">
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
                        <div class="d-flex align-items-center">
                            <img src="https://spoonacular.com/cdn/ingredients_100x100/{{ $ing['image'] }}" 
                                 alt="{{ $ing['name'] }}" 
                                 class="rounded-circle me-3 border" 
                                 style="width: 40px; height: 40px; object-fit: cover;"
                                 onerror="this.style.display='none'">
                            <div>
                                <span class="fw-medium text-capitalize">{{ $ing['nameClean'] ?? $ing['name'] }}</span>
                                <div class="small text-muted" style="font-size: 0.85rem;">{{ $ing['original'] }}</div>
                            </div>
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
                        {!! $meal['instructions'] !!} 
                    @else
                        <div class="alert alert-light text-center">
                            <i class="bi bi-info-circle mb-2 d-block fs-3"></i>
                            Maaf, instruksi detail tidak tersedia langsung di sini.<br>
                            Silakan klik tombol <b>"Lihat Sumber Asli"</b> di sebelah kiri.
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection