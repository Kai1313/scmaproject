<?php

namespace App;

use App\Models\Master\Pelanggan;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $table = 'permintaan_penjualan';
    protected $primaryKey = 'id_permintaan_penjualan';
    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
}
