<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchasing extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';
    public $timestamps = false;

    public function qc()
    {
        return $this->hasMany(QualityControl::class, 'id_pembelian');
    }
}
