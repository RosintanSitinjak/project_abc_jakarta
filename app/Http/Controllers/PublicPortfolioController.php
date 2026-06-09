<?php

namespace App\Http\Controllers;

use App\Support\PublicCatalogCache;
use Illuminate\Http\JsonResponse;

class PublicPortfolioController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(PublicCatalogCache::portfolios());
    }
}
