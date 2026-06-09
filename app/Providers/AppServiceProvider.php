<?php

namespace App\Providers;

use App\Models\Portfolio;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceScopeOfService;
use App\Observers\PortfolioObserver;
use App\Observers\ProductObserver;
use App\Observers\ServiceObserver;
use App\Observers\ServiceScopeOfServiceObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Service::observe(ServiceObserver::class);
        ServiceScopeOfService::observe(ServiceScopeOfServiceObserver::class);
        Product::observe(ProductObserver::class);
        Portfolio::observe(PortfolioObserver::class);

        Relation::morphMap([
            'article' => \App\Models\Article::class,
            'service' => \App\Models\Service::class,
            'product' => \App\Models\Product::class,
            'portfolio' => \App\Models\Portfolio::class,
        ]);
    }
}
