<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    public static $suffixUrl = [
        '/index',
        '/create',
        '/show',
        '/form',
        '/list',
        '/print',
        '/edit',
    ];
}
