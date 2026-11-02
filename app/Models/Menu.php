<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['menu_group_id', 'name', 'route', 'icon', 'sort_order'];

    public function group()
    {
        return $this->belongsTo(MenuGroup::class, 'menu_group_id');
    }
}