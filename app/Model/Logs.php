<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $fillable = ['ipaddress', 'user_id', 'module', 'task', 'note', 'logdate', 'old_data', 'new_data',  'inserted_by_id', 'updated_by_id'];

    // public function menu()
    // {
    //     return $this->hasMany('App\Logs','id');
    // }
}
