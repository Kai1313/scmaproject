<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Log;

class Periode extends Model
{
    protected $table = 'periode';

    public static function checkPeriod($trxDate) {
        try {
            // Init data
            $month = date("m", strtotime($trxDate));
            $year = date("Y", strtotime($trxDate));

            // Get periode
            $periode = self::where("tahun_periode", $year)->where("bulan_periode", $month)->first();
            if ($periode) {
                if ($periode->status_periode == 0) {
                    return TRUE;
                }
                return FALSE;
            }
            return FALSE;
        } catch (\Exception $e) {
            Log::error("Error when check periode close");
            Log::error($e);
            return FALSE;
        }
    }
}
