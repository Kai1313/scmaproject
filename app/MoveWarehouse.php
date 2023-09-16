<?php

namespace App;

use App\Models\Master\Cabang;
use Illuminate\Database\Eloquent\Model;

class MoveWarehouse extends Model
{
    protected $table = 'pindah_gudang';
    protected $primaryKey = 'id_pindah_gudang';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    protected $fillable = [
        'id_pindah_barang',
        'id_pindah_barang2',
        'type',
        'id_cabang',
        'id_gudang',
        'tanggal_pindah_gudang',
        'nama_pindah_gudang',
        'kode_pindah_gudang',
        'id_cabang_tujuan',
        'tujuan_pindah_gudang',
        'nomor_polisi',
        'transporter',
        'keterangan_pindah_gudang',
        'dokumen_pindah_gudang',
        'status_pindah_gudang',
        'user_pindah_gudang',
        'date_pindah_gudang',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }

    public function parent()
    {
        return $this->belongsTo(MoveWarehouse::class, 'id_pindah_gudang2', 'id_pindah_gudang');
    }

    public function formatdetail()
    {
        return $this->hasMany(MoveWarehouseDetail::class, 'id_pindah_gudang')
            ->select(
                'pindah_gudang_detail.id_barang',
                'id_pindah_gudang_detail',
                'pindah_gudang_detail.id_satuan_barang',
                'jumlah_pindah_gudang_detail',
                'kode_batang_pindah_gudang_detail',
                'kode_batang_lama_pindah_gudang_detail',
                'nama_barang',
                'nama_satuan_barang',
                'status_diterima',
                'batch_pindah_gudang_detail',
                'tanggal_kadaluarsa_pindah_gudang_detail'
            )
            ->leftJoin('barang', 'pindah_gudang_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pindah_gudang_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang');
    }
}
