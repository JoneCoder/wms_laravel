<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\WarehouseController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\PermissionController;

Route::prefix('v1')->group(function () {
    
    // Public routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        Route::get('/user', function (Request $request) {
            return response()->json([
                'success' => true,
                'data' => $request->user()->load('role.permissions', 'organization')
            ]);
        });

        // Roles & Permissions (manage_roles)
        Route::middleware('permission:manage_roles')->group(function () {
            Route::apiResource('roles', RoleController::class)->except(['show', 'destroy']);
            Route::get('permissions', [PermissionController::class, 'index']);
        });

        // Products (manage_products)
        Route::middleware('permission:manage_products')->group(function () {
            Route::apiResource('products', ProductController::class);
        });

        // Warehouses & Locations (manage_warehouses)
        Route::middleware('permission:manage_warehouses')->group(function () {
            Route::apiResource('warehouses', WarehouseController::class);
            Route::apiResource('warehouses.locations', LocationController::class)->scoped();
        });

        // Inventory
        Route::prefix('inventory')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->middleware('permission:view_inventory');
            Route::post('receive', [InventoryController::class, 'receive'])->middleware('permission:receive_inventory');
            Route::post('transfer', [InventoryController::class, 'transfer'])->middleware('permission:transfer_inventory');
            Route::post('dispatch', [InventoryController::class, 'dispatchStock'])->middleware('permission:dispatch_inventory');
        });
        
        Route::get('/stock-movements', [InventoryController::class, 'movements'])->middleware('permission:view_inventory');
    });
});
