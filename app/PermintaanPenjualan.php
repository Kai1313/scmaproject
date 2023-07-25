<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PermintaanPenjualan extends Model
{
    protected $table = 'permintaan_penjualan';
    protected $primaryKey = 'id_permintaan_penjualan';
    public $timestamps = false;


    function visit(): HasOne
    {
        return $this->hasOne(Visit::class, 'permintaan_penjualan_id', 'id_permintaan_penjualan');
    }
}
