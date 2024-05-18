<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductionDetail extends Model
{
    protected $table = 'produksi_detail';
    protected $primaryKey = 'id_produksi_detail';
    public $timestamps = false;
}
