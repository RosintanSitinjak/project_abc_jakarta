<?php

namespace App\Support;

use App\Models\Portfolio;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PublicCatalogCache
{
    public const SERVICES_KEY = 'public:services';
    public const PRODUCTS_KEY = 'public:products';
    public const PORTFOLIOS_KEY = 'public:portfolios';

    public static function services(): Collection
    {
        return Cache::rememberForever(self::SERVICES_KEY, function () {
            return self::queryServices();
        });
    }

    public static function products(): Collection
    {
        return Cache::rememberForever(self::PRODUCTS_KEY, function () {
            return self::queryProducts();
        });
    }

    public static function portfolios(): Collection
    {
        return Cache::rememberForever(self::PORTFOLIOS_KEY, function () {
            return self::queryPortfolios();
        });
    }

    public static function refreshServices(): Collection
    {
        $services = self::queryServices();
        Cache::forever(self::SERVICES_KEY, $services);
        return $services;
    }

    public static function refreshProducts(): Collection
    {
        $products = self::queryProducts();
        Cache::forever(self::PRODUCTS_KEY, $products);
        return $products;
    }

    public static function refreshPortfolios(): Collection
    {
        $portfolios = self::queryPortfolios();
        Cache::forever(self::PORTFOLIOS_KEY, $portfolios);
        return $portfolios;
    }

    private static function queryServices(): Collection
    {
        return Service::query()
            ->select(['id', 'name', 'slug', 'description', 'content'])
            ->with([
                'scopes' => fn($q) => $q->select(['id', 'service_id', 'scope']),
                'thumbnail'
            ])
            ->latest()
            ->get();
    }
}