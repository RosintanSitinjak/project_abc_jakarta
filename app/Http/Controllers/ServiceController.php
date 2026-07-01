<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAttachments;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    use ManagesAttachments;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Service::query()
            ->with(['scopes', 'thumbnail']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($request->sort_by, $sortOrder);
        } else {
            $query->latest();
        }

        $services = $request->paginated ? $query->paginate($request->itemsPerPage) : $query->get();

        return response()->json($services);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $thumbnailId = $data['thumbnail_id'] ?? null;
        unset($data['thumbnail_id']);

        $service = Service::create($data);
        $this->setSingleAttachment($service, 'thumbnail', $thumbnailId, 'thumbnail');

        \App\Support\PublicCatalogCache::refreshServices();

        return response()->json($service->load(['scopes', 'thumbnail']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service): JsonResponse
    {
        return response()->json($service->load(['scopes', 'thumbnail']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $data = $request->validated();
        $thumbnailId = $data['thumbnail_id'] ?? null;
        unset($data['thumbnail_id']);

        $service->update($data);
        $this->setSingleAttachment($service, 'thumbnail', $thumbnailId, 'thumbnail');

        \App\Support\PublicCatalogCache::refreshServices();

        return response()->json($service->load(['scopes', 'thumbnail']));
    }

    /**
     * Remove the specified resource from storage.
     */
//     public function destroy(Service $service): JsonResponse
//     {
//         $service->delete();

//         return response()->json(['status' => 'deleted']);
//     }
// }

public function destroy(Service $service)
{
    try {
        $service->delete();
        
        // PENTING: Refresh cache agar data di landing page juga terhapus
        \App\Support\PublicCatalogCache::refreshServices();

        return response()->json(['message' => 'Service deleted successfully']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
    }
}
}