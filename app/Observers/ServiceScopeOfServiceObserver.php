<?php

namespace App\Observers;

use App\Models\ServiceScopeOfService;
use App\Support\PublicCatalogCache;

class ServiceScopeOfServiceObserver
{
    public function saved(ServiceScopeOfService $serviceScopeOfService): void
    {
        PublicCatalogCache::refreshServices();
    }

    public function deleted(ServiceScopeOfService $serviceScopeOfService): void
    {
        PublicCatalogCache::refreshServices();
    }

    public function restored(ServiceScopeOfService $serviceScopeOfService): void
    {
        PublicCatalogCache::refreshServices();
    }

    public function forceDeleted(ServiceScopeOfService $serviceScopeOfService): void
    {
        PublicCatalogCache::refreshServices();
    }
}
