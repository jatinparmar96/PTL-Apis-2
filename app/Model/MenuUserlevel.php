<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MenuUserlevel extends Model
{
    protected $fillable = ['userlevel_id', 'menu_id', 'is_active', 'list', 'create', 'update', 'delete',  'inserted_by_id', 'updated_by_id'];
}
