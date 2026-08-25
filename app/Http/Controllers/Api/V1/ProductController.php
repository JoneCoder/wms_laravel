<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\DTOs\ProductDTO;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * @OA\Get(
     *      path="/api/v1/products",
     *      operationId="getProductsList",
     *      tags={"Products"},
     *      summary="Get list of products",
     *      description="Returns list of products paginated",
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
            $products = $this->productService->getProducts($perPage, $search);

            return $this->successResponse('Success', $products
            );
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred.', $e->getMessage()
            , 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/products",
     *      operationId="storeProduct",
     *      tags={"Products"},
     *      summary="Create new product",
     *      description="Creates a new product record",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"sku", "name"},
     *              @OA\Property(property="sku", type="string", example="SKU-1001"),
     *              @OA\Property(property="name", type="string", example="Widget A"),
     *              @OA\Property(property="description", type="string", example="A useful widget"),
     *              @OA\Property(property="unit", type="string", example="pcs"),
     *              @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *              @OA\Property(property="low_stock_threshold", type="integer", example=10)
     *          )
     *      ),
     *      @OA\Response(response=201, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $dto = ProductDTO::fromRequest($request);
            $product = $this->productService->createProduct($dto);

            return $this->successResponse('Product created successfully', $product, 201);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred.', $e->getMessage()
            , 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/products/{product}",
     *      operationId="getProductById",
     *      tags={"Products"},
     *      summary="Get product information",
     *      description="Returns product data",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="product",
     *          description="Product id",
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
    public function show(Product $product): JsonResponse
    {
        try {
            return $this->successResponse('Success', $product
            );
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred.', $e->getMessage()
            , 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/products/{product}",
     *      operationId="updateProduct",
     *      tags={"Products"},
     *      summary="Update existing product",
     *      description="Updates product data",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="product",
     *          description="Product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="sku", type="string", example="SKU-1001-B"),
     *              @OA\Property(property="name", type="string", example="Widget B"),
     *              @OA\Property(property="description", type="string", example="An updated widget"),
     *              @OA\Property(property="unit", type="string", example="box"),
     *              @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *              @OA\Property(property="low_stock_threshold", type="integer", example=20)
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $dto = ProductDTO::fromRequest($request);
            $updatedProduct = $this->productService->updateProduct($product, $dto);

            return $this->successResponse('Success', $updatedProduct
            );
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred.', $e->getMessage()
            , 500);
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/products/{product}",
     *      operationId="deleteProduct",
     *      tags={"Products"},
     *      summary="Delete existing product",
     *      description="Deletes a record and returns no content",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="product",
     *          description="Product id",
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
    public function destroy(Product $product): JsonResponse
    {
        try {
            $this->productService->deleteProduct($product);

            return $this->successResponse('Product deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred.', $e->getMessage()
            , 500);
        }
    }
}
