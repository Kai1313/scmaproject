<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    protected $table = "surat_jalan";

    protected $fillable = [
        'no_surat_jalan', 'tanggal', 'id_pengguna', 'keterangan', 'no_dokumen_lain', 'penerima', 'alamat_penerima', 'no_dokumen_iso', 'status_revisi_iso', 'tanggal_berlaku_iso', 'status', 'jenis',
    ];

    public function details()
    {
        return $this->hasMany(SuratJalanDetail::class, 'id_surat_jalan');
    }

    public function savedetail($data)
    {
        try {
            $detail = json_decode($details);

            foreach ($detail as $key => $data) {
                $array = [
                    'id_surat_jalan' => $this->id_pemakaian,
                    'nama_barang' => $data->id_barang,
                    'id_satuan_barang' => $data->id_satuan_barang,
                    'jumlah' => $data->jumlah,
                    'kode_batang' => $data->kode_batang,
                    'index' => count($this->details) + 1,
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
                    'id_cabang' => $this->id_cabang,
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
                    'id_wrapper' => $master->id_wrapper,
                    'weight' => $master->weight,
                    'nett' => $store->jumlah - $master->weight - $store->weight_zak,
                ]);
            }
            return ['result' => true];
        } catch (\Exception $e) {
            Log::error($e);
            return [
                "result" => false,
                "message" => "Data gagal disimpan",
            ];
        }
    }

    public function deleteDetail($id)
    {
        try {
            $data = MaterialUsageDetail::where('id_pemakaian', $this->id_pemakaian)->where('index', $id)->first();
            if ($data) {
                $master = MasterQrCode::where('kode_batang_master_qr_code', $data->kode_batang)->first();
                if ($master) {
                    $master->sisa_master_qr_code = $master->sisa_master_qr_code + $data->jumlah;
                    $master->zak = ($master->zak ? $master->zak : 0) + $data->jumlah_zak;
                    $master->weight_zak = ($master->weight_zak ? $master->weight_zak : 0) + $data->weight_zak;
                    $master->save();
                }

                DB::table('kartu_stok')->where('kode_kartu_stok', $this->kode_pemakaian)
                    ->where('kode_batang_kartu_stok', $data->kode_batang)
                    ->where('id_jenis_transaksi', 25)->delete();

                MaterialUsageDetail::where('id_pemakaian', $this->id_pemakaian)->where('index', $id)->delete();

                $details = MaterialUsageDetail::where('id_pemakaian', $this->id_pemakaian)->orderBy('index', 'asc')->get();
                foreach ($details as $key => $detail) {
                    MaterialUsageDetail::where('id_pemakaian', $this->id_pemakaian)->where('index', $detail->index)->update(['index' => $key + 1]);
                }
            }

            return ['result' => true];
        } catch (\Exception $th) {
            Log::error($th);
            return ["result" => false, "message" => "Data gagal diproses"];
        }
    }
}
