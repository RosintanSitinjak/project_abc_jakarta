<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAttachments;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    use ManagesAttachments;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Client::query()
            ->with(['logo']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'ILIKE', "%{$search}%");
        }

        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($request->sort_by, $sortOrder);
        } else {
            $query->orderBy('position', 'asc')->latest();
        }

        $clients = $request->paginated ? $query->paginate($request->itemsPerPage) : $query->get();

        return response()->json($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $data = $request->validated();
        $logoId = $data['logo_id'] ?? null;
        unset($data['logo_id']);

        $client = Client::create($data);
        $this->setSingleAttachment($client, 'logo', $logoId, 'logo');

        return response()->json($client->load(['logo']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): JsonResponse
    {
        return response()->json($client->load(['logo']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $data = $request->validated();
        $logoId = $data['logo_id'] ?? null;
        unset($data['logo_id']);

        $client->update($data);
        $this->setSingleAttachment($client, 'logo', $logoId, 'logo');

        return response()->json($client->load(['logo']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json(['status' => 'deleted']);
    }
}
