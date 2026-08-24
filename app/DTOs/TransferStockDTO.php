<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class TransferStockDTO
{
    public function __construct(
        public readonly int $product_id,
        public readonly int $source_location_id,
        public readonly int $destination_location_id,
        public readonly int $quantity,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            product_id: (int) $request->validated('product_id'),
            source_location_id: (int) $request->validated('source_location_id'),
            destination_location_id: (int) $request->validated('destination_location_id'),
            quantity: (int) $request->validated('quantity'),
        );
    }
}
