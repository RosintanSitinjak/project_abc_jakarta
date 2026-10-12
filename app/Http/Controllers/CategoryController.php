<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Tampil Daftar Kategori dengan Fitur Pencarian & Hitung Jumlah Buku
     */
    public function index(Request $request): JsonResponse
    {
        // withCount('books') akan menambahkan kolom virtual 'books_count' secara otomatis
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
            'name' => 'required|string|max:255',
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . rand(100, 999),
            'description' => $request->description,
        ]);

        return response()->json($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        // Karena ada relasi, di skripsi bisa dijelaskan: 
        // Menggunakan SoftDeletes agar data histori buku tetap aman.
        $category->delete();
        return response()->json(['status' => 'deleted']);
    }
}