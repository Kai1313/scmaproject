<?php

namespace App;

use App\Models\Master\Cabang;
use DB;
use Illuminate\Database\Eloquent\Model;

class MaterialUsage extends Model
{
    protected $table = 'pemakaian_header';
    protected $primaryKey = 'id_pemakaian';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    protected $fillable = [
        'tanggal',
        'kode_pemakaian',
        'id_cabang',
        'id_gudang',
        'catatan',
        'user_created',
        'dt_created',
        'user_modified',
        'dt_modified',
        'is_qc',
        'void',
        'void_user_ids',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }

    public function details()
    {
        return $this->hasMany(MaterialUsageDetail::class, 'id_pemakaian');
    }

    public function formatdetail()
    {
        return $this->hasMany(MaterialUsageDetail::class, 'id_pemakaian')
            ->select(
                'index',
                'id_pemakaian',
                'kode_batang',
                'pemakaian_detail.id_barang',
                'nama_barang',
                'pemakaian_detail.id_satuan_barang',
                'nama_satuan_barang',
                'jumlah',
                'weight',
                'weight_zak as tare',
                'jumlah_zak',
                'catatan',
                DB::raw('jumlah - weight_zak as nett')
            )
            ->leftJoin('barang', 'pemakaian_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pemakaian_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang');
    }

    public static function createcode($id_cabang)
    {
        $branchCode = DB::table('cabang')->where('id_cabang', $id_cabang)->first();
        $string = 'PM.' . $branchCode->kode_cabang . '.' . date('ym');
        $check = DB::table('pemakaian_header')->where('kode_pemakaian', 'like', $string . '%')->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (4 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        return $string . '.' . $nol . $check;
    }

    public function checkStockDetails($details)
    {
        $arrayQrCode = [];
        $arrayStock = [];
        $detail = json_decode($details);

        foreach ($detail as $data) {
            if (!isset($data->id_pemakaian)) {
                if (isset($arrayStock[$data->kode_batang])) {
                    $arrayStock[$data->kode_batang] = $arrayStock[$data->kode_batang] - $data->jumlah;
                } else {
                    $stock = MasterQrCode::where('kode_batang_master_qr_code', $data->kode_batang)->value('sisa_master_qr_code');
                    $arrayStock[$data->kode_batang] = $stock - $data->jumlah;
                }
            }
        }

        $message = '';
        foreach ($arrayStock as $key => $res) {
            if ($res < 0) {
                $message .= $key . ', ';
            }
        }

        if ($message != '') {
            return [
                'result' => false,
                'message' => 'QR Code ' . $message . ' stok tidak mencukupi',
            ];
        }

        return ['result' => true];
    }

    public function savedetails($details)
    {
        $idJenisTransaksi = 25;
        $detail = json_decode($details);

        foreach ($detail as $data) {
            $check = MaterialUsageDetail::where('id_pemakaian', $this->id_pemakaian)->where('index', $data->index)->first();
            if (!$check) {
                $array = [
                    'id_pemakaian' => $this->id_pemakaian,
                    'id_barang' => $data->id_barang,
                    'id_satuan_barang' => $data->id_satuan_barang,
                    'jumlah' => $data->jumlah,
                    'kode_batang' => $data->kode_batang,
                    'index' => $data->index,
                    'weight' => 0,
                    'jumlah_zak' => $data->jumlah_zak,
                    'weight_zak' => $data->weight_zak,
                    'catatan' => $data->catatan,
                ];
                $store = new MaterialUsageDetail;
                $store->fill($array);
                $store->save();

                $master = MasterQrCode::where('kode_batang_master_qr_code', $store->kode_batang)->first();
                if ($master) {
                    $master->sisa_master_qr_code = $master->sisa_master_qr_code - $store->jumlah;
                    $master->zak = ($master->zak ? $master->zak : 0) - $store->jumlah_zak;
                    $master->weight_zak = ($master->weight_zak ? $master->weight_zak : 0) - $store->weight_zak;
                    $master->save();
                }

                DB::table('kartu_stok')->insert([
                    'id_gudang' => $this->id_gudang,
                    'id_jenis_transaksi' => $idJenisTransaksi,
                    'kode_kartu_stok' => $this->kode_pemakaian,
                    'id_barang' => $store->id_barang,
                    'id_satuan_barang' => $store->id_satuan_barang,
                    'nama_kartu_stok' => $this->id_pemakaian,
                    'nomor_kartu_stok' => $store->index,
                    'tanggal_kartu_stok' => $this->tanggal,
                    'debit_kartu_stok' => 0,
                    'kredit_kartu_stok' => $store->jumlah,
                    'tanggal_kadaluarsa_kartu_stok' => $master->tanggal_expired_master_qr_code,
                    'mtotal_debit_kartu_stok' => 0,
                    'mtotal_kredit_kartu_stok' => 0,
                    'kode_batang_kartu_stok' => $store->kode_batang,
                    'kode_batang_lama_kartu_stok' => '',
                    'rak_kartu_stok' => '',
                    'batch_kartu_stok' => $master->batch_master_qr_code,
                    'id_perkiraan' => 34,
                    'sg_kartu_stok' => $master->sg_master_qr_code,
                    'be_kartu_stok' => $master->be_master_qr_code,
                    'ph_kartu_stok' => $master->ph_master_qr_code,
                    'warna_kartu_stok' => $master->warna_master_qr_code,
                    'keterangan_kartu_stok' => $this->catatan . ', ' . $data->catatan,
                    'status_kartu_stok' => 1,
                    'user_kartu_stok' => session()->get('user')['id_pengguna'],
                    'date_kartu_stok' => date('Y-m-d H:i:s'),
                    'zak' => $store->jumlah_zak,
                    'id_wrapper_zak' => $master->id_wrapper_zak,
                    'weight_zak' => $store->weight_zak,
                ]);
            }
        }

        return ['status' => 'success'];
    }

    public function voidDetails()
    {
        foreach ($this->details as $detail) {
            $master = MasterQrCode::where('kode_batang_master_qr_code', $detail->kode_batang)->first();
            if ($master) {
                $master->sisa_master_qr_code = $master->sisa_master_qr_code + $detail->jumlah;
                $master->zak = ($master->zak ? $master->zak : 0) + $detail->jumlah_zak;
                $master->weight_zak = ($master->weight_zak ? $master->weight_zak : 0) + $detail->weight_zak;
                $master->save();
            }

            DB::table('kartu_stok')->where('kode_kartu_stok', $this->kode_pemakaian)
                ->where('kode_batang_kartu_stok', $detail->kode_batang)
                ->where('id_jenis_transaksi', $this->id_jenis_transaksi)->delete();
        }

        return ['status' => 'success'];
    }
}
