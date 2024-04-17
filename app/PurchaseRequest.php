<?php

namespace App;

use App\Models\Master\Cabang;
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

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

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
        $arrayCabang = [
            '1' => [1],
            '2' => [5],
        ];

        $gudang = $arrayCabang[$this->id_cabang];
        return $this->hasMany(PurchaseRequestDetail::class, 'purchase_request_id')
            ->select(
                'purchase_request_id',
                'index as old_index',
                'index',
                'purchase_request_detail.id_barang',
                'nama_barang',
                'kode_barang',
                'purchase_request_detail.id_satuan_barang',
                'nama_satuan_barang',
                'qty',
                'notes',
                'approval_notes',
                'approval_status',
                'closed',
                DB::raw('(case when closed = 0 then "Open" else "Closed" end) as status_data'),
                DB::raw('(case
                    when sum(sisa_master_qr_code-weight-weight_zak) > 0 and barang.id_kategori_barang <> 7
                    then sum(sisa_master_qr_code-weight-weight_zak)
                    else 0
                end) as stok')
            )
            ->leftJoin('barang', 'purchase_request_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'purchase_request_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
            ->leftJoin('master_qr_code', function ($kartuStok) use ($gudang) {
                $kartuStok->on('purchase_request_detail.id_barang', '=', 'master_qr_code.id_barang')
                    ->whereIn('master_qr_code.id_gudang', $gudang);
            })->groupBy('id_barang', 'notes')->orderBy('index', 'asc');
    }

    public function savedetails($details)
    {
        $detail = json_decode($details);
        $array = [];
        foreach ($detail as $data) {
            if ($data->old_index != '') {
                $check = DB::table('purchase_request_detail')
                    ->where('purchase_request_id', $this->purchase_request_id)
                    ->where('index', $data->old_index)->first();
                if ($check) {
                    $check->index = $data->index;
                    $check->id_barang = $data->id_barang;
                    $check->id_satuan_barang = $data->id_satuan_barang;
                    $check->qty = $data->qty;
                    $check->notes = $data->notes;
                    $array[] = $check;
                }
            } else {
                $data->purchase_request_id = $this->purchase_request_id;
                $array[] = $data;
            }
        }

        DB::table('purchase_request_detail')->where('purchase_request_id', $this->purchase_request_id)->delete();
        foreach ($array as $a) {
            DB::table('purchase_request_detail')->insert([
                'purchase_request_id' => $a->purchase_request_id,
                'index' => $a->index,
                'id_barang' => $a->id_barang,
                'id_satuan_barang' => $a->id_satuan_barang,
                'qty' => $a->qty,
                'notes' => $a->notes,
                'approval_status' => isset($a->approval_status) ? $a->approval_status : 0,
                'approval_user_id' => isset($a->approval_user_id) ? $a->approval_user_id : null,
                'approval_date' => isset($a->approval_date) ? $a->approval_date : null,
                'closed' => $a->closed,
                'approval_notes' => isset($a->approval_notes) ? $a->approval_notes : null,
            ]);
        }

        return ['status' => 'success'];
    }

    public static function createcode($id_cabang)
    {
        $arrayMonth = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $startString = 'PR/SCMA' . ($id_cabang == 1 ? '-SBY' : '-JKT') . '/';
        $endString = '/' . ($arrayMonth[date('n')]) . '/' . date('Y');

        $check = \DB::table('purchase_request_header')->where('id_cabang', $id_cabang)->where('purchase_request_code', 'like', '%' . $endString . '%')->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (4 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        $string = $startString . $nol . $check . $endString;

        return $string;
        // $branchCode = DB::table('cabang')->where('id_cabang', $id_cabang)->first();
        // $string = 'PR.' . $branchCode->kode_cabang . '.' . date('ym');
        // $check = DB::table('purchase_request_header')->where('purchase_request_code', 'like', $string . '%')->count();
        // $check += 1;
        // $nol = '';
        // for ($i = 0; $i < (4 - strlen((string) $check)); $i++) {
        //     $nol .= '0';
        // }

        // return $string . '.' . $nol . $check;
    }

    public function saveStatusDetail()
    {
        $detail = DB::table('purchase_request_detail')
            ->where('purchase_request_id', $this->purchase_request_id)->get();

        foreach ($detail as $data) {
            $check = DB::table('purchase_request_detail')
                ->where('purchase_request_id', $this->purchase_request_id)
                ->where('index', $data->index)->first();
            if ($check) {
                DB::table('purchase_request_detail')
                    ->where('purchase_request_id', $this->purchase_request_id)
                    ->where('index', $data->index)
                    ->update([
                        'approval_status' => $this->approval_status,
                        'approval_user_id' => $this->approval_user_id,
                        'approval_date' => $this->approval_date,
                    ]);
            }
        }

        return ['status' => 'success'];
    }

    // public function medias()
    // {
    //     return $this->hasMany(Media::class, 'nama_media', 'id')
    //         ->select('id_media as id', 'lokasi_media as image')
    //         ->where('tipe_media', 'purchase_request')->where('nama_media');
    // }
}
