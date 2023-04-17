<?php

namespace App;

use App\Models\Master\Cabang;
use Illuminate\Database\Eloquent\Model;

class MasterQrCode extends Model
{
    protected $table = 'master_qr_code';
    protected $primaryKey = 'id_master_qr_code';
    public $timestamps = false;

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function satuan()
    {
        return $this->belongsTo(SatuanBarang::class, 'id_satuan_barang');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }
}
