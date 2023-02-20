<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $table = 'purchase_request_header';
    protected $primaryKey = 'purchase_request_id';

    protected $fillable = [
        'id_cabang', 'purchase_request_code', 'purchase_request_date', 'id_gudang', 'purchase_request_estimation_date', 'purchase_request_user_id', 'user_created', 'user_modified', 'catatan', 'approval_status', 'approval_user_id', 'approval_date', 'void', 'void_user_id',
    ];

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'purchase_request_user_id');
    }

    public function details()
    {
        return $this->hasMany(PurchaseRequestDetail::class, 'purchase_request_id');
    }

    public function formatdetail()
    {
        return $this->hasMany(PurchaseRequestDetail::class, 'purchase_request_id')
            ->select('index', 'purchase_request_detail.id_barang', 'nama_barang', 'kode_barang', 'purchase_request_detail.id_satuan_barang', 'nama_satuan_barang', 'qty', 'notes')
            ->leftJoin('barang', 'purchase_request_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'purchase_request_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang');
    }

    public function savedetails($details)
    {
        $detail = json_decode($details);
        $ids = array_column($detail, 'index');

        DB::table('purchase_request_detail')
            ->where('purchase_request_id', $this->purchase_request_id)
            ->whereNotIn('index', $ids)->delete();

        foreach ($detail as $data) {
            $check = DB::table('purchase_request_detail')
                ->where('purchase_request_id', $this->purchase_request_id)
                ->where('index', $data->index)->first();
            if ($check) {
                DB::table('purchase_request_detail')
                    ->where('purchase_request_id', $this->purchase_request_id)
                    ->where('index', $data->index)
                    ->update([
                        // 'index' => $data->index,
                        'id_barang' => $data->id_barang,
                        'id_satuan_barang' => $data->id_satuan_barang,
                        'qty' => $data->qty,
                        'notes' => $data->notes,
                    ]);
            } else {
                DB::table('purchase_request_detail')->insert([
                    'index' => $data->index,
                    'id_barang' => $data->id_barang,
                    'id_satuan_barang' => $data->id_satuan_barang,
                    'qty' => $data->qty,
                    'notes' => $data->notes,
                    'purchase_request_id' => $this->purchase_request_id,
                    'closed' => '1',
                ]);
            }
        }

        return ['status' => 'success'];
    }

    public static function createcode($id_cabang)
    {
        $branchCode = DB::table('cabang')->where('id_cabang', $id_cabang)->first();
        $string = 'PR.' . $branchCode->kode_cabang . '.' . date('ym');
        $check = DB::table('purchase_request_header')->where('purchase_request_code', 'like', '%' . $string)->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (4 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        return $string . '.' . $nol . $check;
    }
}
