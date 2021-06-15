<?php

namespace App\Http\Requests\Payment;

use App\Payment;
use App\Rules\DiscountRule;
use App\Rules\MobileRule;
use App\Rules\PaymentRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CreatePaymentRequest extends FormRequest
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
            'order_id' => 'required|exists:orders,id',
            'mobile' => ['nullable', new MobileRule()],
            'discount' => ['nullable', new DiscountRule()],
            'payed_amount' => ['required', new PaymentRule()],
            'method' => 'required|in:cheque,card_to_card,payment_gateway,other_method',
            'card_number' => 'nullable|string|min:16|max:16',
            'status' => 'in:0,1',
            'descriptions' => 'nullable|string|max:1000',
        ];
    }
}
