<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DiscountRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $discountRegex = '~^[1-9]\d{3,12}(?:\.\d{3})?|0$~';
        preg_match($discountRegex, $value, $matches);
        return !empty($matches);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid discount format.';
    }
}
