<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterQrCode extends Model
{
    protected $table = 'master_qr_code';
    protected $primaryKey = 'id_master_qr_code';
    public $timestamps = false;
}
