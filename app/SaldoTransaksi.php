<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaldoTransaksi extends Model
{
    protected $table = 'saldo_transaksi';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
