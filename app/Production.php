<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $table = 'produksi';
    protected $primaryKey = 'id_produksi';
    public $timestamps = false;
}
