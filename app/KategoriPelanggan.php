<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KategoriPelanggan extends Model
{
    protected $table = 'kategori_pelanggan';
    protected $primaryKey = 'id_kategori_pelanggan';
    public $timestamps = false;
}
