<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MaterialUsageDetail extends Model
{
    protected $table = 'pemakaian_detail';
    public $timestamps = false;

    protected $fillable = [
        'id_pemakaian',
        'index',
        'kode_batang',
        'id_barang',
        'id_satuan_barang',
        'jumlah',
        'weight',
        'jumlah_zak',
        'weight_zak',
        'catatan',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function satuan()
    {
        return $this->belongsTo(SatuanBarang::class, 'id_satuan_barang');
    }

    public function parent()
    {
        return $this->belongsTo(MaterialUsage::class, 'id_pemakaian');
    }
}
