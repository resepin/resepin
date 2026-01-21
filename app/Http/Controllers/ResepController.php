<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Client\ConnectionException;
use App\Models\Favorite;

class ResepController extends Controller
{
    private $apiKey;
    private $pythonApiUrl;

    public function __construct()
    {
        $this->apiKey = env('SPOONACULAR_API_KEY');
        $this->pythonApiUrl = env('PYTHON_API_URL');
    }

    public function index()
    {
        return view('home');
    }

    public function analyze(Request $request)
    {
        // 1. Perpanjang Nyawa PHP (Biar gak kena Fatal Error 60s)
        set_time_limit(120); 

        // 2. Validasi Gambar
        $request->validate([
            'image' => 'required|image|max:5000',
        ]);

        // ---------------------------------------------------------
        // 3. TANYA AI (AZURE)
        // ---------------------------------------------------------
        try {
            // TAMBAHAN 2: Timeout diset 90 detik (lebih kecil dari set_time_limit 120)
            // connectTimeout 10 detik (biar kalau server mati, langsung error gak nunggu lama)
            $responseAi = Http::timeout(90)
                ->connectTimeout(10) 
                ->attach(
                    'file', file_get_contents($request->file('image')), 'foto.jpg'
                )->post($this->pythonApiUrl);

            if ($responseAi->failed()) {
                return back()->with('error', 'Gagal menghubungi AI Azure (Server Error). Silakan coba lagi.');
            }

            $detected_ingredients = $responseAi->json()['ingredients'] ?? [];

            if (empty($detected_ingredients)) {
                return back()->with('error', 'AI tidak menemukan bahan makanan di foto ini. Coba foto lebih dekat/jelas.');
            }

        } catch (ConnectionException $e) {
            // TAMBAHAN 3: Tangkap Error Timeout di sini
            return back()->with('error', 'Waduh, AI-nya kelamaan mikir (Timeout > 90 detik). Coba upload foto yang lebih kecil atau coba lagi nanti.');
            
        } catch (\Exception $e) {
            // Error umum lainnya
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }

        // Bersihkan nama bahan
        $clean_ingredients = array_map(function($item) {
            return str_replace('_', ' ', $item);
        }, $detected_ingredients);


        // ---------------------------------------------------------
        // 4. LOGIKA FILTER CANGGIH
        // ---------------------------------------------------------
        $excludedIngredients = [];

        // A. Filter Halal
        if ($request->has('is_halal')) {
            $haram = ['pork', 'bacon', 'ham', 'lard', 'alcohol', 'wine', 'beer', 'rum', 'whiskey'];
            $excludedIngredients = array_merge($excludedIngredients, $haram);
        }

        // B. Filter Anti Pedas
        if ($request->has('no_spicy')) {
            $pedas = ['chili', 'cayenne', 'jalapeno', 'wasabi', 'pepper', 'sriracha', 'hot sauce'];
            $excludedIngredients = array_merge($excludedIngredients, $pedas);
        }

        // C. Filter Manual
        if ($request->filled('custom_exclude')) {
            $manualInputs = explode(',', $request->input('custom_exclude'));
            $manualInputs = array_map('trim', $manualInputs);
            $excludedIngredients = array_merge($excludedIngredients, $manualInputs);
        }

        // D. Filter Diet
        $dietString = '';
        if ($request->has('diets')) {
            $dietString = implode(',', $request->input('diets'));
        }

        // ---------------------------------------------------------
        // 5. CARI RESEP (Spoonacular)
        // ---------------------------------------------------------
        try {
            $response = Http::get("https://api.spoonacular.com/recipes/complexSearch", [
                'apiKey' => $this->apiKey,
                'includeIngredients' => implode(',', $clean_ingredients),
                'excludeIngredients' => implode(',', $excludedIngredients),
                'diet' => $dietString,
                'number' => 8,
                'addRecipeInformation' => true,
                'fillIngredients' => true,
                'ignorePantry' => true,
                'sort' => 'min-missing-ingredients',
                'includeNutrition' => true
            ]);

            $recipes = $response->json()['results'] ?? [];

            $favoriteIds = [];
            if (Auth::check()) {
                $favoriteIds = Favorite::where('user_id', Auth::id())
                    ->pluck('spoonacular_id')
                    ->toArray();
            }

            return view('result', [
                'ingredients' => $clean_ingredients,
                'recipes' => $recipes,
                'favoriteIds' => $favoriteIds
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengambil resep dari Spoonacular: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $response = Http::get("https://api.spoonacular.com/recipes/{$id}/information", [
            'apiKey' => $this->apiKey,
            'includeNutrition' => false,
        ]);

        if (!$response->successful()) {
            return redirect()->route('home')->with('error', 'Gagal mengambil detail resep.');
        }

        return view('detail', [
            'meal' => $response->json()
        ]);
    }
}