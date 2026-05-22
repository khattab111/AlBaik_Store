<?php

namespace App\Services;

use App\Repositories\ProductRepository;

class ProductService
{
    public function __construct(protected ProductRepository $repository) {}

    public function list(array $filters = [], int $perPage = 12)
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function get(int $id)
    {
        return $this->repository->find($id);
    }
}
