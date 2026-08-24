<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Repositories\Eloquent\PermissionRepository;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Eloquent\OrganizationRepository;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Repositories\Eloquent\WarehouseRepository;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Eloquent\LocationRepository;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Eloquent\InventoryRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(OrganizationRepositoryInterface::class, OrganizationRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(WarehouseRepositoryInterface::class, WarehouseRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
