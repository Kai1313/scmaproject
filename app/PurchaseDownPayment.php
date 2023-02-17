<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseDownPayment extends Model
{
    protected $table = 'uang_muka_pembelian';
    protected $primaryKey = 'id_uang_muka_pembelian';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    protected $fillable = [
        'id_cabang', 'kode_uang_muka_pembelian', 'tanggal', 'id_permintaan_pembeliaan', 'id_mata_uang', 'rate', 'nominal', 'total', 'catatan', 'id_slip', 'void', 'void_user_id', 'user_created', 'dt_created', 'user_modified', 'dt_modified',
    ];
}
