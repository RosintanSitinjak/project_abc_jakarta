<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAttachments;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    use ManagesAttachments;


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Article::with(['thumbnail', 'author'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'ilike', '%' . $search . '%')
                    ->orWhere('description', 'ilike', '%' . $search . '%');
            });

        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->sort_by;

            if ($sortBy === 'author.name') {
                $query->select('articles.*')
                      ->leftJoin('users', 'articles.author_id', '=', 'users.id')
                      ->orderBy('users.name', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->latest();
        }

        $articles = $query->paginate($request->per_page ?? 10);

        return response()->json($articles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail_id' => ['nullable', 'uuid', 'exists:attachments,id'],
        ]);

        $validated['author_id'] = Auth::id();

        $thumbnailId = $validated['thumbnail_id'] ?? null;
        unset($validated['thumbnail_id']);

        $validated['slug'] = Str::slug($validated['title']);
        
        // Ensure slug is unique, even among soft-deleted records
        $count = Article::withTrashed()->where('slug', 'LIKE', "{$validated['slug']}%")->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . ($count + 1);
        }

        DB::beginTransaction();
        try {
            $article = Article::create($validated);
            $this->setSingleAttachment($article, 'thumbnail', $thumbnailId, 'thumbnail');

            DB::commit();

            return response()->json($article->load(['thumbnail', 'author']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create article', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        return response()->json($article->load(['thumbnail', 'author']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail_id' => ['nullable', 'uuid', 'exists:attachments,id'],
        ]);

        $thumbnailId = $validated['thumbnail_id'] ?? null;
        unset($validated['thumbnail_id']);

        if ($request->title !== $article->title) {
            $validated['slug'] = Str::slug($validated['title']);
            $count = Article::withTrashed()->where('slug', 'LIKE', "{$validated['slug']}%")->where('id', '!=', $article->id)->count();
            if ($count > 0) {
                $validated['slug'] .= '-' . ($count + 1);
            }
        }

        DB::beginTransaction();
        try {
            $article->update($validated);
            $article->update(['author_id' => Auth::id()]);
            $this->setSingleAttachment($article, 'thumbnail', $thumbnailId, 'thumbnail');

            DB::commit();

            return response()->json($article->fresh(['thumbnail', 'author']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update article', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $article->delete();
        return response()->json(null, 204);
    }
}
