<?php

namespace App\Http\Middleware;

use App\Services\PermissionCacheService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    protected PermissionCacheService $permissionCache;

    public function __construct(PermissionCacheService $permissionCache)
    {
        $this->permissionCache = $permissionCache;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        // Load permissions into singleton (cached per request/Redis)
        $this->permissionCache->loadPermissions($user);

        if (!$this->permissionCache->hasPermission($permission)) {
            return response()->json(['success' => false, 'message' => 'Forbidden: You do not have the required permission.'], 403);
        }

        return $next($request);
    }
}
