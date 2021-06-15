<?php

namespace App\Policies;

use App\User;
use App\order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        $allowed = [User::TYPE_ADMIN, User::TYPE_CUSTOMER, User::TYPE_ORDER_RESPONSIBLE];
        return array_search($user->type, $allowed) !== false;
    }

    public function delete(User $user, Order $order)
    {
        return $user->isAdmin()
            || $user->isUserType(User::TYPE_ORDER_RESPONSIBLE)
            || $user->getAuthIdentifier() === $order->getAttribute('user_id');
    }

    public function view(User $user, Order $order)
    {
        return $user->isAdmin()
            || (array_search($user->type, User::EMPLOYEE_TYPES) !== false)
            || $user->getAuthIdentifier() === $order->getAttribute('user_id');
    }

    public function viewAllByEmployees(User $user, Order $order)
    {
        return array_search($user->type, User::EMPLOYEE_TYPES) !== false;
    }

    public function restore(User $user, Order $order)
    {
        return $user->isAdmin() || $user->isUserType(User::TYPE_ORDER_RESPONSIBLE);
    }
}
