<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class GeneralLedger extends Model
{
    protected $table = 'jurnal_umum';
    protected $primaryKey = 'id_jurnal_umum';
}
