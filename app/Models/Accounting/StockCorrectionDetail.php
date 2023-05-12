<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class StockCorrectionDetail extends Model
{
    protected $table = 'koreksi_stok_detail';
    protected $primaryKey = 'id_koreksi_stok_detail';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
