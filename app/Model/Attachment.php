<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['module', 'module_id', 'attachment', 'comments', 'inserted_by_id', 'updated_by_id'];
}
