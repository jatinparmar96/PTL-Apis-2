<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserlevelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'alias' => 'required',
            'is_active' => 'required'
        ];
    }
}
