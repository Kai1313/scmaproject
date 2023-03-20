<?php

namespace App;

use DB;
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
        return $this->belongsTo(Satuan::class, 'id_satuan_barang');
    }

    public function updatePembelianDetail()
    {
        $array = [
            'sg_pembelian_detail' => $this->sg_pembelian_detail,
            'be_pembelian_detail' => $this->be_pembelian_detail,
            'ph_pembelian_detail' => $this->ph_pembelian_detail,
            'warna_pembelian_detail' => $this->warna_pembelian_detail,
            'keterangan_pembelian_detail' => $this->keterangan_pembelian_detail,
        ];

        DB::table('pembelian_detail')->where('id_pembelian', $this->id_pembelian)
            ->where('id_barang', $this->id_barang)->update($array);

    }
}
