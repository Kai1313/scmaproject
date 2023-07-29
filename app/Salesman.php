<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    protected $table = 'salesman';
    protected $primaryKey = 'id_pengguna';
    public $timestamps = false;

    protected $fillable = [
        'id_salesman',
        'kode_salesman',
        'nama_salesman',
        'alamat_salesman',
        'telepon_salesman',
        'keterangan_salesman',
        'status_salesman',
        'user_salesman',
        'date_salesman',
        'id_cabang',
    ];
}
