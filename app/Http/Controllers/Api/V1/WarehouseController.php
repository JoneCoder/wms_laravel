<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        try {
            $warehouses = Warehouse::paginate($request->per_page ?? 15);
            return response()->json(['success' => true, 'data' => $warehouses]);
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
                'code' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'address' => 'nullable|string',
                'status' => 'nullable|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
            }

            $warehouse = Warehouse::create($request->all());

            return response()->json(['success' => true, 'data' => $warehouse], 201);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Warehouse $warehouse)
    {
        try {
            return response()->json(['success' => true, 'data' => $warehouse]);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        try {
            $warehouse->update($request->all());
            return response()->json(['success' => true, 'data' => $warehouse]);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Warehouse $warehouse)
    {
        try {
            $warehouse->delete();
            return response()->json(['success' => true, 'message' => 'Warehouse deleted']);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
