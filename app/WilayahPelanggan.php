<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WilayahPelanggan extends Model
{
    protected $table = 'wilayah_pelanggan';
    protected $primaryKey = 'id_wilayah_pelanggan';
    public $timestamps = false;
}
