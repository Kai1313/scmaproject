<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class ProductionCost extends Model
{
    protected $table = 'beban_produksi';
    protected $primaryKey = 'id_beban_produksi';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
