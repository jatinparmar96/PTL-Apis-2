<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'family_code' => 'required',
            'family_id' => 'required|numeric',
            'name' => 'required',
            'mobile' => 'numeric',
            'landline' => 'numeric',
            'dobr' => 'date',
            'doba' => 'date',
            'email_id' => 'email',
        ];
    }
}
