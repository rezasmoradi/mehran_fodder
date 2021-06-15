<?php

namespace App\Http\Requests\Ticket;

use App\Rules\OwnSameTicket;
use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
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
            'request_text' => ['required', 'string', 'min:10', 'max:250', new OwnSameTicket()],
        ];
    }
}
