<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        try {
            $this->inventoryService = $inventoryService;
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $orgId = auth()->user()->organization_id;
            $page = $request->page ?? 1;
            $cacheKey = "inventory_org_{$orgId}_page_{$page}";

            $inventory = Cache::remember($cacheKey, 60, function () {
                return Inventory::with(['product', 'location.warehouse'])->paginate(20);
            });

            return response()->json(['success' => true, 'data' => $inventory]);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function receive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:1',
            'reference_number' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->inventoryService->receive(
                $request->product_id,
                $request->location_id,
                $request->quantity,
                $request->reference_number
            );
            $this->clearInventoryCache();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'source_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|exists:locations,id|different:source_location_id',
            'quantity' => 'required|integer|min:1',
            'reference_number' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->inventoryService->transfer(
                $request->product_id,
                $request->source_location_id,
                $request->destination_location_id,
                $request->quantity,
                $request->reference_number
            );
            $this->clearInventoryCache();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function dispatchStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'source_location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:1',
            'reference_number' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->inventoryService->dispatchStock(
                $request->product_id,
                $request->source_location_id,
                $request->quantity,
                $request->reference_number
            );
            $this->clearInventoryCache();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function movements(Request $request)
    {
        try {
            $query = StockMovement::with(['product', 'sourceLocation', 'destinationLocation', 'user']);

            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $movements = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

            return response()->json(['success' => true, 'data' => $movements]);
            } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function clearInventoryCache()
    {
        // simplistic cache clearing strategy for this assignment
        // in a real app, you'd use tags Cache::tags(['inventory'])->flush(); (if using redis)
        if (config('cache.default') === 'redis') {
             // Let's assume Redis is default or fallback to flush cache, 
             // but `flush` wipes everything. Best to use tags.
             Cache::flush(); 
        }
    }
}
