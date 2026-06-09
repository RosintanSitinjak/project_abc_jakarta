<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAttachments;
use App\Http\Requests\StorePortfolioRequest;
use App\Http\Requests\UpdatePortfolioRequest;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;

class PortfolioController extends Controller
{
    use ManagesAttachments;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Portfolio::query()
            ->with(['category', 'thumbnail', 'photos']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->sort_by;

            if ($sortBy === 'category.name') {
                $query->select('portfolios.*')
                      ->leftJoin('categories', 'portfolios.category_id', '=', 'categories.id')
                      ->orderBy('categories.name', $sortOrder);
            } elseif ($sortBy === 'clientName') {
                $query->orderBy('client_name', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->latest();
        }

        $portfolios = $request->paginated ? $query->paginate($request->itemsPerPage) : $query->get();

        return response()->json($portfolios);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePortfolioRequest $request): JsonResponse
    {
        $data = $request->validated();
        $thumbnailId = $data['thumbnail_id'] ?? null;
        unset($data['thumbnail_id']);

        $photoIds = $this->resolveAttachmentIds($request, 'photo_ids');
        unset($data['photo_ids']);

        $portfolio = Portfolio::create($data);
        $this->setSingleAttachment($portfolio, 'thumbnail', $thumbnailId, 'thumbnail');
        $this->syncMultipleAttachments($portfolio, 'photos', $photoIds, 'photo');

        \App\Support\PublicCatalogCache::refreshPortfolios();

        return response()->json($portfolio->load(['category', 'thumbnail', 'photos']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Portfolio $portfolio): JsonResponse
    {
        return response()->json($portfolio->load(['category', 'thumbnail', 'photos']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePortfolioRequest $request, Portfolio $portfolio): JsonResponse
    {
        $data = $request->validated();
        $thumbnailId = $data['thumbnail_id'] ?? null;
        unset($data['thumbnail_id']);

        $photoIds = $this->resolveAttachmentIds($request, 'photo_ids');
        unset($data['photo_ids']);

        $portfolio->update($data);
        $this->setSingleAttachment($portfolio, 'thumbnail', $thumbnailId, 'thumbnail');
        $this->syncMultipleAttachments($portfolio, 'photos', $photoIds, 'photo');

        \App\Support\PublicCatalogCache::refreshPortfolios();

        return response()->json($portfolio->load(['category', 'thumbnail', 'photos']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Portfolio $portfolio): JsonResponse
    {
        $portfolio->delete();

        return response()->json(['status' => 'deleted']);
    }
}
