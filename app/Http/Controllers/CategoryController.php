<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()->withCount('books');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'ILIKE', "%{$search}%");
        }

        return response()->json($query->latest()->get());
    }

public function store(Request $request): JsonResponse
    {
        $request->validate([
            // Tambahkan 'unique' agar tidak ada kategori kembar
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . rand(100, 999),
            'description' => $request->description,
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            // Unik, kecuali untuk ID kategori yang sedang diedit ini
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string'
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . rand(100, 999),
            'description' => $request->description,
        ]);

        return response()->json($category);
    }
    /**
     * FUNGSI DESTROY DENGAN PROTEKSI (SAFETY GUARD)
     */
    public function destroy(Category $category): JsonResponse
    {
        // CEK: Apakah masih ada buku di kategori ini?
        if ($category->books()->count() > 0) {
            return response()->json([
                'message' => "Gagal menghapus! Kategori '{$category->name}' masih memiliki koleksi buku. Silakan pindahkan atau hapus buku terlebih dahulu."
            ], 422); // Error 422 (Unprocessable Entity)
        }

        $category->delete();
        return response()->json(['status' => 'deleted']);
    }
}