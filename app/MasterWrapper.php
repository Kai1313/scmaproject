<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterWrapper extends Model
{
    protected $table = 'master_wrapper';
    protected $primaryKey = 'id_wrapper';
    public $timestamps = false;

    protected $fillable = [
        'id_cabang', 'nama_wrapper', 'weight', 'path', 'path2', 'catatan', 'user_created', 'dt_created', 'user_modified', 'dt_modified',
    ];

    public function uploadfile($req, $data)
    {
        if (isset($req->file_upload)) {
            // if ($data && $data->path) {
            //     $check = \Storage::exists($data->path);
            //     dd(Storage::disk('ftp')->exists($data->path));

            //     if ($check) {
            //         Storage::delete([$data->path, $data->path2]);
            //     }
            // }

            $media = $req->file('file_upload');
            $name = uniqid();
            $ext = strtolower($media->getClientOriginalExtension());
            $fileName = ($data->path ? $data->path : $name . '.' . $ext);

            \Storage::put($fileName, fopen($media, 'r+'));

            // $fileName2 = $name . '-mini.' . $ext;
            // $newImg = \Image::make($image)->fit(100, 100);
            // \Storage::disk('public')->put($fileName2, $newImg);

            // $newImg->save($folder['folder_path'] . '/' . $path);

            $data->path = $fileName;
            // $data->path2 = $fileName2;
            $data->save();
        }
    }
}
