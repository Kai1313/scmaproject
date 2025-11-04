<?php
namespace App\Http\Controllers;

use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\SaldoBalance;
use App\Models\Master\Akun;
use App\Models\Master\Pelanggan;
use App\Models\Master\Pemasok;
use DateInterval;
use DatePeriod;
use DateTime;
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

        $data_cabang    = getCabang();
        $data_pelanggan = Pelanggan::all();
        $data_pemasok   = Pemasok::all();

        $data = [
            "pageTitle"      => "SCA Accounting | Transaksi Transfer Saldo",
            "data_cabang"    => $data_cabang,
            "data_pelanggan" => $data_pelanggan,
            "data_pemasok"   => $data_pemasok,
        ];

        return view('accounting.journal.transfer_balance.form', $data);
    }

    public function saldoTransfer(Request $request)
    {
        try {
            // Init Data
            $id_cabang = $request->id_cabang;
            // $start_month = $request->start_month;
            // $end_month   = $request->end_month;

            $start_date = date("Y-m-d", strtotime("$request->start_month-01"));
            $end_date   = date("Y-m-t", strtotime("$request->end_month-01"));

            $start_date = new DateTime($start_date);
            $end_date   = new DateTime($end_date);
            $interval   = new DateInterval('P1M');

            $period   = new DatePeriod($start_date, $interval, $end_date);
            $dataAkun = Akun::where("id_cabang", $id_cabang)->where("isshown", 1)->where('id_akun', '4')->get();

            DB::beginTransaction();
            foreach ($period as $date) {
                $month = $date->format('n');
                $year  = $date->format('Y');

                $startDatePeriod = date("Y-m-d", strtotime($date->format('Y-m-d')));
                $endDatePeriod   = date("Y-m-t", strtotime($date->format('Y-m-d')));

                $nextMonth = date("n", strtotime("+1 month $startDatePeriod"));
                $nextYear  = date("Y", strtotime("+1 month $startDatePeriod"));

                SaldoBalance::where("bulan", $nextMonth)->where("tahun", $nextYear)->where("id_cabang", $id_cabang)->delete();

                // Init debet kredit
                $debet  = 0;
                $kredit = 0;

                foreach ($dataAkun as $key => $akun) {
                    $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")
                        ->where("id_akun", $akun->id_akun)->where("id_cabang", $akun->id_cabang)
                        ->where("bulan", $month)->where("tahun", $year)->first();
                    // dd($saldo);

                    $data_saldo_ledgers = JurnalDetail::selectRaw("SUM(IFNULL(jurnal_detail.debet, 0)) as debet, SUM(IFNULL(jurnal_detail.credit, 0)) as kredit")
                        ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                        ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                        ->where("jurnal_detail.id_akun", $akun->id_akun)
                        ->where("jurnal_header.id_cabang", $akun->id_cabang)
                        ->where("jurnal_header.void", "0")
                        ->whereBetween("jurnal_header.tanggal_jurnal", [$startDatePeriod, $endDatePeriod])
                    // ->where("jurnal_header.tanggal_jurnal", ">=", $startDatePeriod)
                    // ->where("jurnal_header.tanggal_jurnal", "<=", $endDatePeriod)
                        ->groupBy("jurnal_detail.id_akun")->first();
                    // dd($data_saldo_ledgers);

                    $saldo_debet  = ($saldo) ? $saldo->saldo_debet : 0;
                    $saldo_kredit = ($saldo) ? $saldo->saldo_kredit : 0;
                    // Log::info("saldo debet ".$saldo_debet." saldo kredit ".$saldo_kredit);

                    $debet  = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                    $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                    // Log::info("saldo debet ".$debet." saldo kredit ".$kredit);
                    // dd($saldo_debet, $saldo_kredit, $debet, $kredit);
                    $saldo_debet  = $saldo_debet + $debet;
                    $saldo_kredit = $saldo_kredit + $kredit;
                    $saldoAkhir   = (float) $saldo_debet - (float) $saldo_kredit;

                    $saldo_balance            = new SaldoBalance;
                    $saldo_balance->id_cabang = $akun->id_cabang;
                    $saldo_balance->id_akun   = $akun->id_akun;
                    $saldo_balance->bulan     = $nextMonth;
                    $saldo_balance->tahun     = $nextYear;
                    $saldo_balance->debet     = ($saldoAkhir > 0) ? $saldoAkhir : 0;                //$saldo_debet;
                    $saldo_balance->credit    = ($saldoAkhir > 0) ? 0 : floatval(abs($saldoAkhir)); //$saldo_kredit;

                    if (! $saldo_balance->save()) {
                        // Revert post closing
                        DB::rollback();

                        return response()->json([
                            "result"  => false,
                            "message" => "Transfer Saldo Gagal.",
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                "result"  => true,
                "message" => "Successfully store transfer saldo data",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            $message = "Transfer saldo error";

            Log::error($message);
            Log::error($e);
            return response()->json([
                "result"  => false,
                "message" => $message,
            ]);
        }
    }
}
