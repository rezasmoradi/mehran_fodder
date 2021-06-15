<?php

namespace App\Http\Requests\User;

use App\Rules\MobileRule;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateUserRequest extends FormRequest
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
            'first_name' => 'nullable|string|min:3|max:45',
            'last_name' => 'nullable|string|min:3|max:45',
            'username' => 'nullable|string|min:5|max:100',
            'password' => 'nullable|min:8|max:30',
            'phone' => ['nullable', new PhoneRule()],
            'province' => 'nullable|string|min:2|max:45',
            'city' => 'nullable|string|min:2|max:45',
            'village' => 'nullable|string|min:3|max:45',
            'street' => 'nullable|string|min:3|max:45',
            'postal_code' => 'nullable|digits:10',
        ];
    }
}
