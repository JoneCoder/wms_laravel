<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\DTOs\RoleDTO;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    /**
     * @OA\Get(
     *      path="/api/v1/roles",
     *      operationId="getRolesList",
     *      tags={"Roles"},
     *      summary="Get list of roles",
     *      description="Returns list of roles for the current organization",
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
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $roles = $this->roleService->paginateRoles($perPage, $search);

            return $this->successResponse('Success', $roles
            );
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while fetching roles.', $e->getMessage()
            , 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/roles",
     *      operationId="storeRole",
     *      tags={"Roles"},
     *      summary="Store new role",
     *      description="Creates a new role",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", type="string", example="manager"),
     *              @OA\Property(property="permissions", type="array", @OA\Items(type="integer", example=1))
     *          )
     *      ),
     *      @OA\Response(response=201, description="Successful operation"),
     *      @OA\Response(response=400, description="Bad Request")
     * )
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            $roleDTO = RoleDTO::fromRequest($request);
            $role = $this->roleService->createRole($roleDTO, $request->user()->organization_id);

            return response()->json([
                'success' => true,
                'data' => $role->load('permissions')
            ], 201);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while storing the role.', $e->getMessage()
            , 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/roles/{id}",
     *      operationId="updateRole",
     *      tags={"Roles"},
     *      summary="Update existing role",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Role id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="manager"),
     *              @OA\Property(property="permissions", type="array", @OA\Items(type="integer"))
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        try {
            $roleDTO = RoleDTO::fromRequest($request);
            $updatedRole = $this->roleService->updateRole($role, $roleDTO);

            return $this->successResponse('Success', $updatedRole->fresh('permissions')
            );
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while updating the role.', $e->getMessage()
            , 500);
        }
    }
}
