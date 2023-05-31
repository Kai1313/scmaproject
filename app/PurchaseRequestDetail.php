<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestDetail extends Model
{
    protected $table = 'purchase_request_detail';
    public $timestamps = false;

    protected $fillable = [
        'purchase_request_id', 'index', 'id_barang', 'id_satuan_barang', 'qty', 'notes', 'approval_notes', 'approval_status', 'approval_user_id', 'approval_date', 'closed',
    ];
    // protected $primaryKey = 'purchase_request_id';

    // const CREATED_AT = 'dt_created';
    // const UPDATED_AT = 'dt_modified';

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function satuan()
    {
        return $this->belongsTo(SatuanBarang::class, 'id_satuan_barang');
    }

    public function kartuStok()
    {
        return $this->belongsTo(KartuStok::class, 'id_barang')
            ->select(\DB::raw('sum(debit_kartu_stok) - sum(kredit_kartu_stok)'))
            ->groupBy('id_barang');
    }
}
