<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::with(['brand', 'supplier', 'category', 'images', 'variants'])
            ->where('status', true);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('retail_price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('retail_price', '<=', $filters['max_price']);
        }

        $sort = $filters['sort'] ?? 'latest';

        match ($sort) {
            'price_asc' => $query->orderBy('retail_price'),
            'price_desc' => $query->orderByDesc('retail_price'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return $query->paginate(min($perPage, 60));
    }

    public function find(int $id): ?Product
    {
        return Product::with(['brand', 'supplier', 'category', 'images', 'variants', 'reviews'])->find($id);
    }
}
