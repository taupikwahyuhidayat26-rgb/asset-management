<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuGroup extends Model
{
    protected $fillable = ['name', 'sort_order'];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'menu_group_id')->orderBy('sort_order');
    }
}