<?php

namespace App\Observers;

use App\Models\Service;
use App\Support\PublicCatalogCache;

class ServiceObserver
{
    public function saved(Service $service): void
    {
        PublicCatalogCache::refreshServices();
    }

    public function deleted(Service $service): void
    {
        PublicCatalogCache::refreshServices();
    }

    public function restored(Service $service): void
    {
        PublicCatalogCache::refreshServices();
    }

    public function forceDeleted(Service $service): void
    {
        PublicCatalogCache::refreshServices();
    }
}
