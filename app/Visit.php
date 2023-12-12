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
    ];

    public static $progressIndicator = [
        'VISIT', 'TERIMA SAMPLE', 'TRIAL SAMPLE', 'APPROVAL SAMPLE', 'QUOTATION', 'APPROVAL QUOTATION', 'ISSUED PO',
    ];

    public static $visitMethod = ['LOKASI', 'WHATSAPP', 'TELEPON'];

    public static $kategoriPelanggan = ['EXISTING CUSTOMER', 'NEW CUSTOMER', 'OLD CUSTOMER'];

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

    public function uploadfile($req, $data)
    {
        if ($req->image_path) {
            $explode = explode(";base64,", $req->image_path);
            $findExt = explode("image/", $explode[0]);

            $ext = $findExt[1];
            $name = uniqid();

            $media = base64_decode($explode[1]);
            $mainpath = $name . '.' . $ext;

            $img = \Image::make($media)->fit(150);
            $img->save('asset/' . $mainpath);

            $data->path = $mainpath;
            $data->save();
        }

        return ['status' => 'success'];
    }
}
