<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'userlevel_id' => 'required',
            'department_id' => 'required',
            'is_active' => 'required',
            'fullname' => 'required',
            'username' => 'required'
        ];
    }
}
