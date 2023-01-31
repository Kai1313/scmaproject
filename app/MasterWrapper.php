<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterWrapper extends Model
{
    protected $table = 'master_wrapper';
    protected $primaryKey = 'id_wrapper';
    public $timestamps = false;

    protected $fillable = [
        'id_cabang', 'nama_wrapper', 'weight', 'path', 'path2', 'catatan', 'user_created', 'dt_created', 'user_modified', 'dt_modified',
    ];
}
