<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class ProductDTO
{
    public function __construct(
        public readonly ?string $sku = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $unit = null,
        public readonly ?string $status = null,
        public readonly ?int $low_stock_threshold = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            sku: $request->validated('sku'),
            name: $request->validated('name'),
            description: $request->validated('description'),
            unit: $request->validated('unit'),
            status: $request->validated('status'),
            low_stock_threshold: is_null($request->validated('low_stock_threshold')) ? null : (int) $request->validated('low_stock_threshold'),
        );
    }
    
    public function toArray(): array
    {
        return array_filter([
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'unit' => $this->unit,
            'status' => $this->status,
            'low_stock_threshold' => $this->low_stock_threshold,
        ], fn($value) => !is_null($value));
    }
}
