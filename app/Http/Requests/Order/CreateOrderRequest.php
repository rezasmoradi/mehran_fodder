<?php

namespace App\Http\Requests\Order;

use App\Order;
use App\Rules\MobileRule;
use App\Rules\OwnUserRule;
use App\Rules\PaymentRule;
use App\Rules\PrepaymentRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create', Order::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => ['required', new OwnUserRule($this->post('type'))],
            'product_id' => 'exists:products,id',
            'total_amount' => 'required|integer',
            'pre_payment' => ['required', new PaymentRule(), new PrepaymentRule($this->input('product_id'), $this->input('total_amount'))],
            'order_type' => 'exists:orders',
            'descriptions' => 'nullable|string|max:1000',
            'mobile' => ['nullable', new MobileRule()],
            'payment_method' => 'nullable|exists:payments,method',
            'ref_id' => 'nullable|string|min:8|max:16',
        ];
    }
}
