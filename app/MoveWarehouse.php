<?php

namespace App;

use App\MasterQrCode;
use App\Models\Master\Cabang;
use DB;
use Illuminate\Database\Eloquent\Model;

class MoveWarehouse extends Model
{
    protected $table = 'pindah_barang';
    protected $primaryKey = 'id_pindah_barang';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    protected $fillable = [
        'id_pindah_barang',
        'id_pindah_barang2',
        'type',
        'id_cabang',
        'id_gudang',
        'tanggal_pindah_barang',
        'kode_pindah_barang',
        'id_cabang_tujuan',
        'nomor_polisi',
        'transporter',
        'keterangan_pindah_barang',
        'status_pindah_barang',
        'user_created',
        'dt_created',
        'user_modified',
        'dt_modified',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }

    public function parent()
    {
        return $this->belongsTo(MoveWarehouse::class, 'id_pindah_barang2', 'id_pindah_barang');
    }

    public function destinationBranch()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang_tujuan');
    }

    public function details()
    {
        return $this->hasMany(MoveWarehouseDetail::class, 'id_pindah_barang');
    }

    public function formatdetail()
    {
        return $this->hasMany(MoveWarehouseDetail::class, 'id_pindah_barang')
            ->select(
                'be',
                'bentuk',
                'pindah_barang_detail.id_barang',
                'id_pindah_barang_detail',
                'pindah_barang_detail.id_satuan_barang',
                'qty',
                'keterangan',
                'qr_code',
                'nama_barang',
                'nama_satuan_barang',
                'ph',
                'sg',
                'warna',
                'status_diterima'
            )
            ->leftJoin('barang', 'pindah_barang_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pindah_barang_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang');
    }

    public static function createcode($id_cabang)
    {
        $branchCode = DB::table('cabang')->where('id_cabang', $id_cabang)->first();
        $string = 'PG.' . $branchCode->kode_cabang . '.' . date('ym');
        $check = DB::table('pindah_barang')->where('kode_pindah_barang', 'like', $string . '%')->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (4 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        return $string . '.' . $nol . $check;
    }

    public function savedetails($details, $type = 'in')
    {
        $detail = json_decode($details);
        $ids = array_column($detail, 'id_pindah_barang_detail');
        $selectTrash = MoveWarehouseDetail::whereNotIn('id_pindah_barang_detail', $ids)->get();
        foreach ($selectTrash as $trash) {
            $trashQrCode = MasterQrCode::where('kode_batang_master_qr_code', $trash->qr_code)->first();
            if ($type == 'out') {
                $trashQrCode->sisa_master_qr_code = $trashQrCode->jumlah_master_qr_code;
            } else {
                $trashQrCode->sisa_master_qr_code = 0;
            }

            $trashQrCode->save();
            $trash->delete();
        }

        foreach ($detail as $data) {
            $check = DB::table('pindah_barang_detail')
                ->where('id_pindah_barang_detail', $data->id_pindah_barang_detail)->first();
            if (!$check) {
                DB::table('pindah_barang_detail')->insert([
                    'id_pindah_barang' => $this->id_pindah_barang,
                    'id_barang' => $data->id_barang,
                    'id_satuan_barang' => $data->id_satuan_barang,
                    'qty' => normalizeNumber($data->qty),
                    'qr_code' => $data->qr_code,
                    'sg' => $data->sg,
                    'be' => $data->be,
                    'ph' => $data->ph,
                    'bentuk' => $data->bentuk,
                    'warna' => $data->warna,
                    'keterangan' => $data->keterangan,
                    'status_diterima' => isset($data->status_diterima) ? $data->status_diterima : 0,
                    'user_created' => session()->get('user')['id_pengguna'],
                    'dt_created' => date('Y-m-d H:i:s'),
                ]);

                $master = MasterQrCode::where('kode_batang_master_qr_code', $data->qr_code)->first();
                if ($master) {
                    if ($type == 'in' && array_key_exists('status_diterima', $data) && $data->status_diterima == '1') {
                        $master->sisa_master_qr_code = $master->jumlah_master_qr_code;
                    } else {
                        $master->sisa_master_qr_code = 0;
                    }

                    $master->save();
                }
            }
        }

        return ['status' => 'success'];
    }
}
