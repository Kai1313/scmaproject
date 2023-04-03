<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoveWarehouseDetail extends Model
{
    protected $table = 'pindah_barang_detail';
    protected $primaryKey = 'id_pindah_barang_detail';
    public $timestamps = false;
}
