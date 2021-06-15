<?php

namespace App\Rules;

use Hekmatinasser\Verta\Verta;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class AfterNowRule implements Rule
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
        return diff_after_now($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The time must be after now';
    }
}
