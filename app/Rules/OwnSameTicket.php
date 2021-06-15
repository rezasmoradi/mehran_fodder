<?php

namespace App\Rules;

use App\Ticket;
use Illuminate\Contracts\Validation\Rule;

class OwnSameTicket implements Rule
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
        $sameUserRequest = Ticket::query()
                ->where(['user_ip' => client_ip(), 'request_text' => $value])
                ->count() == 0;
        $tooRequest = Ticket::query()->where('request_text', $value)->count() < 3;
        return $sameUserRequest && $tooRequest;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Too Many Request.';
    }
}
