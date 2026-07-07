<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Concerns\ManagesAttachments;

class ArticleController extends Controller
{
    use ManagesAttachments;

    /**
     * Tampil Daftar Berita
     */
public function index(Request $request): JsonResponse
{
    // Kita suruh Laravel mengambil data artikel SEKALIGUS gambarnya
    $query = Article::query()->with(['thumbnail', 'author']);
    
    if ($request->filled('search')) {
        $query->where('title', 'ILIKE', "%{$request->search}%");
    }
    
    return response()->json($query->latest()->get());
}
    /**
     * Simpan Berita Baru
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
        ]);

        $article = Article::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'user_id' => auth()->id(), 
            'thumbnail_id' => $request->thumbnail_id,
        ]);

        return response()->json($article->load('thumbnail'), 201);
    }

    /**
     * Ambil Detail Satu Berita (PENTING untuk fitur Edit)
     */
    public function show(Article $article): JsonResponse
    {
        // Mengambil satu data berita beserta gambarnya untuk ditampilkan di form edit
        return response()->json($article->load(['thumbnail', 'author']));
    }

    /**
     * Update Berita yang Sudah Ada
     */
    public function update(Request $request, Article $article): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
        ]);

        $article->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'thumbnail_id' => $request->thumbnail_id, // Update gambar jika ada perubahan
        ]);

        return response()->json($article->load('thumbnail'));
    }

    /**
     * Hapus Berita
     */
    public function destroy(Article $article): JsonResponse
    {
        $article->delete();
        return response()->json(['status' => 'deleted']);
    }
}