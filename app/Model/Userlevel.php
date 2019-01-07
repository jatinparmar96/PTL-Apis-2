<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Userlevel extends Model
{
    protected $fillable = ['name', 'alias', 'is_active', 'inserted_by_id', 'updated_by_id'];
}
