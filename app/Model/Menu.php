<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['caption', 'level', 'class_name', 'link', 'parent', 'sort', 'inserted_by_id', 'updated_by_id'];

    public function menu()
    {
        return $this->hasMany('App\Menu','id');
    }
}
