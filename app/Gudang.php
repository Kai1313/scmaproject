<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    protected $table = 'gudang';
    protected $primaryKey = 'id_gudang';
    public $timestamps = false;

    protected $fillable = [
        'id_cabang', 'id_perkiraan', 'kode_gudang', 'nama_gudang', 'nama_badan_gudang', 'alamat_gudang', 'kota_gudang', 'telepon1_gudang', 'telepon2_gudang', 'gambar_gudang', 'keterangan_gudang', 'status_gudang', 'user_gudang', 'date_gudang',
    ];
}
