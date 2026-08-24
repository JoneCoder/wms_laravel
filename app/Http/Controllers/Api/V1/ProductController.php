<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Global scope ensures we only see our organization's products
            $products = Product::paginate($request->per_page ?? 15);
            return response()->json(['success' => true, 'data' => $products]);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sku' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'unit' => 'nullable|string',
                'status' => 'nullable|in:active,inactive',
                'low_stock_threshold' => 'nullable|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
            }

            // organization_id will be automatically injected by BelongsToOrganization trait
            $product = Product::create($request->all());

            return response()->json(['success' => true, 'data' => $product], 201);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Product $product)
    {
        try {
            return response()->json(['success' => true, 'data' => $product]);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Product $product)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sku' => 'sometimes|string|max:255',
                'name' => 'sometimes|string|max:255',
                'status' => 'sometimes|in:active,inactive',
                'low_stock_threshold' => 'sometimes|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
            }

            $product->update($request->all());

            return response()->json(['success' => true, 'data' => $product]);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
