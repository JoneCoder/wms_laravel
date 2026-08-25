<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\DTOs\WarehouseDTO;
use App\Services\WarehouseService;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WarehouseController extends Controller
{
    public function __construct(
        protected WarehouseService $warehouseService
    ) {}

    /**
     * @OA\Get(
     *      path="/api/v1/warehouses",
     *      operationId="getWarehousesList",
     *      tags={"Warehouses"},
     *      summary="Get list of warehouses",
     *      description="Returns list of warehouses paginated",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Number of items per page",
     *          required=false,
     *          @OA\Schema(type="integer", default=15)
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->per_page ?? 15;
            $search = $request->input('search');
            $warehouses = $this->warehouseService->getWarehouses($perPage, $search);

            return $this->successResponse('Success', $warehouses
            );
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred.', $e->getMessage()
            , 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/warehouses",
     *      operationId="storeWarehouse",
     *      tags={"Warehouses"},
     *      summary="Create new warehouse",
     *      description="Creates a new warehouse record",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"code", "name"},
     *              @OA\Property(property="code", type="string", example="WH-001"),
     *              @OA\Property(property="name", type="string", example="Main Warehouse"),
     *              @OA\Property(property="address", type="string", example="123 Storage St"),
     *              @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active")
     *          )
     *      ),
     *      @OA\Response(response=201, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        try {
            $dto = WarehouseDTO::fromRequest($request);
            $warehouse = $this->warehouseService->createWarehouse($dto);

            return $this->successResponse('Warehouse created successfully', $warehouse, 201);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred.', $e->getMessage()
            , 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/warehouses/{warehouse}",
     *      operationId="getWarehouseById",
     *      tags={"Warehouses"},
     *      summary="Get warehouse information",
     *      description="Returns warehouse data",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="warehouse",
     *          description="Warehouse id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        try {
            return $this->successResponse('Success', $warehouse
            );
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred.', $e->getMessage()
            , 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/warehouses/{warehouse}",
     *      operationId="updateWarehouse",
     *      tags={"Warehouses"},
     *      summary="Update existing warehouse",
     *      description="Updates warehouse data",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="warehouse",
     *          description="Warehouse id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="code", type="string", example="WH-001-A"),
     *              @OA\Property(property="name", type="string", example="Updated Warehouse"),
     *              @OA\Property(property="address", type="string", example="456 New Storage St"),
     *              @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        try {
            $dto = WarehouseDTO::fromRequest($request);
            $updatedWarehouse = $this->warehouseService->updateWarehouse($warehouse, $dto);

            return $this->successResponse('Success', $updatedWarehouse
            );
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred.', $e->getMessage()
            , 500);
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/warehouses/{warehouse}",
     *      operationId="deleteWarehouse",
     *      tags={"Warehouses"},
     *      summary="Delete existing warehouse",
     *      description="Deletes a record and returns no content",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="warehouse",
     *          description="Warehouse id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        try {
            $this->warehouseService->deleteWarehouse($warehouse);

            return $this->successResponse('Warehouse deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred.', $e->getMessage()
            , 500);
        }
    }
}
