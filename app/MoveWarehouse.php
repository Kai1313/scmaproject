<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoveWarehouse extends Model
{
    protected $table = 'pindah_gudang';
    protected $primaryKey = 'id_pindah_gudang';
    public $timestamps = false;

    protected $fillable = [
        'id_pindah_gudang', 'id_pindah_gudang2', 'type', 'id_cabang', 'id_gudang', 'tanggal_pindah_gudang', 'nama_pindah_gudang', 'kode_pindah_gudang', 'id_cabang_tujuan', 'tujuan_pindah_gudang', 'nomot_polisi', 'transporter', 'dokumen_pindah_gudang', 'keterangan_pindah_gudang', 'status_pindah_gudang', 'user_pindah_gudang', 'date_pindah_gudang',
    ];

    public function details()
    {
        return $this->hasMany(MoveWarehouse::class, 'id_pindah_gudang_detail');
    }
}
