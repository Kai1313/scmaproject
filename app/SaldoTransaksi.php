<?php

namespace App;

use App\Models\Accounting\JurnalDetail;
use App\Models\Master\Cabang;
use App\Models\Master\Pelanggan;
use App\Penjualan;
use Illuminate\Database\Eloquent\Model;

class SaldoTransaksi extends Model
{
    protected $table = 'saldo_transaksi';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'tipe_transaksi', 'id_transaksi', 'id_cabang', 'tanggal', 'ref_id', 'catatan', 'id_pelanggan', 'id_pemasok', 'dpp', 'ppn', 'uang_muka', 'biaya', 'total', 'discount', 'bayar', 'sisa', 'id_jurnal', 'no_giro', 'tanggal_giro', 'tanggal_giro_jt', 'id_slip2', 'status_giro', 'tipe_pembayaran',
    ];

    protected $appends = ['aging'];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang', 'id_cabang');
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_transaksi', 'nama_penjualan');
    }

    public function jurnalDetail()
    {
        return $this->belongsTo(JurnalDetail::class, 'id_transaksi', 'id_transaksi');
    }

    public function getAgingAttribute()
    {

        $tgl1 = strtotime($this->tanggal);
        $tgl2 = strtotime(date('Y-m-d'));

        $jarak = $tgl2 - $tgl1;

        $hari = $jarak / 60 / 60 / 24;

        return $hari;
    }
}
