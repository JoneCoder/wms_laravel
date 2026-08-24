<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class WarehouseDTO
{
    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $name = null,
        public readonly ?string $address = null,
        public readonly ?string $status = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            code: $request->validated('code'),
            name: $request->validated('name'),
            address: $request->validated('address'),
            status: $request->validated('status'),
        );
    }
    
    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'status' => $this->status,
        ], fn($value) => !is_null($value));
    }
}
