<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QualityControl extends Model
{
    protected $table = 'qc';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_cabang', 'id_pembelian', 'id_barang', 'id_satuan_barang', 'jumlah_pembelian_detail', 'tanggal_qc', 'status_qc', 'reason', 'sg_pembelian_detail', 'be_pembelian_detail', 'ph_pembelian_detail', 'warna_pembelian_detail', 'keterangan_pembelian_detail', 'bentuk_pembelian_detail', 'approval_date', 'approval_reason', 'approval_user_id', 'path', 'path2',
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
        $purchase = PurchaseDetail::where('id_pembelian', $this->id_pembelian)->where('id_barang', $this->id_barang)->get();
        foreach ($purchase as $p) {
            $p->sg_pembelian_detail = $this->sg_pembelian_detail;
            $p->be_pembelian_detail = $this->be_pembelian_detail;
            $p->ph_pembelian_detail = $this->ph_pembelian_detail;
            $p->warna_pembelian_detail = $this->warna_pembelian_detail;
            $p->bentuk_pembelian_detail = $this->bentuk_pembelian_detail;
            $p->keterangan_qc_pembelian_detail = $this->keterangan_pembelian_detail;
            $p->save();

            $data = MasterQrCode::where('kode_batang_lama_master_qr_code', $p->kode_batang_pembelian_detail)->where('id_barang', $this->id_barang)->first();
            $data->sg_master_qr_code = $this->sg_pembelian_detail;
            $data->be_master_qr_code = $this->be_pembelian_detail;
            $data->ph_master_qr_code = $this->ph_pembelian_detail;
            $data->warna_master_qr_code = $this->warna_pembelian_detail;
            $data->bentuk_master_qr_code = $this->bentuk_pembelian_detail;
            $data->keterangan_qc_master_qr_code = $this->keterangan_pembelian_detail;
            $data->status_qc_qr_code = $this->status_qc;
            $data->save();
        }

        return true;
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
