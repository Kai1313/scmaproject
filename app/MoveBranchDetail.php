<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoveBranchDetail extends Model
{
    protected $table = 'pindah_barang_detail';
    protected $primaryKey = 'id_pindah_barang_detail';
    public $timestamps = false;

    protected $fillable = [
        'id_pindah_barang_detail',
        'id_pindah_barang',
        'id_barang',
        'id_satuan_barang',
        'qty',
        'tanggal_kadaluarsa',
        'qr_code',
        'batch',
        'sg',
        'be',
        'ph',
        'warna',
        'bentuk',
        'keterangan',
        'status_diterima',
        'user_created',
        'dt_created',
    ];
}
