<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Slip extends Model
{
    protected $table = 'master_slip';
    protected $primaryKey = 'id_slip';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
