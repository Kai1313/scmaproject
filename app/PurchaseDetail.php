<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    protected $table = 'pembelian_detail';
    protected $primaryKey = 'id_pembelian_detail';
    public $timestamps = false;

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'id_pembelian');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function satuan()
    {
        return $this->belongsTo(SatuanBarang::class, 'id_satuan_barang');
    }
}
