<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class DispatchStockDTO
{
    public function __construct(
        public readonly int $product_id,
        public readonly int $location_id,
        public readonly int $quantity,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            product_id: (int) $request->validated('product_id'),
            location_id: (int) $request->validated('location_id'),
            quantity: (int) $request->validated('quantity'),
        );
    }
}
