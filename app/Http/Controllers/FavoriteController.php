<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // lihat daftar favorite user
    public function index()
    {
        $user = Auth::user();
        $favorites = Favorite::where('user_id', $user->id)->get();

        return view('favorites.index', compact('favorites'));
    }

    // logic toggle favorite
    public function store(Request $request)
    {
        if (!Auth::check()) {
            // Jika request via AJAX tapi belum login
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Silakan login dulu!'], 401);
            }
            return redirect()->route('login')->with('error', 'Silakan login untuk menyimpan resep.');
        }

        $user = Auth::user();
    
        $existing = Favorite::where('user_id', $user->id)
            ->where('spoonacular_id', $request->spoonacular_id)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed'; // Tandai kalau dihapus
            $msg = 'Dihapus dari favorit.';
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'spoonacular_id' => $request->spoonacular_id,
                'title' => $request->title,
                'image' => $request->image
            ]);
            $action = 'added'; // Tandai kalau ditambah
            $msg = 'Disimpan ke favorit!';
        }

        // RESPON KHUSUS AJAX (Agar tidak reload)
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success', 
                'action' => $action,
                'message' => $msg
            ]);
        }

        // Respon Biasa (Untuk halaman Detail yang pakai Form)
        return back()->with('success', $msg);
        }
}
