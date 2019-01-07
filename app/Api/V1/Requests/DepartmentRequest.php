<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'company_id' => 'required',
            'name' => 'required',
            'status' => 'required'
        ];
    }
}
