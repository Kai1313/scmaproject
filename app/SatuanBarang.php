<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SatuanBarang extends Model
{
    protected $table = 'satuan_barang';
    protected $primaryKey = 'id_satuan_barang';
    public $timestamps = false;
}
