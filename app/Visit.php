<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $table = 'visit';
    protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $fillable = [
        'id_cabang',
        'id_salesman',
        'id_pelanggan',
        'visit_date',
        'status',
        'visit_title',
        'visit_desc',
        'pre_visit_desc',
        'coordinate',
        'user_created',
        'created_at',
        'user_modified',
        'updated_at',
        'pre_visit_code',
        'visit_code',
        'progress_ind',
        'visit_type',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang', 'id_cabang');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Models\Master\Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public static function createcode($id_cabang)
    {
        $branchCode = DB::table('cabang')->where('id_cabang', $id_cabang)->first();
        $string = 'KS.' . $branchCode->kode_cabang . '.' . date('ym');
        $check = DB::table('visit')->where('visit_code', 'like', $string . '%')->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (4 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        return $string . '.' . $nol . $check;
    }
}
