<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QualityControl extends Model
{
    protected $table = 'qc';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_cabang', 'id_pembelian', 'id_barang', 'id_satuan_barang', 'jumlah_pembelian_detail', 'tanggal_qc', 'status_qc', 'reeason', 'sg_pembelian_detail', 'be_pembelian_detail', 'ph_pembelian_detail', 'warna_pembelian_detail', 'keterangan_pembelian_detail',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }

    public function purchasing()
    {
        return Purchasing::find($this->id_pembelian);
    }
}
