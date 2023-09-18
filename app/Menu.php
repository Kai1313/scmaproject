<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    public static $suffixUrl = [
        '/index',
        '/create',
        '/entry',
        '/show',
        '/form',
        '/list',
        '/print',
        '/edit',
    ];

    public function akses()
    {
        return $this->belongsTo(AksesMenu::class, 'id_menu', 'id_menu');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'kepala_menu', 'id_menu');
    }

    public function childs()
    {
        return $this->hasMany(Menu::class, 'kepala_menu', 'id_menu')->where('status_menu', 1)->orderBy('urut_menu', 'asc');
    }
}
