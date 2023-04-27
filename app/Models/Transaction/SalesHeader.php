<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class SalesHeader extends Model
{
    protected $table = 'penjualan';
    protected $primaryKey = 'id_penjualan';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
