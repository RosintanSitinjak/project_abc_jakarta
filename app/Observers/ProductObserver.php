<?php

namespace App\Observers;

use App\Models\Product;
use App\Support\PublicCatalogCache;

class ProductObserver
{
    public function saved(Product $product): void
    {
        PublicCatalogCache::refreshProducts();
    }

    public function deleted(Product $product): void
    {
        PublicCatalogCache::refreshProducts();
    }

    public function restored(Product $product): void
    {
        PublicCatalogCache::refreshProducts();
    }

    public function forceDeleted(Product $product): void
    {
        PublicCatalogCache::refreshProducts();
    }
}
