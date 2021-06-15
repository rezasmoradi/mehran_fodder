<?php

namespace App\Policies;

use App\Payment;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    private $exists;
    private $userPayments;

    public function viewAllByRoute(User $user, Payment $payment)
    {
        $this->userPayments = $payment->user($user)->select('order_id')->get()->toArray();
        foreach ($this->userPayments as $userPayment) {
            if ($payment->getAttribute('order_id') === $userPayment['order_id']) {
                $this->exists = true;
            }
        }
        return $user->isAdmin() || $user->isUserType(User::TYPE_ACCOUNTANT) || $this->exists;
    }

    public function viewAll(User $user)
    {
        return $user->isAdmin() || $user->isUserType(User::TYPE_ACCOUNTANT);
    }

    public function view(User $user, Payment $payment)
    {
        $this->userPayments = $payment->user($user)->select('order_id')->get()->toArray();
        foreach ($this->userPayments as $userPayment) {
            if ($payment->getAttribute('order_id') === $userPayment['order_id']) {
                $this->exists = true;
            }
        }
        return $user->isAdmin()
            || $user->isUserType(User::TYPE_ACCOUNTANT)
            || $this->exists;
    }

    public function update(User $user, Payment $payment)
    {
        return $user->isAdmin() || $user->isUserType(User::TYPE_ACCOUNTANT);
    }

    public function create(User $user, Payment $payment)
    {
        if ($user->isAdmin() && $user->isUserType(User::TYPE_ACCOUNTANT)) {
            return true;
        } elseif ($user->isCustomer() && $payment->isGateway($payment->method)) {
            return true;
        }
        return false;
    }

    public function delete(User $user, Payment $payment)
    {
        return $user->isAdmin();
    }
}
