<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
    public function index(Request $request): JsonResponse
    {
        try {
            return $this->successResponse('Success', $this->permissionService->getAllPermissions()
            );
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while fetching permissions.', $e->getMessage()
            , 500);
        }
    }
}
