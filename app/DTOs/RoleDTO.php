<?php

namespace App\DTOs;

class RoleDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?array $permissions
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            permissions: $request->validated('permissions')
        );
    }
    
    public function toArray(): array
    {
        $data = [];
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        return $data;
    }
}
