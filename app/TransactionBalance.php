<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionBalance extends Model
{
    protected $table = 'saldo_transaksi';
    public $timestamps = false;

    protected $fillable = [
        'id', 'tipe_transaksi', 'id_transaksi', 'tanggal', 'ref_id', 'catatan', 'id_pelanggan', 'id_pemasok', 'dpp', 'ppn', 'uang_muka', 'biaya', 'sisa', 'id_jurnal', 'no_giro', 'tanggal_giro', 'tanggal_giro_jt', 'status_giro', 'tipe_pembayaran', 'total', 'bayar', 'id_cabang', 'tanggal_jatuh_tempo',
    ];
}
