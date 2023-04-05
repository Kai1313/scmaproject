<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoveWarehouse extends Model
{
    protected $table = 'pindah_gudang';
    protected $primaryKey = 'id_pindah_gudang';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
