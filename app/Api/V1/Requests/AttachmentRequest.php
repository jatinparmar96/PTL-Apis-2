<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'module_id' => 'required',
            'attachment' => 'required'
        ];
    }
}
