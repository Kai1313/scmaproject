<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class StokMinimalHitung extends Model
{
    protected $table = 'stok_minimal_hitung';
    protected $primaryKey = ['id', 'bulan', 'tahun', 'id_barang', 'id_cabang'];
}
