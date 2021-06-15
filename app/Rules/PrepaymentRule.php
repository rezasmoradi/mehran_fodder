<?php

namespace App\Rules;

use App\Payment;
use App\Product;
use Illuminate\Contracts\Validation\Rule;

class PrepaymentRule implements Rule
{
    private $productId;
    private $totalAmount;

    /**
     * Create a new rule instance.
     *
     * @param string|int $productId
     * @param int $totalAmount
     */
    public function __construct($productId, $totalAmount)
    {
        $this->productId = $productId;
        $this->totalAmount = $totalAmount;
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
        $totalPrice = payable($this->productId, $this->totalAmount);
        $minPrepayment = ($totalPrice[1] * 10) / 100;
        return $value >= $minPrepayment;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The at least pre-payment is not observed';
    }
}
