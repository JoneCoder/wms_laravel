<?php

namespace App\Repositories\Contracts;

use App\Models\Organization;

interface OrganizationRepositoryInterface
{
    public function create(array $data): Organization;
}
