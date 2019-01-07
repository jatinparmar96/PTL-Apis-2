<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ipaddress' => 'required',
            'user_id' => 'required',
            'module' => 'required',
            'task' => 'required',
            'note' => 'required',
            'logdate' => 'required'
        ];
    }
}
