<?php

namespace App;

use App\Models\Master\Cabang;
use Illuminate\Database\Eloquent\Model;

class MaterialUsage extends Model
{
    protected $table = 'pemakaian_header';
    protected $primaryKey = 'id_pemakaian';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    protected $fillable = [
        'tanggal',
        'kode_pemakaian',
        'id_cabang',
        'id_gudang',
        'catatan',
        'user_created',
        'dt_created',
        'user_modified',
        'dt_modified',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'id_gudang');
    }

    public function details()
    {
        return $this->hasMany(MaterialUsageDetail::class, 'id_pemakaian');
    }
}
