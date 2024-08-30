<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Log;

class SuratJalan extends Model
{
    protected $table = "surat_jalan";
    protected $primaryKey = 'id';

    protected $fillable = [
        'no_surat_jalan', 'tanggal', 'id_pengguna', 'keterangan', 'no_dokumen_lain', 'penerima', 'alamat_penerima', 'no_dokumen_iso', 'status_revisi_iso', 'tanggal_berlaku_iso', 'status', 'jenis',
    ];

    public function details()
    {
        return $this->hasMany(SuratJalanDetail::class, 'id_surat_jalan');
    }

    public static function createcode()
    {
        $endString = '/SJ-U/' . date('m') . '/' . date('Y');
        $check = \DB::table('surat_jalan')->where('no_surat_jalan', 'like', '%' . $endString)->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (3 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        $string = $nol . $check . $endString;
        return $string;
    }

    public function savedetails($details)
    {
        try {
            $detail = json_decode($details);
            foreach ($detail as $key => $data) {
                $array = [];
                $store = SuratJalanDetail::find($data->id);
                $array = [
                    'nama_barang' => $data->nama_barang,
                    'satuan' => $data->satuan,
                    'jumlah' => $data->jumlah,
                    'keterangan' => $data->keterangan,
                ];

                if (!$store) {
                    $array['id_surat_jalan'] = $this->id;
                    $store = new SuratJalanDetail;
                }

                $store->fill($array);
                $store->save();
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

    public function deleteDetails($details)
    {
        try {
            $detail = json_decode($details);
            $ids = array_column($detail, 'id');
            SuratJalanDetail::whereIn('id', $ids)->delete();

            return ['result' => true];
        } catch (\Exception $th) {
            Log::error($th);
            return ["result" => false, "message" => "Data gagal diproses"];
        }
    }
}
