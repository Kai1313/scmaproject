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

    public function savedetails($details)
    {
        $idJenisTransaksi = 25;
        $detail = json_decode($details);
        $ids = array_column($detail, 'index');
        $selectTrash = MaterialUsageDetail::where('id_pemakaian', $this->id_pemakaian)
            ->whereNotIn('index', $ids)
            ->get();
        foreach ($selectTrash as $trash) {
            $trashQrCode = MasterQrCode::where('kode_batang_master_qr_code', $trash->kode_batang)->first();
            if ($trashQrCode) {
                $trashQrCode->sisa_master_qr_code = $trashQrCode->sisa_master_qr_code + $trash->jumlah;
                $trashQrCode->weight_zak = $trashQrCode->weight_zak + $trash->weight_zak;
                $trashQrCode->zak = $trashQrCode->zak + $trash->jumlah_zak;
                $trashQrCode->save();
            }

            $kartuStok = KartuStok::where('id_jenis_transaksi', $idJenisTransaksi)
                ->where('kode_batang_kartu_stok', $trash->kode_batang)
                ->where('kode_kartu_stok', $this->kode_pemakaian)
                ->delete();

            $trash->delete();
        }

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
                ];
                $store = new MaterialUsageDetail;
                $store->fill($array);
                $store->save();

                $master = MasterQrCode::where('kode_batang_master_qr_code', $store->kode_batang)->first();
                if ($master) {
                    $master->sisa_master_qr_code = $master->sisa_master_qr_code - $store->jumlah;
                    $master->zak = $master->zak - $store->jumlah_zak;
                    $master->weight_zak = $master->weight_zak - $store->weight_zak;
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
                    'tanggal_kartu_stok' => date('Y-m-d'),
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
                    'keterangan_kartu_stok' => $master->keterangan_master_qr_code,
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
}
