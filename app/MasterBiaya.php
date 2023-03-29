<?php

namespace App;

use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use Illuminate\Database\Eloquent\Model;

class MasterBiaya extends Model
{
    protected $table = 'master_biaya';
    protected $primaryKey = 'id_biaya';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    protected $fillable = [
        'id_cabang', 'nama_biaya', 'id_akun_biaya', 'isppn', 'ispph', 'id_akun_pph', 'value_pph', 'aktif', 'user_created', 'dt_created', 'user_modified', 'dt_modified',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    public function akunBiaya()
    {
        return $this->belongsTo(Akun::class, 'id_akun_biaya');
    }

    public function akunPph()
    {
        return $this->belongsTo(Akun::class, 'id_akun_pph');
    }

}
