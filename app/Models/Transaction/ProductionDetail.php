<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ProductionDetail extends Model
{
    protected $table = 'produksi_detail';
    protected $primaryKey = 'id_produksi_detail';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
