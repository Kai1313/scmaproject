<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AksesMenu extends Model
{
    protected $table = 'akses_menu';

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu', 'id_menu');
    }
}
