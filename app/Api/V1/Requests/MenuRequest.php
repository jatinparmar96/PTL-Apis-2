<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'caption' => 'required',
            'link' => 'required'
        ];
    }
}
