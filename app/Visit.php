<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Visit extends Model
{
    protected $table = 'visit';
    protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $fillable = [
        'id_cabang',
        'id_salesman',
        'id_pelanggan',
        'visit_date',
        'status',
        'visit_title',
        'visit_desc',
        'pre_visit_desc',
        'coordinate',
        'user_created',
        'created_at',
        'user_modified',
        'updated_at',
        'pre_visit_code',
        'visit_code',
        'progress_ind',
        'visit_type',
        'alasan_pembatalan',
        'range_potensial',
        'total',
        'proofment_1',
        'proofment_2',
        'permintaan_penjualan_id',
        'alasan_ubah_tanggal',
        'kategori_kunjungan',
        'solusi',
        'latitude_visit',
        'longitude_visit',
    ];

    public static $progressIndicator = [
        'VISIT', 'TERIMA SAMPLE', 'TRIAL SAMPLE', 'APPROVAL SAMPLE', 'QUOTATION', 'APPROVAL QUOTATION', 'ISSUED PO',
    ];

    public static $initialProgressIndicator = [
        'V', 'TS', 'T', 'AS', 'Q', 'AQ', 'IP',
    ];

    public static $visitMethod = ['LOKASI', 'WHATSAPP', 'TELEPON'];

    public static $kategoriPelanggan = ['EXISTING CUSTOMER', 'NEW CUSTOMER', 'OLD CUSTOMER'];

    public static $listStatus = [
        '0' => ['text' => 'Batal', 'html' => '<label class="label label-danger">Batal</label>'],
        '1' => ['text' => 'Belum Dilaksanakan', 'html' => '<label class="label label-info">Belum dilaksanakan</label>'],
        '2' => ['text' => 'Sudah Dilaksanakan', 'html' => '<label class="label label-success">Sudah Dilaksanakan</label>'],
    ];

    public function getNamaPelangganAttribute()
    {
        return $this->pelanggan ? $this->pelanggan->nama_pelanggan : '-';
    }

    public function getNamaSalesmanAttribute()
    {
        return $this->salesman ? $this->salesman->nama_salesman : '-';
    }

    public function getNamaCabangAttribute()
    {
        return $this->cabang ? $this->cabang->nama_cabang : '-';
    }

    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'id_salesman', 'id_salesman');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang', 'id_cabang');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Models\Master\Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function sales_order()
    {
        return $this->belongsTo(PermintaanPenjualan::class, 'permintaan_penjualan_id', 'id_permintaan_penjualan');
    }

    public function medias()
    {
        return $this->hasMany(Media::class, 'id', 'id')
            ->select('id_media as id', 'lokasi_media as image')
            ->where('tipe_media', 'visit');
    }

    public static function createcode($id_cabang)
    {
        $branchCode = DB::table('cabang')->where('id_cabang', $id_cabang)->first();
        $string = 'KS.' . $branchCode->kode_cabang . '.' . date('ym');
        $check = DB::table('visit')->where('visit_code', 'like', $string . '%')->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (4 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        return $string . '.' . $nol . $check;
    }

    public function uploadfile($medias)
    {
        foreach ($medias as $media) {
            if (is_string($media)) {
                $explode = explode(";base64,", $media);
                $findExt = explode("image/", $explode[0]);

                $ext = $findExt[1];
                $name = uniqid();

                $media = base64_decode($explode[1]);
                $mainpath = $name . '.' . $ext;

                $img = \Image::make($media);
                $img->save('asset/' . $mainpath);

                $id = DB::table('media')->insert([
                    'id' => $this->id,
                    'lokasi_media' => 'asset/' . $mainpath,
                    'status_media' => '1',
                    'tipe_media' => 'visit',
                    'keterangan_media' => '',
                    'date_media' => date('Y-m-d'),
                    'user_media' => session()->get('user')['id_pengguna'],
                ]);
            }
        }

        return ['result' => true];
    }

    public function removefile($medias)
    {
        foreach ($medias as $media) {
            if (!is_string($media)) {
                $data = Media::where('id_media', $media->id)->first();
                unlink(public_path($media->image));
                $data->delete();
            }
        }

        return ['result' => true];
    }
}
