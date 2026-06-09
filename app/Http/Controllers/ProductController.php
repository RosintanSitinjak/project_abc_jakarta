<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAttachments;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ManagesAttachments;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['category', 'thumbnail']);

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
                $query->select('products.*')
                      ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                      ->orderBy('categories.name', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('position', 'asc')->latest();
        }

        $products = $request->paginated ? $query->paginate($request->itemsPerPage) : $query->get();

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $thumbnailId = $data['thumbnail_id'] ?? null;
        unset($data['thumbnail_id']);

        $product = Product::create($data);
        $this->setSingleAttachment($product, 'thumbnail', $thumbnailId, 'thumbnail');

        \App\Support\PublicCatalogCache::refreshProducts();

        return response()->json($product->load(['category', 'thumbnail']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json($product->load(['category', 'thumbnail']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();
        $thumbnailId = $data['thumbnail_id'] ?? null;
        unset($data['thumbnail_id']);

        $product->update($data);
        $this->setSingleAttachment($product, 'thumbnail', $thumbnailId, 'thumbnail');

        \App\Support\PublicCatalogCache::refreshProducts();

        return response()->json($product->load(['category', 'thumbnail']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['status' => 'deleted']);
    }
}
