<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori dengan jumlah buku aktif
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()->withCount('books');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'ILIKE', "%{$search}%");
        }

        return response()->json($query->latest()->get());
    }

    /**
     * Menambah kategori baru
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string'
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori ini sudah terdaftar.',
        ]);

        $category = Category::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name) . '-' . rand(100, 999),
            'description' => $request->description,
        ]);

        return response()->json($category, 201);
    }

    /**
     * Mengubah data kategori
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string'
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori ini sudah terdaftar.',
        ]);

        // Perbarui slug hanya jika nama kategori berubah
        $slug = ($category->name !== $request->name) 
            ? Str::slug($request->name) . '-' . rand(100, 999) 
            : $category->slug;

        $category->update([
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
        ]);

        return response()->json($category);
    }

    /**
     * Menghapus kategori dengan pengaman integritas data buku
     */
    public function destroy(Category $category): JsonResponse
    {
        // Proteksi: cegah penghapusan jika kategori masih digunakan oleh buku
        if ($category->books()->count() > 0) {
            return response()->json([
                'message' => "Gagal menghapus! Kategori '{$category->name}' masih memiliki koleksi buku aktif. Silakan pindahkan atau hapus buku terkait terlebih dahulu."
            ], 422);
        }

        $category->delete();
        return response()->json(['status' => 'deleted', 'message' => 'Kategori berhasil dihapus.']);
    }
}