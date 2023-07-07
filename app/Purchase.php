<?php

namespace App;

use App\Models\Master\Cabang;
use App\Models\Master\Pemasok;
use DB;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';
    public $timestamps = false;

    protected $fillable = [
        'id_pcabang',
        'id_gudang',
        'tanggal_pembelian',
        'nama_pembelian',
        'id_pemasok',
        'id_jenis_pembayaran',
        'nomor_po_pembelian',
        'nomor_npd_pembelian',
        'tempo_hari_pembelian',
        'dokumen_pembelian',
        'ppn_pembelian',
        'id_mata_uang',
        'kurs_pembelian',
        'mdiskon_pembelian',
        'mdpp_pembelian',
        'mtotal_pembelian',
        'keterangan_pembelian',
        'status_pembelian',
        'status_pembelian',
        'user_pembelian',
        'date_pembelian',
        'id_slip',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }

    public function pemasok()
    {
        return $this->belongsTo(Pemasok::class, 'id_pemasok');
    }

    public function qc()
    {
        return $this->hasMany(QualityControl::class, 'id_pembelian')
            ->select(
                'jumlah_pembelian_detail',
                'qc.id_barang',
                'id',
                'barang.nama_barang',
                'qc.id_satuan_barang',
                'nama_satuan_barang',
                'tanggal_qc',
                'status_qc',
                'reason',
                'sg_pembelian_detail',
                'be_pembelian_detail',
                'ph_pembelian_detail',
                'warna_pembelian_detail',
                'keterangan_pembelian_detail',
                'bentuk_pembelian_detail'
            )
            ->leftJoin('barang', 'qc.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'qc.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang');
    }

    public function detailgroup()
    {
        return $this->hasMany(PurchaseDetail::class, 'id_pembelian')
            ->select(
                DB::raw('sum(pembelian_detail.nett) as jumlah_pembelian_detail'),
                'pembelian_detail.id_barang as id',
                'barang.nama_barang as text',
                'pembelian_detail.id_satuan_barang',
                'nama_satuan_barang',
                'start_range_sg',
                'final_range_sg',
                'start_range_be',
                'final_range_be',
                'start_range_ph',
                'final_range_ph',
                'id_kategori_barang',
                'warna_qc_barang',
                'bentuk_qc_barang'
            )
            ->leftJoin('barang', 'pembelian_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pembelian_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
            ->leftJoin('qc', function ($qc) {
                $qc->on('pembelian_detail.id_pembelian', '=', 'qc.id_pembelian');
                $qc->on('pembelian_detail.id_barang', '=', 'qc.id_barang');
            })
            ->where('qc.id', null)
            ->groupBy('pembelian_detail.id_barang');
    }
}
