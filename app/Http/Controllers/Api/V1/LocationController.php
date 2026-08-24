<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Warehouse;
use App\DTOs\LocationDTO;
use App\Services\LocationService;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(
        protected LocationService $locationService
    ) {}

    /**
     * @OA\Get(
     *      path="/api/v1/warehouses/{warehouse}/locations",
     *      operationId="getLocationsList",
     *      tags={"Locations"},
     *      summary="Get list of locations in a warehouse",
     *      description="Returns list of locations paginated",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="warehouse",
     *          in="path",
     *          description="Warehouse id",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Number of items per page",
     *          required=false,
     *          @OA\Schema(type="integer", default=15)
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Warehouse Not Found")
     * )
     */
    public function index(Request $request, Warehouse $warehouse)
    {
        try {
            $perPage = $request->per_page ?? 15;
            $search = $request->input('search');
            $locations = $this->locationService->getLocations($warehouse, $perPage, $search);

            return response()->json([
                'success' => true,
                'data' => $locations
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
     *      path="/api/v1/warehouses/{warehouse}/locations",
     *      operationId="storeLocation",
     *      tags={"Locations"},
     *      summary="Create new location",
     *      description="Creates a new location within a warehouse",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="warehouse",
     *          in="path",
     *          description="Warehouse id",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"code", "name"},
     *              @OA\Property(property="code", type="string", example="A1-01"),
     *              @OA\Property(property="name", type="string", example="Aisle 1 Rack 1"),
     *              @OA\Property(property="type", type="string", enum={"bin", "rack", "shelf"}, example="rack"),
     *              @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active")
     *          )
     *      ),
     *      @OA\Response(response=201, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Warehouse Not Found"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreLocationRequest $request, Warehouse $warehouse)
    {
        try {
            $dto = LocationDTO::fromRequest($request);
            $location = $this->locationService->createLocation($warehouse, $dto);

            return response()->json([
                'success' => true, 
                'data' => $location
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/warehouses/{warehouse}/locations/{location}",
     *      operationId="getLocationById",
     *      tags={"Locations"},
     *      summary="Get location information",
     *      description="Returns location data",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="warehouse",
     *          description="Warehouse id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="location",
     *          description="Location id",
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
    public function show(Warehouse $warehouse, Location $location)
    {
        try {
            return response()->json([
                'success' => true, 
                'data' => $location
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
     * @OA\Put(
     *      path="/api/v1/warehouses/{warehouse}/locations/{location}",
     *      operationId="updateLocation",
     *      tags={"Locations"},
     *      summary="Update existing location",
     *      description="Updates location data",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="warehouse",
     *          description="Warehouse id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="location",
     *          description="Location id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="code", type="string", example="A1-01-B"),
     *              @OA\Property(property="name", type="string", example="Updated Rack"),
     *              @OA\Property(property="type", type="string", enum={"bin", "rack", "shelf"}, example="shelf"),
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
    public function update(UpdateLocationRequest $request, Warehouse $warehouse, Location $location)
    {
        try {
            $dto = LocationDTO::fromRequest($request);
            $updatedLocation = $this->locationService->updateLocation($location, $dto);

            return response()->json([
                'success' => true, 
                'data' => $updatedLocation
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
     * @OA\Delete(
     *      path="/api/v1/warehouses/{warehouse}/locations/{location}",
     *      operationId="deleteLocation",
     *      tags={"Locations"},
     *      summary="Delete existing location",
     *      description="Deletes a record and returns no content",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="warehouse",
     *          description="Warehouse id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="location",
     *          description="Location id",
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
    public function destroy(Warehouse $warehouse, Location $location)
    {
        try {
            $this->locationService->deleteLocation($location);

            return response()->json([
                'success' => true, 
                'message' => 'Location deleted successfully'
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
