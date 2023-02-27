<?php

namespace App;

use App\Models\Master\Cabang;
use App\Models\Master\MataUang;
use App\Models\Master\Slip;
use DB;
use Illuminate\Database\Eloquent\Model;

class PurchaseDownPayment extends Model
{
    protected $table = 'uang_muka_pembelian';
    protected $primaryKey = 'id_uang_muka_pembelian';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    protected $fillable = [
        'id_cabang', 'kode_uang_muka_pembelian', 'tanggal', 'id_permintaan_pembelian', 'id_mata_uang', 'rate', 'nominal', 'total', 'catatan', 'id_slip', 'void', 'void_user_id', 'user_created', 'dt_created', 'user_modified', 'dt_modified',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    public static function createcode($id_cabang)
    {
        $branchCode = DB::table('cabang')->where('id_cabang', $id_cabang)->first();
        $string = 'UMB.' . $branchCode->kode_cabang . '.' . date('ym');
        $check = DB::table('uang_muka_pembelian')->where('kode_uang_muka_pembelian', 'like', $string . '%')->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (4 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        return $string . '.' . $nol . $check;
    }

    public function slip()
    {
        return $this->belongsTo(Slip::class, 'id_slip');
    }

    public function mataUang()
    {
        return $this->belongsTo(MataUang::class, 'id_mata_uang');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'id_permintaan_pembelian');
    }
}
