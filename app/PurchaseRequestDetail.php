<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestDetail extends Model
{
    protected $table = 'purchase_request_detail';
    public $timestamps = false;

    protected $fillable = [
        'purchase_request_id', 'index', 'id_barang', 'id_satuan_barang', 'qty', 'notes', 'approval_notes', 'approval_status', 'approval_user_id', 'approval_date', 'closed',
    ];
    // protected $primaryKey = 'purchase_request_id';

    // const CREATED_AT = 'dt_created';
    // const UPDATED_AT = 'dt_modified';

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function satuan()
    {
        return $this->belongsTo(SatuanBarang::class, 'id_satuan_barang');
    }

    public function kartuStok()
    {
        return $this->belongsTo(KartuStok::class, 'id_barang')
            ->select(\DB::raw('sum(debit_kartu_stok) - sum(kredit_kartu_stok)'))
            ->groupBy('id_barang');
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
                    'id' => $this->purchase_request_id,
                    'nama_media' => $this->index,
                    'lokasi_media' => 'asset/' . $mainpath,
                    'status_media' => '1',
                    'tipe_media' => 'purchase_request',
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
                $data = Media::where('id_media', $media)->first();
                unlink(public_path($data->lokasi_media));
                $data->delete();
            }
        }

        return ['result' => true];
    }
}
