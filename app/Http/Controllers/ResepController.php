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
        set_time_limit(120); 

        $request->validate([
            'image' => 'required|image|max:5000',
        ]);

        // ---------------------------------------------------------
        // TANYA AI (AZURE) - Ini yang lambat
        // ---------------------------------------------------------
        try {
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
            return back()->with('error', 'Coba upload foto yang lebih kecil atau coba lagi nanti.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }

        $clean_ingredients = array_map(fn($item) => str_replace('_', ' ', $item), $detected_ingredients);

        // Simpan filter ke session untuk digunakan di AJAX
        $filters = [
            'is_halal' => $request->has('is_halal'),
            'no_spicy' => $request->has('no_spicy'),
            'custom_exclude' => $request->input('custom_exclude', ''),
            'diets' => $request->input('diets', []),
        ];

        // LANGSUNG redirect ke result page dengan ingredients saja
        // Recipes akan di-load via AJAX (progressive loading)
        return view('result', [
            'ingredients' => $clean_ingredients,
            'filters' => $filters,
            'recipes' => [],  // Kosong dulu, akan diisi AJAX
            'favoriteIds' => [],
            'loadViaAjax' => true,  // Flag untuk result.blade.php
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

    /**
     * API: Analyze image dengan Azure AI (dipanggil otomatis saat upload)
     */
    public function analyzeImage(Request $request)
    {
        set_time_limit(120);

        $request->validate([
            'image' => 'required|image|max:5000',
        ]);

        try {
            $responseAi = Http::timeout(90)
                ->connectTimeout(10)
                ->attach('file', file_get_contents($request->file('image')), 'foto.jpg')
                ->post($this->pythonApiUrl);

            if ($responseAi->failed()) {
                return response()->json(['success' => false, 'error' => 'Gagal menghubungi AI Azure'], 500);
            }

            $detected_ingredients = $responseAi->json()['ingredients'] ?? [];

            if (empty($detected_ingredients)) {
                return response()->json(['success' => false, 'error' => 'Tidak ada bahan makanan terdeteksi'], 422);
            }

            $clean_ingredients = array_map(fn($item) => str_replace('_', ' ', $item), $detected_ingredients);

            return response()->json(['success' => true, 'ingredients' => $clean_ingredients]);

        } catch (ConnectionException $e) {
            return response()->json(['success' => false, 'error' => 'Timeout, coba foto lebih kecil'], 408);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Search recipes dari Spoonacular (dipanggil setelah Azure selesai)
     */
    public function searchRecipes(Request $request)
    {
        $request->validate([
            'ingredients' => 'required|array|min:1',
        ]);

        $ingredients = $request->input('ingredients');
        $excludedIngredients = [];

        if ($request->boolean('is_halal')) {
            $excludedIngredients = array_merge($excludedIngredients, ['pork', 'bacon', 'ham', 'lard', 'alcohol', 'wine', 'beer', 'rum', 'whiskey']);
        }

        if ($request->boolean('no_spicy')) {
            $excludedIngredients = array_merge($excludedIngredients, ['chili', 'cayenne', 'jalapeno', 'wasabi', 'pepper', 'sriracha', 'hot sauce']);
        }

        if ($request->filled('custom_exclude')) {
            $manual = array_map('trim', explode(',', $request->input('custom_exclude')));
            $excludedIngredients = array_merge($excludedIngredients, $manual);
        }

        $dietString = $request->has('diets') ? implode(',', $request->input('diets')) : '';

        try {
            $response = Http::get("https://api.spoonacular.com/recipes/complexSearch", [
                'apiKey' => $this->apiKey,
                'includeIngredients' => implode(',', $ingredients),
                'excludeIngredients' => implode(',', $excludedIngredients),
                'diet' => $dietString,
                'number' => 12,
                'addRecipeInformation' => true,
                'fillIngredients' => true,
                'ignorePantry' => true,
                'sort' => 'max-used-ingredients',
                'instructionsRequired' => true,
                'includeNutrition' => true
            ]);

            $recipes = $response->json()['results'] ?? [];

            $favoriteIds = [];
            if (Auth::check()) {
                $favoriteIds = Favorite::where('user_id', Auth::id())->pluck('spoonacular_id')->toArray();
            }

            return response()->json([
                'success' => true,
                'ingredients' => $ingredients,
                'recipes' => $recipes,
                'favoriteIds' => $favoriteIds
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Tampilkan hasil dari cache (data yang sudah di-fetch sebelumnya)
     * INI YANG BIKIN <100ms! Data sudah ready, tinggal render view.
     */
    public function showCached(Request $request)
    {
        $data = json_decode($request->input('cached_data'), true);

        if (!$data || !isset($data['success']) || !$data['success']) {
            return redirect()->route('home')->with('error', 'Data tidak valid, silakan coba lagi.');
        }

        return view('result', [
            'ingredients' => $data['ingredients'] ?? [],
            'recipes' => $data['recipes'] ?? [],
            'favoriteIds' => $data['favoriteIds'] ?? [],
            'loadViaAjax' => false,  // Data sudah lengkap, tidak perlu AJAX lagi
        ]);
    }
}