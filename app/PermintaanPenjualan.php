<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermintaanPenjualan extends Model
{
    protected $table = 'permintaan_penjualan';
    protected $primaryKey = 'id_permintaan_penjualan';
    public $timestamps = false;
}
