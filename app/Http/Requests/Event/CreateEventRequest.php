<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
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
            'users' => 'required|array',
            'event_title' => 'required|string|min:3|max:30',
            'event_content' => 'required|string|min:10|max:1000',
            'publish_date' => ['nullable', 'date_format:Y-m-d H:i', 'after:now']
        ];
    }
}
