<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $primaryKey = 'id_media';

    protected $fillable = [
        'id',
        'nama_media',
        'lokasi_media',
        'tipe_media',
        'keterangan_media',
        'status_media',
        'user_media',
        'date_media',
    ];
}
