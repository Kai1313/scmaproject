<?php

namespace App\Models\Accounting;

use App\SaldoTransaksi;
use App\Cabang;
use App\Models\Master\Slip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class JurnalHeader extends Model
{
    protected $table = 'jurnal_header';
    protected $primaryKey = 'id_jurnal';
    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';

    function jurnalDetails()
    {
        return $this->hasMany(JurnalDetail::class, 'id_jurnal', 'id_jurnal');
    }

    function saldo_transaksi()
    {
        return $this->hasMany(SaldoTransaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public static function generateJournalCode($cabang, $jenis){
        try {
            $ex = 0;
            do {
                // Init data
                $kodeCabang = Cabang::find($cabang);
                $prefix = $kodeCabang->kode_cabang . "." . $jenis . "." . date("ym");
    
                // Check exist
                $check = JurnalHeader::selectRaw("kode_jurnal,
                CONCAT(SUBSTRING_INDEX(kode_jurnal, '.', 2), '.', LPAD(SUBSTRING_INDEX(kode_jurnal, '.', -1), 5, '0')) AS formatted_kode_jurnal")
                    ->where("kode_jurnal", "LIKE", "$prefix%")
                    ->orderByRaw("CAST(SUBSTRING_INDEX(`kode_jurnal`, '.', -1) AS UNSIGNED) DESC")->get();
                if (count($check) > 0) {
                    // echo 'ini check : ' . $check[0]->kode_jurnal . '<br>';
                    // echo 'ini check formatted : ' . $check[0]->formatted_kode_jurnal . '<br>';
                    $max = (int) substr($check[0]->kode_jurnal, -5);
                    if ($max == 0) { // kurang dari 10000
                        $max = (int) substr($check[0]->kode_jurnal, -4);
                    }
                    // echo 'ini max belum ditambah : ' . $max . '<br>';
                    $max += 1;
                    // echo 'ini max : ' . $max . '<br>';
                    $code = $prefix . "." . sprintf("%04s", $max);
                    // echo 'ini code : ' . $max . '<br>';
                } else {
                    $code = $prefix . ".0001";
                }
                // echo 'in while : ' . $code . '<br>';
                $ex++;
                if ($ex >= 5) {
                    $code = "error";
                    break;
                }
            } while (JurnalHeader::where("kode_jurnal", $code)->first());
            // echo 'after while : ' . $code . "<br>";
            return $code;
        } catch (\Exception $e) {
            Log::error("Error when generate journal code");
            Log::error($e);
        }
    }

    public static function generateJournalCodeWithSlip($cabang, $jenis, $slip){
        try {
            $ex = 0;
            do {
                // Init data
                $kodeCabang = Cabang::find($cabang);
                $kodeSlip = Slip::find($slip);
                $prefix = $kodeCabang->kode_cabang . "." . $jenis . "." . $kodeSlip->kode_slip . "." . date("ym");
    
                // Check exist
                $check = JurnalHeader::selectRaw("kode_jurnal,
                CONCAT(SUBSTRING_INDEX(kode_jurnal, '.', 2), '.', LPAD(SUBSTRING_INDEX(kode_jurnal, '.', -1), 5, '0')) AS formatted_kode_jurnal")
                    ->where("kode_jurnal", "LIKE", "$prefix%")
                    ->orderByRaw("CAST(SUBSTRING_INDEX(`kode_jurnal`, '.', -1) AS UNSIGNED) DESC")->get();
                if (count($check) > 0) {
                    // echo 'ini check : ' . $check[0]->kode_jurnal . '<br>';
                    // echo 'ini check formatted : ' . $check[0]->formatted_kode_jurnal . '<br>';
                    $max = (int) substr($check[0]->kode_jurnal, -5);
                    if ($max == 0) { // kurang dari 10000
                        $max = (int) substr($check[0]->kode_jurnal, -4);
                    }
                    // echo 'ini max belum ditambah : ' . $max . '<br>';
                    $max += 1;
                    // echo 'ini max : ' . $max . '<br>';
                    $code = $prefix . "." . sprintf("%04s", $max);
                    // echo 'ini code : ' . $max . '<br>';
                } else {
                    $code = $prefix . ".0001";
                }
                // echo 'in while : ' . $code . '<br>';
                $ex++;
                if ($ex >= 5) {
                    $code = "error";
                    break;
                }
            } while (JurnalHeader::where("kode_jurnal", $code)->first());
            // echo 'after while : ' . $code . "<br>";
            return $code;
        } catch (\Exception $e) {
            Log::error("Error when generate journal code");
            Log::error($e);
        }
    }
}
