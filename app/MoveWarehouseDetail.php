<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoveWarehouseDetail extends Model
{
    protected $table = 'pindah_gudang_detail';
    protected $primaryKey = 'id_pindah_gudang_detail';
    public $timestamps = false;
}
