<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = ['company_id', 'name', 'inserted_by_id', 'updated_by_id'];

    public function leaves()
    {
        return $this->hasMany('App\Leave');
    }
}
