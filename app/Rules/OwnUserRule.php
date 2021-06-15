<?php

namespace App\Rules;

use App\Order;
use App\User;
use Illuminate\Contracts\Validation\Rule;

class OwnUserRule implements Rule
{
    private $type;

    /**
     * Create a new rule instance.
     *
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->type === Order::TYPE_PURCHASE) {
            return User::query()->where(['id' => $value, 'type' => User::TYPE_SELLER])->count();
        } elseif ($this->type === Order::TYPE_SALE) {
            return User::query()->where(['id' => $value, 'type' => User::TYPE_CUSTOMER])->count();
        } else {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid user id';
    }
}
