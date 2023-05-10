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
        try {
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
                'periode' => date('M Y', strtotime($year . '-' . $month . '-1')),
                'type' => $type,
                'data' => $data_balance
            ];

            if (!empty($data["data"])) {
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
        }
        catch (\Exception $e) {
            $message = "Failed to print general ledger for pdf";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result"=>False,
                "message"=>$message
            ]);
        }
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
        if($type == 'recap'){
            $data_balance = $this->getSummaryBalance($id_cabang, $tahun, $bulan);
        }else if($type == 'detail'){
            $data_balance = $this->getDetailBalance($id_cabang, $tahun, $bulan);
        }else{
            $data_balance = $this->getInitBalance($id_cabang, $tahun, $bulan);
        }

        return $data_balance;
    }

    private function getSummaryBalance($id_cabang, $tahun, $bulan){
        $data = Akun::selectRaw('
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(total_summary, 0)) as total
            ')
            ->leftJoin(DB::raw('(
                SELECT id_akun, sum(total) AS total_summary
                FROM
                    (
                    SELECT id_akun, sum( debet - credit ) AS total
                    FROM
                        jurnal_header a
                        INNER JOIN jurnal_detail b ON a.id_jurnal = b.id_jurnal
                    WHERE
                        void = 0
                        AND YEAR ( tanggal_jurnal ) = ' . $tahun . '
                        AND MONTH ( tanggal_jurnal ) = ' . $bulan . '
                        AND a.id_cabang = ' . $id_cabang . '
                    GROUP BY id_akun
                    UNION ALL
                    SELECT id_akun, sum( debet - credit ) AS total
                    FROM
                        saldo_balance sb
                    WHERE
                        tahun = ' . $tahun . '
                        AND bulan = ' . $bulan . '
                        AND id_cabang = ' . $id_cabang . '
                    GROUP BY id_akun
                ) summary
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 0)
            ->where('master_akun.id_cabang', $id_cabang)
            ->groupBy('new_header1', 'new_header2', 'new_header3')
            ->get();

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];

            if($newHeader1 == ""){
                $newHeader1 = "00. Header1";
            }

            if($newHeader2 == ""){
                $newHeader2 = "00. Header2";
            }

            if($newHeader3 == ""){
                $newHeader3 = "00. Header3";
            }

            // Add new_header1 as a parent
            if (!isset($map[$newHeader1])) {
                $map[$newHeader1] = [
                    'header' => $newHeader1,
                    'total' => 0,
                    'children' => []
                ];
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'total' => 0,
                        'children' => []
                    ];
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    $map[$newHeader1]['children'][$newHeader2]['children'][] = [
                        'header' => $newHeader3,
                        'total' => $item['total']
                    ];

                    $map[$newHeader1]['children'][$newHeader2]['total'] += $item['total'];
                }
                $map[$newHeader1]['total'] += $item['total'];

            } else {
                // Add new_header3 as a child of new_header1
                if (!empty($newHeader3)) {
                    $map[$newHeader1]['children'][] = [
                        'header' => $newHeader3,
                        'total' => $item['total']
                    ];
                    $map[$newHeader1]['total'] += $item['total'];
                }
            }
        }

        // Convert the hash map to an array
        $data = array_values($map);

        return (object) $data;
    }

    private function getDetailBalance($id_cabang, $tahun, $bulan){
        $data = Akun::selectRaw('
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                IFNULL(total_summary, 0) as total,
                kode_akun,
                nama_akun
            ')
            ->leftJoin(DB::raw('(
                SELECT id_akun, sum(total) AS total_summary
                FROM
                    (
                    SELECT id_akun, sum( debet - credit ) AS total
                    FROM
                        jurnal_header a
                        INNER JOIN jurnal_detail b ON a.id_jurnal = b.id_jurnal
                    WHERE
                        void = 0
                        AND YEAR ( tanggal_jurnal ) = ' . $tahun . '
                        AND MONTH ( tanggal_jurnal ) = ' . $bulan . '
                        AND a.id_cabang = ' . $id_cabang . '
                    GROUP BY id_akun
                    UNION ALL
                    SELECT id_akun, sum( debet - credit ) AS total
                    FROM
                        saldo_balance sb
                    WHERE
                        tahun = ' . $tahun . '
                        AND bulan = ' . $bulan . '
                        AND id_cabang = ' . $id_cabang . '
                    GROUP BY id_akun
                ) summary
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 0)
            ->where('master_akun.id_cabang', $id_cabang)
            ->groupBy('new_header1', 'new_header2', 'new_header3', 'master_akun.id_akun')
            ->get();

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];
            $newHeader4 = $item['kode_akun'] . '.' . $item['nama_akun'] ;

            if($newHeader1 == ""){
                $newHeader1 = "00. Header1";
            }

            if($newHeader2 == ""){
                $newHeader2 = "00. Header2";
            }

            if($newHeader3 == ""){
                $newHeader3 = "00. Header3";
            }

            // Add new_header1 as a parent
            if (!isset($map[$newHeader1])) {
                $map[$newHeader1] = [
                    'header' => $newHeader1,
                    'total' => 0,
                    'children' => []
                ];
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'total' => 0,
                        'children' => []
                    ];
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    if (!isset($map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3])) {
                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3] = [
                            'header' => $newHeader3,
                            'total' => 0,
                            'children' => []
                        ];
                    }

                    // Add new_header4 as a child of new_header3
                    if (!empty($newHeader4)) {
                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['children'][] = [
                            'header' => $newHeader4,
                            'total' => $item['total']
                        ];

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total'] += $item['total'];
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total'] += $item['total'];
                }else{
                    // Add new_header3 as a child of new_header1
                    if (!empty($newHeader3)) {
                        $map[$newHeader1]['children'][] = [
                            'header' => $newHeader3,
                            'total' => $item['total']
                        ];
                        $map[$newHeader1]['children'][$newHeader2]['total'] += $item['total'];
                    }
                }
                $map[$newHeader1]['total'] += $item['total'];

            } else {
                // maybe never execute
                // Add new_header4 as a child of new_header1
                if (!empty($newHeader4)) {
                    $map[$newHeader1]['children'][] = [
                        'header' => $newHeader4,
                        'total' => $item['total']
                    ];
                    $map[$newHeader1]['total'] += $item['total'];
                }
            }
        }

        // Convert the hash map to an array
        $data = array_values($map);

        return (object) $data;
    }

    private function getInitBalance($id_cabang, $tahun, $bulan){
        $data = Akun::selectRaw('
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(total_summary, 0)) as total
            ')
            ->leftJoin(DB::raw('(
                SELECT id_akun, sum( debet - credit ) AS total_summary
                FROM
                    saldo_balance sb
                WHERE
                    tahun = ' . $tahun . '
                    AND bulan = ' . $bulan . '
                    AND id_cabang = ' . $id_cabang .  '
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 0)
            ->where('master_akun.id_cabang', $id_cabang)
            ->groupBy('new_header1', 'new_header2', 'new_header3')
            ->get();

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];

            if($newHeader1 == ""){
                $newHeader1 = "00. Header1";
            }

            if($newHeader2 == ""){
                $newHeader2 = "00. Header2";
            }

            if($newHeader3 == ""){
                $newHeader3 = "00. Header3";
            }

            // Add new_header1 as a parent
            if (!isset($map[$newHeader1])) {
                $map[$newHeader1] = [
                    'header' => $newHeader1,
                    'total' => 0,
                    'children' => []
                ];
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'total' => 0,
                        'children' => []
                    ];
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    $map[$newHeader1]['children'][$newHeader2]['children'][] = [
                        'header' => $newHeader3,
                        'total' => $item['total']
                    ];

                    $map[$newHeader1]['children'][$newHeader2]['total'] += $item['total'];
                }
                $map[$newHeader1]['total'] += $item['total'];

            } else {
                // Add new_header3 as a child of new_header1
                if (!empty($newHeader3)) {
                    $map[$newHeader1]['children'][] = [
                        'header' => $newHeader3,
                        'total' => $item['total']
                    ];
                    $map[$newHeader1]['total'] += $item['total'];
                }
            }
        }

        // Convert the hash map to an array
        $data = array_values($map);

        return (object) $data;
    }
}
