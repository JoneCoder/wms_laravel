<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getProducts(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage, $search);
    }

    public function createProduct(ProductDTO $dto): Product
    {
        return $this->productRepository->create($dto->toArray());
    }

    public function updateProduct(Product $product, ProductDTO $dto): Product
    {
        return $this->productRepository->update($product, $dto->toArray());
    }

    public function deleteProduct(Product $product): bool
    {
        return $this->productRepository->delete($product);
    }
}
