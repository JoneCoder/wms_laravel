<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * @OA\Get(
     *      path="/api/v1/permissions",
     *      operationId="getPermissionsList",
     *      tags={"Permissions"},
     *      summary="Get list of all available permissions",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *       )
     *     )
     */
    public function index(Request $request)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->permissionService->getAllPermissions()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching permissions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
