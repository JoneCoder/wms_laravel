<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="WMS API Documentation",
 *      description="L5 Swagger OpenApi description",
 *      @OA\Contact(
 *          email="admin@wms.local"
 *      )
 * )
 * @OA\Server(
 *      url="http://localhost:8000",
 *      description="WMS API Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer"
 * )
 */
use App\Traits\ApiResponseTrait;

abstract class Controller
{
    use ApiResponseTrait;
}
