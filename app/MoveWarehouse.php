<?php

namespace App;

use App\MasterQrCode;
use App\Models\Master\Cabang;
use DB;
use Illuminate\Database\Eloquent\Model;

class MoveWarehouse extends Model
{
    protected $table = 'pindah_gudang';
    protected $primaryKey = 'id_pindah_gudang';
    public $timestamps = false;

    protected $fillable = [
        'id_pindah_gudang', 'id_pindah_gudang2', 'type', 'id_cabang', 'id_gudang', 'tanggal_pindah_gudang', 'nama_pindah_gudang', 'kode_pindah_gudang', 'id_cabang_tujuan', 'tujuan_pindah_gudang', 'nomot_polisi', 'transporter', 'dokumen_pindah_gudang', 'keterangan_pindah_gudang', 'status_pindah_gudang', 'user_pindah_gudang', 'date_pindah_gudang',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }

    public function destinationBranch()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang_tujuan');
    }

    public function details()
    {
        return $this->hasMany(MoveWarehouseDetail::class, 'id_pindah_gudang');
    }

    public function formatdetail()
    {
        return $this->hasMany(MoveWarehouseDetail::class, 'id_pindah_gudang')
            ->select(
                'be_pindah_gudang_detail',
                'bentuk_pindah_gudang_detail',
                'pindah_gudang_detail.id_barang',
                'id_pindah_gudang_detail',
                'pindah_gudang_detail.id_satuan_barang',
                'jumlah_pindah_gudang_detail',
                'keterangan_pindah_gudang_detail',
                'kode_batang_lama_pindah_gudang_detail',
                'kode_batang_pindah_gudang_detail',
                'nama_barang',
                'nama_satuan_barang',
                'ph_pindah_gudang_detail',
                'sg_pindah_gudang_detail',
                'warna_pindah_gudang_detail'
            )
            ->leftJoin('barang', 'pindah_gudang_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pindah_gudang_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang');
    }

    public static function createcode($id_cabang)
    {
        $branchCode = DB::table('cabang')->where('id_cabang', $id_cabang)->first();
        $string = 'PG.' . $branchCode->kode_cabang . '.' . date('ym');
        $check = DB::table('pindah_gudang')->where('kode_pindah_gudang', 'like', $string . '%')->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (4 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        return $string . '.' . $nol . $check;
    }

    public function savedetails($details)
    {
        $detail = json_decode($details);
        $ids = array_column($detail, 'id_pindah_gudang_detail');
        dd($ids);
        foreach ($ids as $detailId) {
            $trash = MoveWarehouseDetail::find($detailId);
            // if ($trash) {
            $trashQrCode = MasterQrCode::where('kode_batang_master_qr_code', $trash->kode_batang_pindah_gudang_detail)->first();
            $trashQrCode->sisa_master_qr_code = $trashQrCode->jumlah_master_qr_code;
            $trashQrCode->save();

            $trash->delete();
            // }
        }

        foreach ($detail as $data) {
            $check = DB::table('pindah_gudang_detail')
                ->where('id_pindah_gudang_detail', $data->id_pindah_gudang_detail)->first();
            if (!$check) {
                DB::table('pindah_gudang_detail')->insert([
                    'id_pindah_gudang' => $this->id_pindah_gudang,
                    'id_barang' => $data->id_barang,
                    'id_satuan_barang' => $data->id_satuan_barang,
                    'jumlah_pindah_gudang_detail' => normalizeNumber($data->jumlah_pindah_gudang_detail),
                    'mtotal_pindah_gudang_detail' => 0,
                    'kode_batang_pindah_gudang_detail' => $data->kode_batang_pindah_gudang_detail,
                    'kode_batang_lama_pindah_gudang_detail' => $data->kode_batang_lama_pindah_gudang_detail,
                    'sg_pindah_gudang_detail' => $data->sg_pindah_gudang_detail,
                    'be_pindah_gudang_detail' => $data->be_pindah_gudang_detail,
                    'ph_pindah_gudang_detail' => $data->ph_pindah_gudang_detail,
                    'bentuk_pindah_gudang_detail' => $data->bentuk_pindah_gudang_detail,
                    'warna_pindah_gudang_detail' => $data->warna_pindah_gudang_detail,
                    'id_perkiraan' => 1,
                    'status_pindah_gudang_detail' => 1,
                ]);

                $master = MasterQrCode::where('kode_batang_master_qr_code', $data->kode_batang_lama_pindah_gudang_detail)->first();
                if ($master) {
                    $master->sisa_master_qr_code = 0;
                    $master->save();
                }

            }
        }

        return ['status' => 'success'];
    }
}
