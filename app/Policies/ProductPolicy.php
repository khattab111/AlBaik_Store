<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Product $product): bool
    {
        return $product->status || ($user && $this->manage($user));
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->manage($user);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    private function manage(User $user): bool
    {
        return $user->can('manage products') || $user->hasAnyRole(['Super Admin', 'Admin', 'Manager']);
    }
}
