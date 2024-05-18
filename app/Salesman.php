<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    protected $table = 'salesman';
    protected $primaryKey = 'id_salesman';
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
        'pengguna_id',
    ];


    public function visit()
    {
        return $this->hasMany(Visit::class, 'id_salesman', 'id_salesman');
    }
}
