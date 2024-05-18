<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'setting';
    // protected $primaryKey = 'code';
    public $timestamps = false;

    protected $fillable = [
        "id_cabang",
        "code",
        "description",
        "tipe",
        "value1",
        "value2",
        "user_created",
        "dt_created",
        "user_modified",
        "dt_modified",
    ];
}
