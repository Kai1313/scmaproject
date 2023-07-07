<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class QualityControl extends Model
{
    protected $table = 'qc';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_cabang', 'id_pembelian', 'id_barang', 'id_satuan_barang', 'jumlah_pembelian_detail', 'tanggal_qc', 'status_qc', 'reeason', 'sg_pembelian_detail', 'be_pembelian_detail', 'ph_pembelian_detail', 'warna_pembelian_detail', 'keterangan_pembelian_detail', 'bentuk_pembelian_detail', 'approval_date', 'approval_reason', 'approval_user_id', 'path', 'path2',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'id_pembelian');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function satuan()
    {
        return $this->belongsTo(SatuanBarang::class, 'id_satuan_barang');
    }

    public function updatePembelianDetail()
    {
        $array = [
            'sg_pembelian_detail' => $this->sg_pembelian_detail,
            'be_pembelian_detail' => $this->be_pembelian_detail,
            'ph_pembelian_detail' => $this->ph_pembelian_detail,
            'warna_pembelian_detail' => $this->warna_pembelian_detail,
            'bentuk_pembelian_detail' => $this->bentuk_pembelian_detail,
            'keterangan_pembelian_detail' => $this->keterangan_pembelian_detail,
        ];

        DB::table('pembelian_detail')->where('id_pembelian', $this->id_pembelian)
            ->where('id_barang', $this->id_barang)->update($array);

        DB::table('master_qr_code')->where('nama_master_qr_code', $this->purchase->nama_pembelian)
            ->where('id_barang', $this->id_barang)->update([
            'sg_master_qr_code' => $this->sg_pembelian_detail,
            'be_master_qr_code' => $this->be_pembelian_detail,
            'ph_master_qr_code' => $this->ph_pembelian_detail,
            'warna_master_qr_code' => $this->warna_pembelian_detail,
            'bentuk_master_qr_code' => $this->bentuk_pembelian_detail,
            'keterangan_master_qr_code' => $this->keterangan_pembelian_detail,
            'status_qc_qr_code' => $this->status_qc,
        ]);

        return true;
    }

    public function uploadfile($req, $data)
    {
        if ($req->image_path) {
            if ($data->path) {
                \Storage::delete([$data->path, $data->path2]);
            }

            $explode = explode(";base64,", $req->image_path);
            $media = base64_decode($explode[1]);
            $media2 = base64_decode($explode[1]);
            $name = uniqid();
            $fileName = ($data->path ? $data->path : $name);
            \Storage::put($fileName, $media);

            $fileName2 = ($data->path2 ? $data->path2 : $name . '-thumbnail');
            $newImg = \Image::make($media2)->fit(150);
            \Storage::put($fileName2, (string) $newImg->encode());

            $data->path = $fileName;
            $data->path2 = $fileName2;
            $data->save();
        }

        return ['status' => 'success'];
    }
}
