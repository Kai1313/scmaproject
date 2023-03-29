<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'setting';
    protected $primaryKey = 'code';
    public $timestamps = false;
}
