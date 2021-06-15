<?php

namespace App\Http\Requests\User;

use App\Rules\MobileRule;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;

class UnregisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->route('id') ? $this->user()->isAdmin() : $this->user()->id === $this->route('id');
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
