<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    
    protected $fillable = ['userlevel_alias', 'name', 'email', 'company_type', 'website', 'logo', 'employee_count', 'address', 'city', 'state_id', 'country_id', 'pincode', 'phone1', 'phone2', 'description', 'founded_year', 'expiry_date', 'status', 
    'inserted_by_id', 'updated_by_id'];


}
