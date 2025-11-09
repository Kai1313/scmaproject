<?php
namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table      = 'barang';
    protected $primaryKey = 'id_barang';
    public $timestamps    = false;

    public function units()
    {
        $isiSatuan = DB::table('isi_satuan_barang')->select('isi_satuan_barang.id_satuan_barang as id', 'satuan_barang.nama_satuan_barang as text', 'id_barang')
            ->join('satuan_barang', 'isi_satuan_barang.id_satuan_barang', 'satuan_barang.id_satuan_barang')
            ->where('id_barang', $this->id_barang)->get();
        return $isiSatuan;
    }
}
