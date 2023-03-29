<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'permintaan_pembelian';
    protected $primaryKey = 'id_permintaan_pembelian';
}
