<?php

namespace App;

use App\Models\Master\Cabang;
use Illuminate\Database\Eloquent\Model;

class MasterWrapper extends Model
{
    protected $table = 'master_wrapper';
    protected $primaryKey = 'id_wrapper';
    public $timestamps = false;

    protected $fillable = [
        'id_cabang', 'nama_wrapper', 'weight', 'path', 'path2', 'catatan', 'user_created', 'dt_created', 'user_modified', 'dt_modified',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
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
