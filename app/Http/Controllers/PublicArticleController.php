<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicArticleController extends Controller
{
    // Di dalam Controller khusus untuk tampilan luar/user
public function index()
{
    // HANYA ambil yang statusnya published
    $articles = Article::where('status', 'published')
                        ->with('thumbnail')
                        ->latest()
                        ->get();

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