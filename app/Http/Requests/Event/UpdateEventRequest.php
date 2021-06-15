<?php

namespace App\Http\Requests\Event;

use App\Event;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->isAdmin() || Event::query()->find($this->route('id')) === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'users' => 'nullable|array',
            'event_title' => 'nullable|string|min:3|max:30',
            'event_content' => 'nullable|string|min:10|max:1000',
            'publish_date' => ['nullable', 'date_format:Y-m-d H:i', 'after_now'],
        ];
    }
}
