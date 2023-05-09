<?php

namespace App\Http\Controllers;

use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\SaldoBalance;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Excel;
use Illuminate\Support\Facades\DB;
use PDF;

class ReportBalanceController extends Controller
{
    public function index(Request $request)
    {
        // if (checkUserSession($request, 'general_ledger', 'show') == false) {
        //     return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        // }

        $data_cabang = Cabang::all();

        // $this->getData(1, 2023, 2);

        $data = [
            "pageTitle" => "SCA Accounting | Report Neraca",
            "data_cabang" => $data_cabang,
        ];

        return view('accounting.report.balance.index', $data);
    }

    public function populate(Request $request)
    {
        try {
            // dd($request->all());
            // Init Data
            $id_cabang = $request->id_cabang;
            $month = $request->month;
            $year = $request->year;
            $type = $request->type;

            $data = $this->getData($id_cabang, $year, $month, $type);
            Log::debug(json_encode($data));

            return response()->json([
                "result" => true,
                "data" => $data,
            ]);
        }
        catch (\Exception $e) {
            $message = "Failed to get populate balance for view";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result"=>False,
                "message"=>$message
            ]);
        }
    }

    public function exportPdf(Request $request)
    {
        // try {
            // dd($request->all());
            // Init Data
            Log::debug('test pdf');
            $id_cabang = $request->id_cabang;
            $month = $request->month;
            $year = $request->year;
            $type = $request->type;

            $data_cabang = Cabang::find($id_cabang);
            $nama_cabang = $data_cabang->nama_cabang;

            $data_balance = $this->getData($id_cabang, $year, $month, $type);

            Log::debug(json_encode($data_balance));
            $data = [
                'cabang' => $nama_cabang,
                'periode' => $month . '/' . $year,
                'type' => $type,
                'data' => $data_balance
            ];

            if (count($data["data"]) > 0) {
                $pdf = PDF::loadView('accounting.report.balance.print', $data);
                $pdf->setPaper('a4', 'potrait');
                $headers = [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="download.pdf"',
                ];
                return response()->json([
                    "result"=>True,
                    "pdfData"=>base64_encode($pdf->output()),
                    "pdfHeaders"=>$headers,
                ]);
            }
            else {
                return response()->json([
                    "result"=>False,
                    "message"=>"Tidak ada data"
                ]);
            }
        // }
        // catch (\Exception $e) {
        //     $message = "Failed to print general ledger for pdf";
        //     Log::error($message);
        //     Log::error($e);
        //     return response()->json([
        //         "result"=>False,
        //         "message"=>$message
        //     ]);
        // }
    }

    public function exportExcel(Request $request)
    {
        try {
            // dd($request->all());
            // Init Data
            $id_cabang = $request->id_cabang;
            $cabang = Cabang::find($id_cabang);
            $nama_cabang = ($cabang)?$cabang->nama_cabang:NULL;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $type = $request->type;
            $coa = $request->coa;
            $month = date("m", strtotime("-1 month $start_date"));
            $year = date("Y", strtotime($start_date));
            $start_of_the_month = date("Y-m-01", strtotime($start_date));
            $saldo_date = date("Y-m-d", strtotime($start_date." -1 day"));

            $data_ledgers = JurnalDetail::join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                ->whereBetween("jurnal_header.tanggal_jurnal", [$start_date, $end_date]);
            if ($type == "recap") {
                $data_ledgers = $data_ledgers->selectRaw("master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, SUM(jurnal_detail.debet) as debet, SUM(jurnal_detail.credit) as kredit")->groupBy("jurnal_detail.id_akun");
            }
            else {
                $data_ledgers = $data_ledgers->selectRaw("master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, jurnal_header.kode_jurnal, jurnal_detail.keterangan, jurnal_detail.id_transaksi, jurnal_detail.debet as debet, jurnal_detail.credit as kredit, jurnal_header.tanggal_jurnal");
            }
            if ($id_cabang != "all") {
                $data_ledgers = $data_ledgers->where("jurnal_header.id_cabang", $id_cabang);
            }
            if ($coa != "") {
                $data_ledgers = $data_ledgers->where("jurnal_detail.id_akun", $coa);
            }
            if ($type == "recap") {
                $data_ledgers->orderBy("master_akun.kode_akun", "DESC");
            }
            else {
                $data_ledgers->orderBy("jurnal_header.tanggal_jurnal", "DESC");
                $data_ledgers->orderBy("master_akun.kode_akun", "DESC");
            }
            // Get saldo awal dan saldo akhir
            $result = $data_ledgers->get();
            $result_detail = [];
            $saldo_awal_current = '';
            foreach ($result as $key => $value) {
                if ($type == "recap") {
                    $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $value->id_akun)->where("id_cabang", $value->id_cabang)->where("bulan", $month)->where("tahun", $year)->first();
                    $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                    ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                    ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                    ->where("jurnal_detail.id_akun", $value->id_akun)
                    ->where("jurnal_header.id_cabang", $value->id_cabang)
                    ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                    ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                    ->groupBy("jurnal_detail.id_akun")->first();
                    $saldo_debet = ($saldo)?$saldo->saldo_debet:0;
                    $saldo_kredit = ($saldo)?$saldo->saldo_kredit:0;
                    $debet = ($data_saldo_ledgers)?$data_saldo_ledgers->debet:0;
                    $kredit = ($data_saldo_ledgers)?$data_saldo_ledgers->kredit:0;
                    $saldo_awal = ($saldo_debet - $saldo_kredit) + ($debet - $kredit);
                    $saldo_akhir = $saldo_awal + $value->debet - $value->kredit;
                    $value["saldo_awal"] = $saldo_awal;
                    $value["saldo_akhir"] = $saldo_akhir;
                }
                else {
                    if ($saldo_awal_current != $value->id_akun) {
                        $saldo_awal_current = $value->id_akun;
                        $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $value->id_akun)->where("id_cabang", $value->id_cabang)->where("bulan", $month)->where("tahun", $year)->first();
                        $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                        ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                        ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                        ->where("jurnal_detail.id_akun", $value->id_akun)
                        ->where("jurnal_header.id_cabang", $value->id_cabang)
                        ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                        ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                        ->groupBy("jurnal_detail.id_akun")->first();
                        $saldo_debet = ($saldo)?$saldo->saldo_debet:0;
                        $saldo_kredit = ($saldo)?$saldo->saldo_kredit:0;
                        $debet = ($data_saldo_ledgers)?$data_saldo_ledgers->debet:0;
                        $kredit = ($data_saldo_ledgers)?$data_saldo_ledgers->kredit:0;
                        $saldo_awal_debet = $saldo_debet + $debet;
                        $saldo_awal_kredit = $saldo_kredit + $kredit;
                        $saldo_balance = $saldo_awal_debet - $saldo_awal_kredit;
                        $result_detail[] = (object)[
                            "id_cabang"=>$value->id_cabang,
                            "id_akun"=>$value->id_akun,
                            "kode_akun"=>$value->kode_akun,
                            "nama_akun"=>$value->nama_akun,
                            "kode_jurnal"=>"",
                            "id_transaksi"=>"",
                            "keterangan"=>"Saldo Awal",
                            "debet"=>$saldo_awal_debet,
                            "kredit"=>$saldo_awal_kredit,
                            "tanggal_jurnal"=>$saldo_date,
                            "saldo_balance"=>$saldo_balance
                        ];
                    }
                    $saldo_balance = $saldo_balance + $value->debet - $value->kredit;
                    $result_detail[] = (object)[
                        "id_cabang"=>$value->id_cabang,
                        "id_akun"=>$value->id_akun,
                        "kode_akun"=>$value->kode_akun,
                        "nama_akun"=>$value->nama_akun,
                        "kode_jurnal"=>$value->kode_jurnal,
                        "id_transaksi"=>$value->id_transaksi,
                        "keterangan"=>$value->keterangan,
                        "debet"=>$value->debet,
                        "kredit"=>$value->kredit,
                        "tanggal_jurnal"=>$value->tanggal_jurnal,
                        "saldo_balance"=>$saldo_balance
                    ];
                }
            }
            $data = [
                "type"=>$type,
                "start_date"=>$start_date,
                "end_date"=>$end_date,
                "cabang"=>$nama_cabang,
                "data"=>($type == "recap")?$result:$result_detail
            ];
            // dd(count($data["data"]));
            if (count($data["data"]) > 0) {
                return Excel::download(new ReportGeneralLedgerExport($data), 'ReportGeneralLedger.xlsx');
            }
            else {
                return response()->json([
                    "result"=>False,
                    "message"=>"Tidak ada data"
                ]);
            }
        }
        catch (\Exception $e) {
            $message = "Failed to print general ledger for excel";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result"=>False,
                "message"=>$message
            ]);
        }
    }

    private function getData($id_cabang, $tahun, $bulan, $type){
        $data_header1 = Akun::where('header1', '<>', '')
                        ->whereNotNull('header1')
                        ->where('id_cabang', $id_cabang)
                        ->selectRaw('DISTINCT header1')
                        ->orderBy('header1', 'asc')
                        ->pluck('header1');

        $data_header2 = Akun::where('header1', '<>', '')
                        ->whereNotNull('header1')
                        ->where('header2', '<>', '')
                        ->whereNotNull('header2')
                        ->where('id_cabang', $id_cabang)
                        ->select('header1', 'header2')
                        ->groupBy('header2')
                        ->orderBy('header2', 'asc')
                        ->pluck('header1', 'header2');

        $data_header3 = Akun::where('header1', '<>', '')
                        ->whereNotNull('header1')
                        ->where('header2', '<>', '')
                        ->whereNotNull('header2')
                        ->where('header3', '<>', '')
                        ->whereNotNull('header3')
                        ->where('id_cabang', $id_cabang)
                        ->select('header2', 'header3')
                        ->groupBy('header3')
                        ->orderBy('header3', 'asc')
                        ->pluck('header2', 'header3');

        // dd($data_header2);

        $data_summary = [];

        foreach($data_header1 as $header1){
            $header1 = $header1;
            $total_header1 = 0;
            $header2 = [];

            foreach($data_header2 as $key => $value){
                if($value == $header1){
                    $header3 = [];
                    $total_header2 = 0;

                    foreach($data_header3 as $key_header3 => $value_header3){
                        if($value_header3 == $key){
                            if($type == 'recap'){
                                $summary = $this->getSummaryBalance($key_header3, $id_cabang, $tahun, $bulan);
                                if(empty($summary)){
                                    $total = round(0, 2);
                                }else{
                                    $total = round($summary->total, 2);
                                }

                                $total_header2 += $total;

                                array_push($header3, [
                                    'header' => $key_header3,
                                    'total' => $total
                                ]);
                            }else if($type == 'detail'){
                                $detail = $this->getDetailBalance($key_header3, $id_cabang, $tahun, $bulan);
                                $child = [];

                                if(!empty($detail)){
                                    $child = $detail['data'];
                                }

                                $total_header2 += $detail['total_header3'];

                                array_push($header3, [
                                    'header' => $key_header3,
                                    'total' => $detail['total_header3'],
                                    'child' => $child
                                ]);
                            }else{
                                $init = $this->getInitBalance($key_header3, $id_cabang, $tahun, $bulan);
                                if(empty($init)){
                                    $total = round(0, 2);
                                }else{
                                    $total = round($init->total, 2);
                                }

                                $total_header2 += $total;

                                array_push($header3, [
                                    'header' => $key_header3,
                                    'total' => $total
                                ]);
                            }
                        }
                    }

                    $total_header1 += $total_header2;

                    $data_temp_summary = [
                        'header' => $key,
                        'total' => $total_header2,
                        'child' => $header3
                    ];

                    array_push($header2, $data_temp_summary);
                }
            }

            $data = [
                'header' => $header1,
                'total' => $total_header1,
                'child' => $header2
            ];

            array_push($data_summary, $data);
        }

        return $data_summary;
    }

    private function getSummaryBalance($header3, $id_cabang, $tahun, $bulan){
        $data_summary_init = Akun::leftjoin('saldo_balance', 'saldo_balance.id_akun', 'master_akun.id_akun')
                        ->where('saldo_balance.id_cabang', $id_cabang)
                        ->where('master_akun.tipe_akun', 0)
                        ->where('master_akun.header3', $header3)
                        ->where('tahun', $tahun)
                        ->where('bulan', $bulan)
                        ->select('header3', DB::raw('ROUND(IFNULL(SUM(debet-credit), 0), 2) as total'))
                        ->first();

        $data_summary_jurnal = Akun::leftjoin('jurnal_detail as jd', 'jd.id_akun', 'master_akun.id_akun')
                        ->leftjoin('jurnal_header as jh', function($join){
                            $join->on('jh.id_jurnal', 'jd.id_jurnal');
                            $join->on('jh.id_cabang', 'master_akun.id_cabang');
                        })
                        ->where('jh.void', 0)
                        ->where('jh.id_cabang', $id_cabang)
                        ->where('master_akun.tipe_akun', 0)
                        ->where('master_akun.header3', $header3)
                        ->whereRaw('YEAR(jh.tanggal_jurnal) = '. $tahun)
                        ->whereRaw('MONTH(jh.tanggal_jurnal) = '. $bulan)
                        ->select('header3', DB::raw('ROUND(IFNULL(SUM(debet-credit), 0), 2) as total'))
                        ->first();

        $data = [
            "header" => $data_summary_jurnal->header3,
            "total" => round((floatval($data_summary_init->total) + floatval($data_summary_jurnal->total)), 2)
        ];

        return (object) $data;
    }

    private function getDetailBalance($header3, $id_cabang, $tahun, $bulan){
        $data_detail_init = Akun::leftjoin(DB::raw('(
                            SELECT *
                            FROM saldo_balance
                            WHERE id_cabang = ' . $id_cabang . '
                            AND tahun = ' . $tahun . '
                            AND bulan = ' . $bulan . '
                        ) saldo_balance'), 'saldo_balance.id_akun', 'master_akun.id_akun')
                        ->where('master_akun.header3', $header3)
                        ->where('master_akun.tipe_akun', 0)
                        ->groupBy('master_akun.id_akun')
                        ->select('master_akun.kode_akun', 'nama_akun', DB::raw('ROUND(IFNULL(SUM(debet-credit), 0), 2) as total'))
                        ->get();

        $data_detail_jurnal = Akun::leftjoin(DB::raw('(
                            SELECT jd.*
                            FROM jurnal_header jh
                            JOIN jurnal_detail jd on jd.id_jurnal = jh.id_jurnal
                            WHERE jh.void = 0
                            AND jh.id_cabang = ' . $id_cabang . '
                            AND YEAR(tanggal_jurnal) = ' . $tahun . '
                            AND MONTH(tanggal_jurnal) = ' . $bulan . '
                        ) jurnal'), 'jurnal.id_akun', 'master_akun.id_akun')
                        ->where('master_akun.header3', $header3)
                        ->where('master_akun.tipe_akun', 0)
                        ->groupBy('master_akun.id_akun')
                        ->select('master_akun.kode_akun', 'nama_akun', DB::raw('ROUND(IFNULL(SUM(debet-credit), 0), 2) as total'))
                        ->get();

        $data_detail = [];
        $total_header3 = 0;

        for($i = 0; $i < count($data_detail_init); $i++){
            array_push($data_detail, (object) [
                'header' => $data_detail_init[$i]->kode_akun . '.' . $data_detail_init[$i]->nama_akun,
                'total' => round((floatval($data_detail_init[$i]->total) + floatval($data_detail_jurnal[$i]->total)), 2)
            ]);

            $total_header3 += round((floatval($data_detail_init[$i]->total) + floatval($data_detail_jurnal[$i]->total)), 2);
        }

        $data = [
            'data' => $data_detail,
            'total_header3' => $total_header3
        ];

        return $data;
    }

    private function getInitBalance($header3, $id_cabang, $tahun, $bulan){
        $data_summary_init = Akun::leftjoin('saldo_balance', 'saldo_balance.id_akun', 'master_akun.id_akun')
                        ->where('saldo_balance.id_cabang', $id_cabang)
                        ->where('master_akun.tipe_akun', 0)
                        ->where('master_akun.header3', $header3)
                        ->where('tahun', $tahun)
                        ->where('bulan', $bulan)
                        ->select('header3', DB::raw('ROUND(IFNULL(SUM(debet-credit), 0), 2) as total'))
                        ->first();

        $data = [
            "header" => $data_summary_init->header3,
            "total" => round((floatval($data_summary_init->total)), 2)
        ];

        return (object) $data;
    }
}
