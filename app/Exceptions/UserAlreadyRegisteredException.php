<?php

namespace App\Exceptions;

use Exception;

class UserAlreadyRegisteredException extends Exception
{
    public function render()
    {
        return response(['message' => $this->getMessage()], 400);
    }
}
