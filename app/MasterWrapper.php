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
            // $size = $media->getClientSize();
            // $type = explode('/', $media->getClientMimeType())[0];
            $name = date('Ymd') . str_random(4);
            $ext = strtolower($media->getClientOriginalExtension());
            $fileName = $name . '.' . $ext;

            \Storage::disk('ftp')->put($fileName, fopen($media, 'r+'));
            // $media->move($folder['folder_path'], $mainpath);

            // $newpath = $folder['path'] . '/' . $mainpath;
            // $res = $this->mediaSave($media, $type, $newpath, $size);
            // $setCrops = Setting::where('type', 'handle-image')->where('status', '1')->get();
            // if (sizeof($setCrops) > 0) {
            //     foreach ($setCrops as $set) {
            //         $newImg = \Image::make(public_path($newpath));
            //         $path = $name . '-' . $set->key . '.' . $ext;
            //         $un = unserialize($set->value);
            //         $newImg->fit($un['width'], $un['height']);
            //         $newImg->save($folder['folder_path'] . '/' . $path);
            //     }
            // }

            $data->path = $fileName;
            $data->save();
        }
    }
}
