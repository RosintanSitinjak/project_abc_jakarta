<?php

namespace App\Http\Controllers;

use App\Support\PublicCatalogCache;
use Illuminate\Http\JsonResponse;

class PublicProductController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(PublicCatalogCache::products());
    }
}
