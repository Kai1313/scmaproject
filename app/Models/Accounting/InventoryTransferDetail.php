<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class InventoryTransferDetail extends Model
{
    protected $table = 'pindah_barang_detail';
    protected $primaryKey = 'id_pindah_barang_detail';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
