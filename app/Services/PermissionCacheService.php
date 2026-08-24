<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionCacheService
{
    protected array $permissions = [];
    protected ?int $userId = null;

    /**
     * Load permissions for the user and cache them in memory for the request lifecycle.
     */
    public function loadPermissions(User $user): void
    {
        if ($this->userId === $user->id) {
            return;
        }

        // We can also leverage Redis caching here for longer persistence
        $cacheKey = 'user_permissions_' . $user->id;

        $this->permissions = Cache::remember($cacheKey, 3600, function () use ($user) {
            $user->loadMissing('role.permissions');
            if (!$user->role) {
                return [];
            }
            return $user->role->permissions->pluck('name')->toArray();
        });

        $this->userId = $user->id;
    }

    /**
     * Check if the loaded user has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return in_array($permissionName, $this->permissions);
    }

    /**
     * Clear the cache for a specific user (useful when roles/permissions are updated).
     */
    public function clearCache(int $userId): void
    {
        Cache::forget('user_permissions_' . $userId);
        if ($this->userId === $userId) {
            $this->permissions = [];
            $this->userId = null;
        }
    }
}
