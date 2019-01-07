<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'email' => 'required|email|unique',
            'status' => 'required',
            'expiry_date' => 'date',
            'logo' => 'image',
            'phone1' => 'numeric',
            'phone2' => 'numeric',
            'state_id' => 'nullable|numeric',
            'country_id' => 'nullable|numeric'
        ];
    }
}
