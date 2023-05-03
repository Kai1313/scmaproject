<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Closing extends Model
{
    protected $table = 'closing';
    protected $primaryKey = 'id_closing';
    const CREATED_AT = 'dt_record';
    const UPDATED_AT = 'dt_modified';
}
