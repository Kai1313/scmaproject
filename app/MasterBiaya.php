<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterBiaya extends Model
{
    protected $table = 'master_biaya';
    protected $primaryKey = 'id_biaya';
    public $timestamps = false;

    protected $fillable = [
        'id_cabang', 'nama_biaya', 'id_akun_biaya', 'isppn', 'ispph', 'id_akun_pph', 'value_pph', 'aktif', 'user_created', 'dt_created', 'user_modified', 'dt_modified',
    ];
}
