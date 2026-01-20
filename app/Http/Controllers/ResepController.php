<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
        // 1. Validasi Gambar
        $request->validate([
            'image' => 'required|image|max:5000',
        ]);

        // ---------------------------------------------------------
        // 2. TANYA AI (AZURE)
        // ---------------------------------------------------------
        try {
            // Perhatikan: kita pakai 'file' sesuai temuan endpoint tadi
            $responseAi = Http::timeout(120)->attach(
                'file', file_get_contents($request->file('image')), 'foto.jpg'
            )->post($this->pythonApiUrl);

            if ($responseAi->failed()) {
                return back()->with('error', 'Gagal menghubungi AI Azure. Coba lagi.');
            }

            $detected_ingredients = $responseAi->json()['ingredients'] ?? [];

            if (empty($detected_ingredients)) {
                return back()->with('error', 'AI tidak menemukan bahan makanan di foto ini.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Error Koneksi AI: ' . $e->getMessage());
        }

        // Bersihkan nama bahan
        $clean_ingredients = array_map(function($item) {
            return str_replace('_', ' ', $item);
        }, $detected_ingredients);


        // ---------------------------------------------------------
        // 3. LOGIKA FILTER CANGGIH
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

        // C. Filter Manual (User mengetik bahan sendiri)
        if ($request->filled('custom_exclude')) {
            // Mengubah "kacang, udang, nanas" menjadi array ['kacang', 'udang', 'nanas']
            $manualInputs = explode(',', $request->input('custom_exclude'));
            
            // Bersihkan spasi berlebih (misal " udang " jadi "udang")
            $manualInputs = array_map('trim', $manualInputs);
            
            // Gabungkan ke daftar blacklist
            $excludedIngredients = array_merge($excludedIngredients, $manualInputs);
        }

        // D. Filter Diet (Bisa Pilih Banyak)
        $dietString = '';
        if ($request->has('diets')) {
            // Mengubah array ['vegetarian', 'gluten free'] menjadi string "vegetarian,gluten free"
            $dietString = implode(',', $request->input('diets'));
        }

        // ---------------------------------------------------------
        // 4. CARI RESEP (Spoonacular)
        // ---------------------------------------------------------
        $response = Http::get("https://api.spoonacular.com/recipes/complexSearch", [
            'apiKey' => $this->apiKey,
            'includeIngredients' => implode(',', $clean_ingredients), // Bahan dari AI
            'excludeIngredients' => implode(',', $excludedIngredients), // Bahan Terlarang (Gabungan)
            'diet' => $dietString, // Diet (Gabungan)
            'number' => 8,
            'addRecipeInformation' => true,
            'fillIngredients' => true,
            'ignorePantry' => true,
            'sort' => 'min-missing-ingredients'
        ]);

        $recipes = $response->json()['results'] ?? [];

        return view('result', [
            'ingredients' => $clean_ingredients,
            'recipes' => $recipes
        ]);
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