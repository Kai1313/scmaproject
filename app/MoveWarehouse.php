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
        'void',
        'void_user_id',
        'id_cabang_asal',
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

    public function originBranch()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang_asal');
    }

    public function details()
    {
        return $this->hasMany(MoveWarehouseDetail::class, 'id_pindah_barang');
    }

    public function getDetailQRCode()
    {
        return $this->hasMany(MoveWarehouseDetail::class, 'id_pindah_barang')->select('qr_code');
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
                'status_diterima',
                'batch',
                'tanggal_kadaluarsa'
            )
            ->leftJoin('barang', 'pindah_barang_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pindah_barang_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang');
    }

    public static function createcode($id_cabang)
    {
        $branchCode = DB::table('cabang')->where('id_cabang', $id_cabang)->first();
        $string = 'TC.' . $branchCode->kode_cabang . '.' . date('ym');
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
        $idJenisTransaksi = ($type == 'out') ? '21' : '22';
        $detail = json_decode($details);
        $ids = array_column($detail, 'id_pindah_barang_detail');
        $selectTrash = MoveWarehouseDetail::where('id_pindah_barang', $this->id_pindah_barang)
            ->whereNotIn('id_pindah_barang_detail', $ids)
            ->get();
        foreach ($selectTrash as $trash) {
            $trashQrCode = MasterQrCode::where('kode_batang_master_qr_code', $trash->qr_code)->first();
            if ($trashQrCode) {
                if ($type == 'out') {
                    $trashQrCode->sisa_master_qr_code = $trashQrCode->jumlah_master_qr_code;
                } else {
                    $trashQrCode->sisa_master_qr_code = 0;
                }

                $trashQrCode->save();
            }

            $kartuStok = KartuStok::where('id_jenis_transaksi', $idJenisTransaksi)
                ->where('kode_batang_kartu_stok', $trash->qr_code)
                ->where('kode_kartu_stok', $this->kode_pindah_barang)
                ->delete();

            $trash->delete();
        }

        foreach ($detail as $data) {
            $check = MoveWarehouseDetail::where('id_pindah_barang_detail', $data->id_pindah_barang_detail)->first();
            if (!$check) {
                $array = [
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
                    'batch' => $data->batch,
                    'tanggal_kadaluarsa' => $data->tanggal_kadaluarsa,
                ];
                $store = new MoveWarehouseDetail;
                $store->fill($array);
                $store->save();

                $master = MasterQrCode::where('kode_batang_master_qr_code', $data->qr_code)->first();
                if ($master) {
                    if ($type == 'in' && $data->status_diterima == 1) {
                        $master->sisa_master_qr_code = $master->jumlah_master_qr_code;
                        $master->id_cabang = $this->id_cabang;
                        $master->id_gudang = $this->id_gudang;
                        $master->id_jenis_transaksi = $idJenisTransaksi;
                    } else {
                        $master->sisa_master_qr_code = 0;
                    }

                    $master->save();
                }

                DB::table('kartu_stok')->insert([
                    'id_gudang' => $this->id_gudang,
                    'id_jenis_transaksi' => $idJenisTransaksi,
                    'kode_kartu_stok' => $this->kode_pindah_barang,
                    'id_barang' => $data->id_barang,
                    'id_satuan_barang' => $data->id_satuan_barang,
                    'nama_kartu_stok' => $this->id_pindah_barang,
                    'nomor_kartu_stok' => $store->id_pindah_barang_detail,
                    'tanggal_kartu_stok' => date('Y-m-d'),
                    'debit_kartu_stok' => 0,
                    'kredit_kartu_stok' => ($type == 'in' && $data->status_diterima == 1) ? '-' . $store->qty : $store->qty,
                    'tanggal_kadaluarsa_kartu_stok' => $data->tanggal_kadaluarsa,
                    'mtotal_debit_kartu_stok' => 0,
                    'mtotal_kredit_kartu_stok' => 0,
                    'kode_batang_kartu_stok' => $data->qr_code,
                    'kode_batang_lama_kartu_stok' => $data->qr_code,
                    'rak_kartu_stok' => '',
                    'batch_kartu_stok' => $data->batch,
                    'id_perkiraan' => 34,
                    'sg_kartu_stok' => $data->sg,
                    'be_kartu_stok' => $data->be,
                    'ph_kartu_stok' => $data->ph,
                    'warna_kartu_stok' => $data->warna,
                    'keterangan_kartu_stok' => $data->keterangan,
                    'status_kartu_stok' => 1,
                    'user_kartu_stok' => session()->get('user')['id_pengguna'],
                    'date_kartu_stok' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return ['status' => 'success'];
    }

    public function voidDetails()
    {
        foreach ($this->details as $detail) {
            $master = MasterQrCode::where('kode_batang_master_qr_code', $detail->qr_code)->first();
            if ($master) {
                $master->sisa_master_qr_code = $master->jumlah_master_qr_code;
                $master->save();
            }
        }

        return ['status' => 'success'];
    }
}
