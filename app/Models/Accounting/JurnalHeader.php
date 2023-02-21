<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class JurnalHeader extends Model
{
    protected $table = 'jurnal_header';
    protected $primaryKey = 'id_jurnal';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
