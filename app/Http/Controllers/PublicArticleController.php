<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicArticleController extends Controller
{
    public function index(): JsonResponse
    {
        // PENTING: Harus ada .with('thumbnail') agar link gambar ikut terkirim ke Karla
        $articles = Article::with(['thumbnail', 'author'])->latest()->get();
        
        return response()->json($articles);
    }

    public function show($slug): JsonResponse
    {
        $article = Article::with(['thumbnail', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();
            
        return response()->json($article);
    }
}