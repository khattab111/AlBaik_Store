<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage orders') || $user->hasAnyRole(['Super Admin', 'Admin', 'Manager']);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $this->viewAny($user);
    }

    public function update(User $user, Order $order): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }
}
