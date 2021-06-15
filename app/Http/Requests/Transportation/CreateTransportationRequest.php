<?php

namespace App\Http\Requests\Transportation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CreateTransportationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create-transportation');
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
            'license_plate' => 'required|string|min:9|max:9',
            'vehicle_name' => 'required|string|max:45',
            'delivery_amount' => 'required|integer',
            'delivery_at' => 'nullable|date_format:Y-m-d H:i',
            'delivery_status' => 'nullable|in:0,1',
            'descriptions' => 'nullable|string|max:1000',
        ];
    }
}
