<?php

namespace App\Http\Requests\Transportation;

use App\Transportation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateTransportationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('update', Transportation::query()->find($this->route('id')));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_id' => 'nullable|exists:orders,id',
            'license_plate' => 'nullable|string|min:9|max:9',
            'vehicle_name' => 'nullable|string|min:3|max:45',
            'delivery_amount' => 'nullable|integer',
            'delivery_at' => 'nullable|date_format:Y-m-d H:i',
            'delivery_status' => 'nullable|in:0,1',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
