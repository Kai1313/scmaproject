<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestDetail extends Model
{
    protected $table = 'purchase_request_detail';
    public $timestamps = false;
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
}
