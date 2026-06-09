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
        return Cache::store('redis')->rememberForever(self::SERVICES_KEY, function () {
            return self::queryServices();
        });
    }

    public static function products(): Collection
    {
        return Cache::store('redis')->rememberForever(self::PRODUCTS_KEY, function () {
            return self::queryProducts();
        });
    }

    public static function portfolios(): Collection
    {
        return Cache::store('redis')->rememberForever(self::PORTFOLIOS_KEY, function () {
            return self::queryPortfolios();
        });
    }

    public static function refreshServices(): Collection
    {
        $services = self::queryServices();
        Cache::store('redis')->forever(self::SERVICES_KEY, $services);

        return $services;
    }

    public static function refreshProducts(): Collection
    {
        $products = self::queryProducts();
        Cache::store('redis')->forever(self::PRODUCTS_KEY, $products);

        return $products;
    }

    public static function refreshPortfolios(): Collection
    {
        $portfolios = self::queryPortfolios();
        Cache::store('redis')->forever(self::PORTFOLIOS_KEY, $portfolios);

        return $portfolios;
    }

    private static function queryServices(): Collection
    {
        return Service::query()
            ->select(['id', 'name', 'slug', 'description', 'content'])
            ->with([
                'scopes' => function ($query) {
                    $query->select(['id', 'service_id', 'scope']);
                },
                'thumbnail' => function ($query) {
                    $query->select([
                        'id',
                        'attachmentable_id',
                        'attachmentable_type',
                        'name',
                        'path',
                        'size',
                        'mime',
                        'disk',
                        'folder',
                        'type',
                        'remark',
                    ]);
                },
            ])
            ->latest()
            ->get();
    }

    private static function queryProducts(): Collection
    {
        return Product::query()
            ->select(['id', 'category_id', 'title', 'description', 'feature', 'area'])
            ->with([
                'category' => function ($query) {
                    $query->select(['id', 'name', 'description']);
                },
                'thumbnail' => function ($query) {
                    $query->select([
                        'id',
                        'attachmentable_id',
                        'attachmentable_type',
                        'name',
                        'path',
                        'size',
                        'mime',
                        'disk',
                        'folder',
                        'type',
                        'remark',
                    ]);
                },
            ])
            ->orderBy('position', 'asc')
            ->latest()
            ->get();
    }

    private static function queryPortfolios(): Collection
    {
        return Portfolio::query()
            ->select([
                'id',
                'category_id',
                'title',
                'description',
                'client_name',
                'year',
                'areas',
            ])
            ->with([
                'category' => function ($query) {
                    $query->select(['id', 'name', 'description']);
                },
                'thumbnail' => function ($query) {
                    $query->select([
                        'id',
                        'attachmentable_id',
                        'attachmentable_type',
                        'name',
                        'path',
                        'size',
                        'mime',
                        'disk',
                        'folder',
                        'type',
                        'remark',
                    ]);
                },
                'photos' => function ($query) {
                    $query->select([
                        'id',
                        'attachmentable_id',
                        'attachmentable_type',
                        'name',
                        'path',
                        'size',
                        'mime',
                        'disk',
                        'folder',
                        'type',
                        'remark',
                    ])->orderBy('created_at');
                },
            ])
            ->latest()
            ->get();
    }
}
