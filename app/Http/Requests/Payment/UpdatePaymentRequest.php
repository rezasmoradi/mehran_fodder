<?php

namespace App\Http\Requests\Payment;

use App\Payment;
use App\Rules\DiscountRule;
use App\Rules\PaymentRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'payed_amount' => ['nullable', new PaymentRule()],
            'method' => 'nullable|in:cheque,card_to_card,payment_gateway,other_method',
            'status' => 'nullable|in:0,1',
            'descriptions' => 'nullable|string|max:1000',
        ];
    }
}
