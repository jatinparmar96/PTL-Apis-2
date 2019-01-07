<?php

namespace App\Model;

use Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id', 'userlevel_id', 'department_id', 'is_active', 'name','display_name', 'email', 'password', 'email_id','mobile', 'mobile_2', 'landline', 'address', 'pincode', 'comments', 'inserted_by_id', 'updated_by_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Automatically creates hash for the user password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function getCompanies()
    {
        return $this->hasMany('App\Model\Company','user_id');
    }
}
