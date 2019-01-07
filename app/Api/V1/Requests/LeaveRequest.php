<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required',
            'application_date' => 'required|date',
            'leave_start_date' => 'required|date',
            'leave_end_date' => 'required|date',
            'leave_type_id' => 'required|date',
            'approve' => 'required',
        ];
    }
}
