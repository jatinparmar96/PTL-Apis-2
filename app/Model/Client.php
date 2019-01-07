<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['company_id', 'salutation', 'code', 'name', 'mobile', 'landline', 'dobr', 'email_id', 'gender', 'type', 'aadhar_no', 'pan_no', 'address1', 'address2', 'address3', 'city', 'pincode', 'comments', 'inserted_by_id', 'updated_by_id'];
}
