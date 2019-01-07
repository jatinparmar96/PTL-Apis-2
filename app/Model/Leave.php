<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $fillable = ['user_id', 'application_date', 'leave_start_date', 'leave_end_date', 'leave_type_id', 'approve', 'approve_date', 'approve_by', 'comments', 'inserted_by_id', 'updated_by_id'];

    public function leave_types()
    {
        return $this->belongsTo('App\Model\LeaveType', 'leave_type_id');
    }
}
