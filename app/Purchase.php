<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';
    public $timestamps = false;

    public function qc()
    {
        return $this->hasMany(QualityControl::class, 'id_pembelian')
            ->select('jumlah_pembelian_detail', 'qc.id_barang', 'id', 'barang.nama_barang', 'qc.id_satuan_barang', 'nama_satuan_barang', 'tanggal_qc', 'status_qc', 'reason', 'sg_pembelian_detail', 'be_pembelian_detail', 'ph_pembelian_detail', 'warna_pembelian_detail', 'keterangan_pembelian_detail', 'bentuk_pembelian_detail')
            ->leftJoin('barang', 'qc.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'qc.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang');
    }

    public function detailgroup()
    {
        return $this->hasMany(PurchaseDetail::class, 'id_pembelian')
            ->select(DB::raw('sum(pembelian_detail.jumlah_pembelian_detail) as jumlah_pembelian_detail'), 'pembelian_detail.id_barang as id', 'barang.nama_barang as text', 'pembelian_detail.id_satuan_barang', 'nama_satuan_barang')
            ->leftJoin('barang', 'pembelian_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pembelian_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
            ->leftJoin('qc', function ($qc) {
                $qc->on('pembelian_detail.id_pembelian', '=', 'qc.id_pembelian');
                $qc->on('pembelian_detail.id_barang', '=', 'qc.id_barang');
            })
            ->where('qc.id', null)
            ->groupBy('pembelian_detail.id_barang');
    }
}
