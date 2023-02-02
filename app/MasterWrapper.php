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
            $media = $req->file('file_upload');
            $name = date('Ymd') . str_random(4);
            $ext = strtolower($media->getClientOriginalExtension());
            $fileName = $name . '.' . $ext;

            \Storage::put($fileName, file_get_contents($req->file_upload));

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
