<?php

namespace App;

use App\Models\Master\Cabang;
use Illuminate\Database\Eloquent\Model;

class MasterWrapper extends Model
{
    protected $table = 'master_wrapper';
    protected $primaryKey = 'id_wrapper';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    protected $fillable = [
        'id_cabang', 'nama_wrapper', 'weight', 'path', 'path2', 'catatan', 'user_created', 'dt_created', 'user_modified', 'dt_modified', 'id_kategori_wrapper',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    // public function uploadfile($req, $data)
    // {
    //     if ($req->image_path) {
    //         if ($data->path) {
    //             \Storage::delete([$data->path, $data->path2]);
    //         }

    //         $explode = explode(";base64,", $req->image_path);
    //         $media = base64_decode($explode[1]);
    //         $media2 = base64_decode($explode[1]);
    //         $name = uniqid();
    //         $fileName = ($data->path ? $data->path : $name);
    //         \Storage::put($fileName, $media);

    //         $fileName2 = ($data->path2 ? $data->path2 : $name . '-thumbnail');
    //         $newImg = \Image::make($media2)->fit(150);
    //         \Storage::put($fileName2, (string) $newImg->encode());

    //         $data->path = $fileName;
    //         $data->path2 = $fileName2;
    //         $data->save();
    //     }

    //     return ['status' => 'success'];
    // }

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
