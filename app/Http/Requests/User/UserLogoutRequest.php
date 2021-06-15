<?php

namespace App\Http\Requests\User;

use App\Rules\MobileRule;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;

class UserLogoutRequest extends FormRequest
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
        return [];
    }
}