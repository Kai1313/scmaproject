<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class StockCorrectionHeader extends Model
{
    protected $table = 'koreksi_stok';
    protected $primaryKey = 'id_koreksi_stok';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
