<?php

namespace App;

use App\MasterQrCode;
use App\Models\Master\Cabang;
use DB;
use Illuminate\Database\Eloquent\Model;

class MoveBranch extends Model
{
    protected $table = 'pindah_barang';
    protected $primaryKey = 'id_pindah_barang';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    protected $fillable = [
        'id_pindah_barang',
        'id_pindah_barang2',
        'id_jenis_transaksi',
        'type',
        'id_cabang',
        'id_gudang',
        'tanggal_pindah_barang',
        'kode_pindah_barang',
        'id_cabang2',
        'id_gudang2',
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
        'id_produksi',
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
        return $this->belongsTo(MoveBranch::class, 'id_pindah_barang2', 'id_pindah_barang');
    }

    public function cabang2()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang2');
    }

    public function gudang2()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang2');
    }

    public function details()
    {
        return $this->hasMany(MoveBranchDetail::class, 'id_pindah_barang');
    }

    public function getDetailQRCode()
    {
        return $this->hasMany(MoveBranchDetail::class, 'id_pindah_barang')->select('qr_code');
    }

    public function produksi()
    {
        return $this->belongsTo(Production::class, 'id_produksi');
    }

    public function formatDetailGroupBy()
    {
        return $this->hasMany(MoveBranchDetail::class, 'id_pindah_barang')
            ->select(
                'be',
                'bentuk',
                'pindah_barang_detail.id_barang',
                'id_pindah_barang_detail',
                'pindah_barang_detail.id_satuan_barang',
                DB::raw('sum(qty) as qty'),
                'keterangan',
                'qr_code',
                'nama_barang',
                'nama_satuan_barang',
                'ph',
                'sg',
                'warna',
                'status_diterima',
                DB::raw('(case when status_diterima = 1 then "Diterima" else "Belum diterima" end) as status_akhir'),
                'batch',
                'tanggal_kadaluarsa',
                'zak',
                'weight_zak',
                'id_wrapper_zak',
                'pindah_barang_detail.id_barang as id_barang2',
                DB::raw('count(*) as count_data'),
                'keterangan_sj'
            )
            ->leftJoin('barang', 'pindah_barang_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pindah_barang_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
            ->groupBy(['nama_barang', 'batch']);
    }

    public function formatdetail()
    {
        return $this->hasMany(MoveBranchDetail::class, 'id_pindah_barang')
            ->select('pindah_barang_detail.*',
                'nama_barang',
                'nama_satuan_barang',
                DB::raw('(case when pindah_barang_detail.status_diterima = 1 then "Diterima" else "Belum diterima" end) as status_akhir')
            )
            ->leftJoin('barang', 'pindah_barang_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pindah_barang_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
            ->leftJoin('pindah_barang as pb', 'pindah_barang_detail.id_pindah_barang', 'pb.id_pindah_barang')->orderBy('nama_barang', 'asc');
        // ->leftJoin('pindah_barang as pb2', 'pb.id_pindah_barang', 'pb2.id_pindah_barang2');
        // ->leftJoin('pindah_barang_detail as pbd', function ($jo) {
        //     $jo->on('pindah_barang_detail.qr_code', 'pbd.qr_code')->on('pbd.id_pindah_barang', 'pb2.id_pindah_barang');
        // });
    }

    public function formatdetail2()
    {
        return $this->hasMany(MoveBranchDetail::class, 'id_pindah_barang')
            ->select('pindah_barang_detail.*',
                'nama_barang',
                'nama_satuan_barang',
                DB::raw('(case when pindah_barang_detail.status_diterima = 1 then "Diterima" else "Belum diterima" end) as status_akhir')
            )
            ->leftJoin('barang', 'pindah_barang_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pindah_barang_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
            ->leftJoin('pindah_barang as pb', 'pindah_barang_detail.id_pindah_barang', 'pb.id_pindah_barang')->orderBy('id_pindah_barang_detail', 'asc');
        // ->leftJoin('pindah_barang as pb2', 'pb.id_pindah_barang', 'pb2.id_pindah_barang2');
        // ->leftJoin('pindah_barang_detail as pbd', function ($jo) {
        //     $jo->on('pindah_barang_detail.qr_code', 'pbd.qr_code')->on('pbd.id_pindah_barang', 'pb2.id_pindah_barang');
        // });
    }

    public function notReceivedDetail()
    {
        return $this->hasMany(MoveBranchDetail::class, 'id_pindah_barang')
            ->select(
                'pindah_barang_detail.id_barang',
                'pindah_barang_detail.id_pindah_barang_detail',
                'pindah_barang_detail.id_satuan_barang',
                'pindah_barang_detail.qty',
                'pindah_barang_detail.qr_code',
                'nama_barang',
                'nama_satuan_barang',
                'status_diterima',
                DB::raw('(case when pindah_barang_detail.status_diterima = 1 then "Diterima" else "Belum diterima" end) as status_diterima'),
                'pindah_barang_detail.batch',
                'pindah_barang_detail.tanggal_kadaluarsa'
            )
            ->leftJoin('barang', 'pindah_barang_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pindah_barang_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
            ->where('status_diterima', 0);
    }

    public static function createcodeCabang($id_cabang, $date)
    {
        $branchCode = DB::table('cabang')->where('id_cabang', $id_cabang)->first();
        $string = 'TC.' . $branchCode->kode_cabang . '.' . date('ym', strtotime($date));
        $check = DB::table('pindah_barang')->where('kode_pindah_barang', 'like', $string . '%')->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (4 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        return $string . '.' . $nol . $check;
    }

    public static function createcodeGudang($id_cabang)
    {
        $branchCode = DB::table('cabang')->where('id_cabang', $id_cabang)->first();
        $string = 'TG.' . $branchCode->kode_cabang . '.' . date('ym');
        $check = DB::table('pindah_barang')->where('kode_pindah_barang', 'like', $string . '%')->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (4 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        return $string . '.' . $nol . $check;
    }

    public function removedetails($details, $type = 'in')
    {
        $idJenisTransaksi = $this->id_jenis_transaksi;
        $detail = json_decode($details);
        $ids = array_column($detail, 'id_pindah_barang_detail');
        foreach ($detail as $trash) {
            $selectTrash = MoveBranchDetail::where('id_pindah_barang', $this->id_pindah_barang)
                ->where('id_pindah_barang_detail', $trash->id_pindah_barang_detail)
                ->first();
            if ($selectTrash) {
                $trashQrCode = MasterQrCode::where('kode_batang_master_qr_code', $trash->qr_code)->first();
                if ($trashQrCode) {
                    if ($type == 'out') {
                        $trashQrCode->sisa_master_qr_code = $trash->qty;
                    } else {
                        $trashQrCode->sisa_master_qr_code = 0;
                    }

                    $trashQrCode->save();
                }

                $kartuStok = KartuStok::where('id_jenis_transaksi', $idJenisTransaksi)
                    ->where('kode_batang_kartu_stok', $trash->qr_code)
                    ->where('kode_kartu_stok', $this->kode_pindah_barang)
                    ->delete();

                $selectTrash->delete();
            }
        }

        return ['status' => 'success', 'result' => true];
    }

    public function savedetails($details, $type = 'in')
    {
        $idJenisTransaksi = $this->id_jenis_transaksi;
        $detail = json_decode($details);
        $arrayNew = [];
        foreach ($detail as $data) {
            if ($data->id_pindah_barang_detail == '') {
                $arrayNew[] = $data->qr_code;
                // }
                // $check = MoveBranchDetail::where('id_pindah_barang', $this->id_pindah_barang)->where('qr_code', $data->qr_code)->first();
                // if (!$check) {
                $array = [
                    'id_pindah_barang' => $this->id_pindah_barang,
                    'id_barang' => $data->id_barang,
                    'id_satuan_barang' => $data->id_satuan_barang,
                    'qty' => $data->qty,
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
                    'tanggal_kadaluarsa' => $data->tanggal_kadaluarsa ? $data->tanggal_kadaluarsa : null,
                    'zak' => $data->zak,
                    'id_wrapper_zak' => $data->id_wrapper_zak,
                    'weight_zak' => $data->weight_zak,
                ];
                $store = new MoveBranchDetail;
                $store->fill($array);
                $store->save();

                $master = MasterQrCode::where('kode_batang_master_qr_code', $data->qr_code)->first();
                if ($master) {
                    if ($type == 'in' && $data->status_diterima == 1) {
                        $master->sisa_master_qr_code = $data->qty;
                        $master->id_cabang = $this->id_cabang;
                        $master->id_gudang = $this->id_gudang;
                        $master->id_jenis_transaksi = $idJenisTransaksi;
                    } else {
                        $master->sisa_master_qr_code = 0;
                    }

                    $master->save();
                }

                if (in_array($idJenisTransaksi, ['21', '22'])) {
                    DB::table('kartu_stok')->insert([
                        'id_gudang' => $this->id_gudang,
                        'id_jenis_transaksi' => $idJenisTransaksi,
                        'kode_kartu_stok' => $this->kode_pindah_barang,
                        'id_barang' => $data->id_barang,
                        'id_satuan_barang' => $data->id_satuan_barang,
                        'nama_kartu_stok' => $this->id_pindah_barang,
                        'nomor_kartu_stok' => $store->id_pindah_barang_detail,
                        'tanggal_kartu_stok' => $this->tanggal_pindah_barang,
                        'debit_kartu_stok' => $type == 'in' ? $store->qty : 0,
                        'kredit_kartu_stok' => $type == 'out' ? $store->qty : 0,
                        'tanggal_kadaluarsa_kartu_stok' => $data->tanggal_kadaluarsa ? $data->tanggal_kadaluarsa : null,
                        'mtotal_debit_kartu_stok' => 0,
                        'mtotal_kredit_kartu_stok' => 0,
                        'kode_batang_kartu_stok' => $data->qr_code,
                        'kode_batang_lama_kartu_stok' => '',
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
                        'zak' => $data->zak,
                        'id_wrapper_zak' => $data->id_wrapper_zak,
                        'weight_zak' => $data->weight_zak,
                    ]);
                }

                if (in_array($idJenisTransaksi, ['24'])) {
                    // foreach ($arrayNew as $qr) {
                    DB::table('kartu_stok')
                        ->where('id_jenis_transaksi', $idJenisTransaksi)
                        ->where('kode_batang_kartu_stok', $data->qr_code)
                        ->where('nama_kartu_stok', $this->id_pindah_barang2)
                        ->update([
                            'status_kartu_stok' => 1,
                            'kode_kartu_stok' => $this->kode_pindah_barang,
                            'tanggal_kartu_stok' => $this->tanggal_pindah_barang,
                        ]);
                    // }
                }
            }
            // else {
            //     $check->keterangan = $data->keterangan;
            //     $check->save();

            //     DB::table('kartu_stok')
            //         ->where('id_jenis_transaksi', $idJenisTransaksi)
            //         ->where('kode_batang_kartu_stok', $data->qr_code)->update([
            //         'keterangan_kartu_stok' => $data->keterangan,
            //     ]);
            // }
        }

        return ['status' => 'success', 'result' => true];
    }

    public function voidDetails()
    {
        foreach ($this->details as $detail) {
            $master = MasterQrCode::where('kode_batang_master_qr_code', $detail->qr_code)->first();
            if ($master) {
                $master->sisa_master_qr_code = $detail->qty;
                $master->save();
            }

            DB::table('kartu_stok')->where('kode_kartu_stok', $this->kode_pindah_barang)
                ->where('kode_batang_kartu_stok', $detail->qr_code)
                ->where('id_jenis_transaksi', $this->id_jenis_transaksi)->delete();
        }

        return ['status' => 'success'];
    }

    public function saveChangeStatusFromParent($array)
    {
        $details = MoveBranchDetail::where('id_pindah_barang', $this->id_pindah_barang)->get();
        foreach ($details as $detail) {
            if (in_array($detail->qr_code, $array)) {
                $detail->status_diterima = 1;
                $detail->save();
            }
        }

        return response()->json([
            'status' => 'success',
        ]);
    }
}
