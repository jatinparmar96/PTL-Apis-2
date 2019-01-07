<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['option', 'detail', 'value', 'inserted_by_id', 'updated_by_id'];
}
