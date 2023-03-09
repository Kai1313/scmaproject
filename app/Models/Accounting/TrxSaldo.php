<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class TrxSaldo extends Model
{
    protected $table = 'saldo_transaksi';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
