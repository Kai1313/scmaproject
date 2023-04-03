<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Master\Setting;
use App\Models\Master\Akun;
use DB;
use Log;

class MasterSettingController extends Controller
{
    public function getSettingPelunasan($cabang)
    {
        try {
            $cabang = $cabang;
            $piutang_dagang = Setting::select("master_akun.id_akun", "master_akun.nama_akun", "master_akun.kode_akun")->where("code", "Piutang Dagang")->where("setting.id_cabang", $cabang)->join("master_akun", "master_akun.id_akun", "setting.value2")->first();
            $hutang_dagang = Setting::select("master_akun.id_akun", "master_akun.nama_akun", "master_akun.kode_akun")->where("code", "Hutang Dagang")->where("setting.id_cabang", $cabang)->join("master_akun", "master_akun.id_akun", "setting.value2")->first();
            return response()->json([
                "result"=>TRUE,
                "piutang_dagang" => $piutang_dagang,
                "hutang_dagang" => $hutang_dagang,
            ]);
        } 
        catch (\Exception $e) {
            Log::error("Error when get setting pelunasan");
            Log::error($e);
            return response()->json([
                "result"=>FALSE,
                "message"=>"Error when get setting pelunasan"
            ]);
        }
    }
}
