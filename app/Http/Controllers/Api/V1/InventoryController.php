<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use App\DTOs\ReceiveStockDTO;
use App\DTOs\TransferStockDTO;
use App\DTOs\DispatchStockDTO;
use App\Http\Requests\ReceiveStockRequest;
use App\Http\Requests\TransferStockRequest;
use App\Http\Requests\DispatchStockRequest;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * @OA\Get(
     *      path="/api/v1/inventory",
     *      operationId="getInventoryList",
     *      tags={"Inventory"},
     *      summary="Get list of inventory",
     *      description="Returns list of inventory paginated",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Number of items per page",
     *          required=false,
     *          @OA\Schema(type="integer", default=20)
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 20;
            $search = $request->input('search');
            $inventory = $this->inventoryService->getInventory($perPage, $search);

            return response()->json([
                'success' => true,
                'data' => $inventory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/inventory/receive",
     *      operationId="receiveStock",
     *      tags={"Inventory"},
     *      summary="Receive stock",
     *      description="Receives stock into a location",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"product_id", "location_id", "quantity"},
     *              @OA\Property(property="product_id", type="integer", example=1),
     *              @OA\Property(property="location_id", type="integer", example=1),
     *              @OA\Property(property="quantity", type="integer", example=100),
     *              @OA\Property(property="reference_number", type="string", example="PO-12345")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function receive(ReceiveStockRequest $request)
    {
        try {
            $dto = ReceiveStockDTO::fromRequest($request);
            $referenceNumber = $request->input('reference_number');

            $result = $this->inventoryService->receive($dto, $referenceNumber);

            return response()->json([
                'success' => true,
                'message' => 'Stock received successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to receive stock.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/inventory/transfer",
     *      operationId="transferStock",
     *      tags={"Inventory"},
     *      summary="Transfer stock",
     *      description="Transfers stock between locations",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"product_id", "source_location_id", "destination_location_id", "quantity"},
     *              @OA\Property(property="product_id", type="integer", example=1),
     *              @OA\Property(property="source_location_id", type="integer", example=1),
     *              @OA\Property(property="destination_location_id", type="integer", example=2),
     *              @OA\Property(property="quantity", type="integer", example=50),
     *              @OA\Property(property="reference_number", type="string", example="TR-9876")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function transfer(TransferStockRequest $request)
    {
        try {
            $dto = TransferStockDTO::fromRequest($request);
            $referenceNumber = $request->input('reference_number');

            $result = $this->inventoryService->transfer($dto, $referenceNumber);

            return response()->json([
                'success' => true,
                'message' => 'Stock transferred successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer stock.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/inventory/dispatch",
     *      operationId="dispatchStock",
     *      tags={"Inventory"},
     *      summary="Dispatch stock",
     *      description="Dispatches stock from a location",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"product_id", "location_id", "quantity"},
     *              @OA\Property(property="product_id", type="integer", example=1),
     *              @OA\Property(property="location_id", type="integer", example=1),
     *              @OA\Property(property="quantity", type="integer", example=10),
     *              @OA\Property(property="reference_number", type="string", example="SO-5555")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function dispatchStock(DispatchStockRequest $request)
    {
        try {
            $dto = DispatchStockDTO::fromRequest($request);
            $referenceNumber = $request->input('reference_number');

            $result = $this->inventoryService->dispatchStock($dto, $referenceNumber);

            return response()->json([
                'success' => true,
                'message' => 'Stock dispatched successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch stock.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/inventory/movements",
     *      operationId="getStockMovements",
     *      tags={"Inventory"},
     *      summary="Get list of stock movements",
     *      description="Returns list of stock movements paginated",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Number of items per page",
     *          required=false,
     *          @OA\Schema(type="integer", default=20)
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function movements(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 20;
            $search = $request->input('search');
            $movements = $this->inventoryService->getMovements($perPage, $search);

            return response()->json([
                'success' => true,
                'data' => $movements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
