<?php

namespace App\Observers;

use App\Models\Portfolio;
use App\Support\PublicCatalogCache;

class PortfolioObserver
{
    public function saved(Portfolio $portfolio): void
    {
        PublicCatalogCache::refreshPortfolios();
    }

    public function deleted(Portfolio $portfolio): void
    {
        PublicCatalogCache::refreshPortfolios();
    }

    public function restored(Portfolio $portfolio): void
    {
        PublicCatalogCache::refreshPortfolios();
    }

    public function forceDeleted(Portfolio $portfolio): void
    {
        PublicCatalogCache::refreshPortfolios();
    }
}
