<?php

namespace App\Exceptions;

use Exception;

class ProductInventoryNotEnoughException extends Exception
{
    public function render()
    {
        return response(['message' => $this->getMessage()], 500);
    }
}
