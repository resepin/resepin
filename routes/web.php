<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResepController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;

// Halaman Utama (Bisa diakses siapa saja)
Route::get('/', function () { return view('home'); })->name('home');

// --- AUTH MANUAL ROUTES ---
// Middleware 'guest' artinya hanya bisa diakses kalau BELUM login
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Middleware 'auth' artinya hanya bisa diakses kalau SUDAH login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Fitur Favorit
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
});

// Fitur Resep (Controller kamu sebelumnya)
Route::post('/analyze', [ResepController::class, 'analyze'])->name('analyze');
Route::get('/resep/{id}', [ResepController::class, 'show'])->name('resep.show');
Route::get('/analyze', function() {
    return redirect()->route('home')->with('info', 'Halaman hasil telah kadaluarsa. Silakan scan ulang.');
});