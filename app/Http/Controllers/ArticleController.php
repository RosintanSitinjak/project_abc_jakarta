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

    public function index(Request $request): JsonResponse
    {
        $query = Article::query()->with(['thumbnail', 'author']);
        
        if ($request->filled('search')) {
            $query->where('title', 'ILIKE', "%{$request->search}%");
        }
        
        $articles = $query->latest()->get();

        // Transformasi URL Gambar agar muncul di tabel utama
        $articles->transform(function ($article) {
            $article->image_url = $article->thumbnail ? asset('storage/' . $article->thumbnail->path) : null;
            return $article;
        });

        return response()->json($articles);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'status' => 'required|in:draft,published',
            'excerpt' => 'nullable|string|max:200',
        ]);

        $article = Article::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . rand(100,999),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'status' => $request->status,
            'user_id' => auth()->id(), 
            'thumbnail_id' => $request->thumbnail_id,
        ]);

        return response()->json($article->load('thumbnail'), 201);
    }

    public function show(Article $article): JsonResponse
    {
        $article->load(['thumbnail', 'author']);
        // Menambahkan URL gambar lengkap untuk preview form edit
        $article->image_url = $article->thumbnail ? asset('storage/' . $article->thumbnail->path) : null;
        
        return response()->json($article);
    }

    public function update(Request $request, Article $article): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'status' => 'required|in:draft,published',
            'excerpt' => 'nullable|string|max:200',
        ]);

        $article->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . rand(100,999),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'status' => $request->status,
            'thumbnail_id' => $request->thumbnail_id,
        ]);

        return response()->json($article->load('thumbnail'));
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();
        return response()->json(['status' => 'deleted']);
    }
}