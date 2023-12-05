<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Closing;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\SaldoBalance;
use App\Models\Master\Akun;
use App\Models\Master\Pelanggan;
use App\Models\Master\Pemasok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferBalanceController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'transaction/transfer_balance', 'show') == false) {
            // Log::debug(checkUserSession($request, 'closing_journal', 'show'));
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }
        
        $data_cabang = getCabang();
        $data_pelanggan = Pelanggan::all();
        $data_pemasok = Pemasok::all();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Transfer Saldo",
            "data_cabang" => $data_cabang,
            "data_pelanggan" => $data_pelanggan,
            "data_pemasok" => $data_pemasok,
        ];

        return view('accounting.journal.transfer_balance.form', $data);
    }

    public function saldoTransfer(Request $request)
    {
        try {
            // Init Data
            $id_cabang = $request->id_cabang;
            $month = $request->month;
            $year = $request->year;
            $start_date = date("Y-m-d", strtotime("$year-$month-1"));
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));
            $nextMonth = date("n", strtotime("+1 month $start_date"));
            $nextYear = date("Y", strtotime("+1 month $start_date"));

            DB::beginTransaction();
            // Delete saldo transfer if exist
            $delete = SaldoBalance::where("bulan", $nextMonth)->where("tahun", $nextYear)->where("id_cabang", $id_cabang)->delete();
            
            // Get all account that is shown 1
            $dataAkun = Akun::where("id_cabang", $id_cabang)->where("isshown", 1)->get();
            // Log::debug($dataAkun);
            // dd($dataAkun);
            $debet = 0;
            $kredit = 0;
            // dd(count($dataAkun));
            foreach ($dataAkun as $key => $akun) {
                // Get sum debet dan sum kredit
                // $data_ledgers = JurnalDetail::join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                // ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                // ->where("jurnal_header.void", "0")
                // ->where("master_akun.id_cabang", $id_cabang)
                // ->whereBetween("jurnal_header.tanggal_jurnal", [$start_date, $end_date])
                // ->selectRaw("jurnal_header.id_jurnal, master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")->groupBy("jurnal_detail.id_akun")->first();
                $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $akun->id_akun)->where("id_cabang", $akun->id_cabang)->where("bulan", $month)->where("tahun", $year)->first();
                $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                    ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                    ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                    ->where("jurnal_detail.id_akun", $akun->id_akun)
                    ->where("jurnal_header.id_cabang", $akun->id_cabang)
                    ->where("jurnal_header.void", "0")
                    ->where("jurnal_header.tanggal_jurnal", ">=", $start_date)
                    ->where("jurnal_header.tanggal_jurnal", "<=", $end_date)
                    ->groupBy("jurnal_detail.id_akun")->first();
                $saldo_debet = ($saldo) ? $saldo->saldo_debet : 0;
                $saldo_kredit = ($saldo) ? $saldo->saldo_kredit : 0;
                // Log::info("saldo debet ".$saldo_debet." saldo kredit ".$saldo_kredit);
                $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                // Log::info("saldo debet ".$debet." saldo kredit ".$kredit);
                $saldo_debet = $saldo_debet + $debet + (isset($data_ledgers->debet) ? $data_ledgers->debet : 0);
                $saldo_kredit = $saldo_kredit + $kredit + (isset($data_ledgers->kredit) ? $data_ledgers->kredit : 0);
                $saldoAkhir = (float) $saldo_debet - (float) $saldo_kredit;

                // Insert into saldo balance
                $saldo_balance = new SaldoBalance;
                $saldo_balance->id_cabang = $akun->id_cabang;
                $saldo_balance->id_akun = $akun->id_akun;
                $saldo_balance->bulan = $nextMonth;
                $saldo_balance->tahun = $nextYear;
                $saldo_balance->debet = ($saldoAkhir > 0) ? $saldoAkhir : 0; //$saldo_debet;
                $saldo_balance->credit = ($saldoAkhir > 0) ? 0 : floatval(abs($saldoAkhir)); //$saldo_kredit;
                if (!$saldo_balance->save()) {
                    // Revert post closing
                    DB::rollback();

                    return response()->json([
                        "result" => false,
                        "message" => "Transfer Saldo Gagal.",
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully store transfer saldo data",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            $message = "Transfer saldo error";
            // Revert post closing
            $month = $request->month;
            $year = $request->year;
            $id_cabang = $request->id_cabang;

            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }
}
