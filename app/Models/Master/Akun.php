<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    protected $table = 'master_akun';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
