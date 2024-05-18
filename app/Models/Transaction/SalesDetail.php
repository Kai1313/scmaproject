<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class SalesDetail extends Model
{
    protected $table = 'penjualan_detail';
    protected $primaryKey = 'id_penjualan_detail';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
