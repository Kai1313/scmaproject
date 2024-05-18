<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class StokMin extends Model
{
    public $timestamps = false;
    protected $table = 'stok_minimal_barang_gudang';
    protected $primaryKey = 'id_stok_minimal_barang_gudang';
    protected $fillable = [
        'date_stok_minimal_barang_gudang',
    ];
}
