<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class InventoryTransferHeader extends Model
{
    protected $table = 'pindah_barang';
    protected $primaryKey = 'id_pindah_barang';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
