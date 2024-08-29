<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalanDetail extends Model
{
    protected $table = "surat_jalan_detail";

    protected $fillable = [
        'id_surat_jalan', 'nama_barang', 'jumlah', 'satuan', 'keterangan',
    ];

    public function parent()
    {
        return $this->belongsTo(SuratJalan::class, 'id_surat_jalan', 'id');
    }
}
