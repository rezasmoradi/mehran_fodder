<?php

namespace App\Policies;

use App\Transportation;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransportationPolicy
{
    use HandlesAuthorization;

    private $exists = false;

    public function viewAllByEmployees(User $user, Transportation $transportation)
    {
        return $user->isAdmin() || $user->isUserType(User::TYPE_WAREHOUSE_KEEPER);
    }

    public function viewAll(User $user)
    {
        return $user->isAdmin()
            || $user->isUserType(User::TYPE_WAREHOUSE_KEEPER)
            || $user->isUserType(User::TYPE_CUSTOMER);
    }

    public function view(User $user, Transportation $transportation)
    {
        $userTransportations = $transportation->user($user)->select('order_id')->get()->toArray();
        foreach ($userTransportations as $userTransportation) {
            if ($transportation->getAttribute('order_id') === $userTransportation['order_id']) {
                $this->exists = true;
            }
        }

        return $user->isAdmin()
            || $user->isUserType(User::TYPE_WAREHOUSE_KEEPER)
            || $this->exists;
    }

    public function update(User $user, Transportation $transportation)
    {
        $user->isAdmin() || $user->isUserType(User::TYPE_WAREHOUSE_KEEPER);
    }

    public function delete(User $user, Transportation $transportation)
    {
        return $user->isAdmin() || $user->isUserType(User::TYPE_WAREHOUSE_KEEPER);
    }

    public function restore(User $user, Transportation $transportation)
    {
        return $user->isAdmin();
    }

    public function destroy(User $user, Transportation $transportation)
    {
        return $user->isAdmin();
    }
}
