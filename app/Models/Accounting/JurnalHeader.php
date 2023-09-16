<?php

namespace App\Models\Accounting;

use App\SaldoTransaksi;
use Illuminate\Database\Eloquent\Model;

class JurnalHeader extends Model
{
    protected $table = 'jurnal_header';
    protected $primaryKey = 'id_jurnal';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    function jurnalDetails()
    {
        return $this->hasMany(JurnalDetail::class, 'id_jurnal', 'id_jurnal');
    }

    function saldo_transaksi()
    {
        return $this->hasMany(SaldoTransaksi::class, 'id_transaksi', 'id_transaksi');
    }
}
