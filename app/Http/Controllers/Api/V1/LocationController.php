<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function index(Request $request, Warehouse $warehouse)
    {
        try {
            $locations = $warehouse->locations()->paginate($request->per_page ?? 15);
            return response()->json(['success' => true, 'data' => $locations]);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, Warehouse $warehouse)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'status' => 'nullable|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
            }

            // organization_id handles by trait automatically
            $location = $warehouse->locations()->create($request->all());

            return response()->json(['success' => true, 'data' => $location], 201);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Warehouse $warehouse, Location $location)
    {
        try {
            return response()->json(['success' => true, 'data' => $location]);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Warehouse $warehouse, Location $location)
    {
        try {
            $location->update($request->all());
            return response()->json(['success' => true, 'data' => $location]);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Warehouse $warehouse, Location $location)
    {
        try {
            $location->delete();
            return response()->json(['success' => true, 'message' => 'Location deleted']);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
