<?php

namespace App\Http\Requests\Product;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->isAdmin() || $this->user()->isUserType(User::TYPE_WAREHOUSE_KEEPER);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'stock' => 'nullable|integer',
            'unit_price' => 'nullable|integer',
            'discount' => 'nullable|integer',
            'packing_weight' => 'nullable|integer',
        ];
    }
}
