<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class RegisterDTO
{
    public function __construct(
        public readonly string $organization_name,
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            organization_name: $request->validated('organization_name'),
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
        );
    }
}
