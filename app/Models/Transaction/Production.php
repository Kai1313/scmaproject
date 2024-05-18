<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $table = 'produksi';
    protected $primaryKey = 'id_produksi';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
