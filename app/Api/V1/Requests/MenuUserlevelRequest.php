<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuUserlevelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'userlevel_id' => 'required',
            'menu_id' => 'required',
            'is_active' => 'required',
            'list' => 'required',
            'create' => 'required',
            'update' => 'required',
            'delete' => 'required'
        ];
    }
}
