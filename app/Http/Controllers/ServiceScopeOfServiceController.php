<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceScopeOfServiceRequest;
use App\Http\Requests\UpdateServiceScopeOfServiceRequest;
use App\Models\ServiceScopeOfService;
use Illuminate\Http\JsonResponse;

class ServiceScopeOfServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = ServiceScopeOfService::query()->with('service')->latest();
        $scopes = $request->paginated ? $query->paginate($request->itemsPerPage) : $query->get();

        return response()->json($scopes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceScopeOfServiceRequest $request): JsonResponse
    {
        $scope = ServiceScopeOfService::create($request->validated());

        return response()->json($scope->load('service'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceScopeOfService $serviceScopeOfService): JsonResponse
    {
        return response()->json($serviceScopeOfService->load('service'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateServiceScopeOfServiceRequest $request,
        ServiceScopeOfService $serviceScopeOfService
    ): JsonResponse {
        $serviceScopeOfService->update($request->validated());

        return response()->json($serviceScopeOfService->load('service'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceScopeOfService $serviceScopeOfService): JsonResponse
    {
        $serviceScopeOfService->delete();

        return response()->json(['status' => 'deleted']);
    }
}
