<?php

namespace App;

use App\Models\Master\Pemasok;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'permintaan_pembelian';
    protected $primaryKey = 'id_permintaan_pembelian';

    public function supplier()
    {
        return $this->belongsTo(Pemasok::class, 'id_pemasok');
    }
}
