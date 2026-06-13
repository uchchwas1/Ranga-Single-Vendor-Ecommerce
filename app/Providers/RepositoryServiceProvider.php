<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\BrandRepositoryContract;
use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Repositories\Contracts\InventoryRepositoryContract;
use App\Repositories\Contracts\ProductRepositoryContract;
use App\Repositories\Contracts\SettingRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\Eloquent\EloquentBrandRepository;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Repositories\Eloquent\EloquentInventoryRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Repositories\Eloquent\EloquentSettingRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds repository contracts to their Eloquent implementations.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryContract::class, EloquentUserRepository::class);
        $this->app->bind(SettingRepositoryContract::class, EloquentSettingRepository::class);
        $this->app->bind(ProductRepositoryContract::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepositoryContract::class, EloquentCategoryRepository::class);
        $this->app->bind(BrandRepositoryContract::class, EloquentBrandRepository::class);
        $this->app->bind(InventoryRepositoryContract::class, EloquentInventoryRepository::class);
    }
}
