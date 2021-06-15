<?php

namespace App\Http\Requests\User;

use App\Rules\MobileRule;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|string|min:3|max:45',
            'last_name' => 'required|string|min:3|max:45',
            'username' => 'nullable|string|min:5|max:100',
            'password' => 'required|min:8|max:30',
            'type' => 'required|exists:users',
            'mobile' => ['required', new MobileRule()],
            'phone' => ['nullable', new PhoneRule()],
            'province' => 'required|string|min:2|max:45',
            'city' => 'required|string|min:2|max:45',
            'village' => 'nullable|string|min:3|max:45',
            'street' => 'nullable|string|min:3|max:45',
            'postal_code' => 'required|string|min:10|max:10',
        ];
    }
}
