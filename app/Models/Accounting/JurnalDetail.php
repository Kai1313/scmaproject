<?php

namespace App\Models\Accounting;

use App\Models\Master\Akun;
use Illuminate\Database\Eloquent\Model;

class JurnalDetail extends Model
{
    protected $table = 'jurnal_detail';
    protected $primaryKey = 'id_jurnal';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    function masterAkun()
    {
        return $this->hasOne(Akun::class, 'id_akun', 'id_akun');
    }
}
