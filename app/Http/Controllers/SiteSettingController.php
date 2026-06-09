<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAttachments;
use App\Http\Requests\StoreSiteSettingRequest;
use App\Http\Requests\UpdateSiteSettingRequest;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends Controller
{
    use ManagesAttachments;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = SiteSetting::query()->with('logo');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('site_name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->sort_by;
            if ($sortBy === 'siteName') $sortBy = 'site_name';
            if ($sortBy === 'whatsappNumber') $sortBy = 'whatsapp_number';
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $settings = $request->paginated ? $query->paginate($request->itemsPerPage) : $query->get();

        return response()->json($settings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiteSettingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $logoId = $data['logo_id'] ?? null;
        unset($data['logo_id']);

        $setting = SiteSetting::create($data);
        $this->setSingleAttachment($setting, 'logo', $logoId, 'logo');

        return response()->json($setting->load('logo'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SiteSetting $siteSetting): JsonResponse
    {
        return response()->json($siteSetting->load('logo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiteSettingRequest $request, SiteSetting $siteSetting): JsonResponse
    {
        $data = $request->validated();
        $logoId = $data['logo_id'] ?? null;
        unset($data['logo_id']);

        $siteSetting->update($data);
        $this->setSingleAttachment($siteSetting, 'logo', $logoId, 'logo');

        return response()->json($siteSetting->load('logo'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SiteSetting $siteSetting): JsonResponse
    {
        $siteSetting->delete();

        return response()->json(['status' => 'deleted']);
    }
}
