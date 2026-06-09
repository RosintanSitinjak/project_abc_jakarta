<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class PublicArticleController extends Controller
{
    /**
     * Display a listing of articles for the public landing page.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('limit', 10);
        
        $articles = Article::with('thumbnail')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'ilike', '%' . $search . '%');
            })
            ->latest()
            ->paginate($perPage);

        return response()->json($articles);
    }

    /**
     * Return popular articles ordered by views.
     */
    public function popular(Request $request)
    {
        $exclude = $request->get('exclude');

        $articles = Article::with('thumbnail')
            ->when($exclude, function ($query, $excludeSlug) {
                $query->where('slug', '!=', $excludeSlug);
            })
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        return response()->json($articles);
    }

    /**
     * Display a single article by slug and increment view count.
     */
    public function show($slug)
    {
        $article = Article::with('thumbnail')->where('slug', $slug)->firstOrFail();
        $article->increment('views');
        return response()->json($article);
    }
}
