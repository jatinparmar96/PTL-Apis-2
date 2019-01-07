<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
	public $timestamps = false;
    protected $fillable = ['user_id', 'ip_address', 'token', 'timestamp', 'one_signal_user_id', 'one_signal_token'];
}

?>