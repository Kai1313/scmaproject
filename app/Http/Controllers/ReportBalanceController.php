<?php

namespace App\Http\Controllers;

use App\Exports\ReportBalanceExport;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\SaldoBalance;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Excel;
use Illuminate\Support\Facades\DB;
use PDF;
use Psy\Util\Json;

class ReportBalanceController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'report_balance', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = getCabang();

        $data_cabang = $data_cabang->toArray();

        if (count($data_cabang) > 1) {
            $all = (object) [
                    "id_cabang" => "",
                    "nama_cabang" => "ALL",
                    "kode_cabang" => "ALL"
                ];
            array_unshift($data_cabang, $all);
        }

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
        } catch (\Exception $e) {
            $message = "Failed to get populate balance for view";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => False,
                "message" => $message
            ]);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            // Init Data
            $id_cabang = $request->id_cabang;
            $month = $request->month;
            $year = $request->year;
            $type = $request->type;
            $monthName = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

            $data_balance = $this->getData($id_cabang, $year, $month, $type);

            if($id_cabang == null){
                $nama_cabang = 'all';
                $list_cabang = $data_balance['cabang'];
                $data_balance = $data_balance['data'];
            }else{
                $data_cabang = Cabang::find($id_cabang);
                $nama_cabang = $data_cabang->nama_cabang;
                $list_cabang = null;
            }

            $data = [
                'nama_cabang' => $nama_cabang,
                'list_cabang' => $list_cabang,
                'periode_table' => $monthName[$month-1] . ' ' . $year,
                'periode' => date('M Y', strtotime($year . '-' . $month . '-1')),
                'type' => ucwords(str_replace('_', ' ', $type)),
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
                    "result" => True,
                    "pdfData" => base64_encode($pdf->output()),
                    "pdfHeaders" => $headers,
                ]);
            } else {
                return response()->json([
                    "result" => False,
                    "message" => "Tidak ada data"
                ]);
            }
        } catch (\Exception $e) {
            $message = "Failed to print general ledger for pdf";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => False,
                "message" => $message
            ]);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
        // Init Data
        $id_cabang = $request->id_cabang;
        $month = $request->month;
        $year = $request->year;
        $type = $request->type;
        $monthName = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        $data_balance = $this->getData($id_cabang, $year, $month, $type);

        if($id_cabang == null){
            $nama_cabang = 'all';
            $list_cabang = $data_balance['cabang'];
            $data_balance = $data_balance['data'];
        }else{
            $data_cabang = Cabang::find($id_cabang);
            $nama_cabang = $data_cabang->nama_cabang;
            $list_cabang = null;
        }

        // Log::debug(json_encode($data_balance));
        $data = [
            'nama_cabang' => $nama_cabang,
            'list_cabang' => $list_cabang,
            'periode_table' => $monthName[$month-1] . ' ' . $year,
            'periode' => date('M Y', strtotime($year . '-' . $month . '-1')),
            'type' => ucwords(str_replace('_', ' ', $type)),
            'data' => $data_balance
        ];

        // dd(count($data["data"]));
        if (!empty($data["data"])) {
            return Excel::download(new ReportBalanceExport($data), 'ReportBalance.xlsx');
        } else {
            return response()->json([
                "result" => False,
                "message" => "Tidak ada data"
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

    private function getData($id_cabang, $tahun, $bulan, $type)
    {
        if($id_cabang == null){
            if ($type == 'recap') {
                $data_balance = $this->getSummaryBalanceKonsolidasi($tahun, $bulan);
            } else if ($type == 'detail') {
                $data_balance = $this->getDetailBalanceKonsolidasi($tahun, $bulan);
            } else if($type == 'awal') {
                $data_balance = $this->getInitBalanceKonsolidasi($tahun, $bulan);
            }else{
                $data_balance = $this->getInitDetailBalanceKonsolidasi($tahun, $bulan);
            }
        }else{
            if ($type == 'recap') {
                $data_balance = $this->getSummaryBalance($id_cabang, $tahun, $bulan);
            } else if ($type == 'detail') {
                $data_balance = $this->getDetailBalance($id_cabang, $tahun, $bulan);
            } else if($type == 'awal') {
                $data_balance = $this->getInitBalance($id_cabang, $tahun, $bulan);
            }else{
                $data_balance = $this->getInitDetailBalance($id_cabang, $tahun, $bulan);
            }
        }

        return $data_balance;
    }

    private function getSummaryBalance($id_cabang, $tahun, $bulan)
    {
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

            if ($newHeader1 == "") {
                $newHeader1 = "00. Header1";
            }

            if ($newHeader2 == "") {
                $newHeader2 = "00. Header2";
            }

            if ($newHeader3 == "") {
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

    private function getSummaryBalanceKonsolidasi($tahun, $bulan)
    {
        $data_cabang = Cabang::where('status_cabang', 1);
        $list_cabang = $data_cabang->selectRaw('*, REPLACE(LOWER(nama_cabang), " ", "_") as new_nama_cabang')->pluck('new_nama_cabang');
        $data_cabang = $data_cabang->get();

        $data_konsolidasi = [];
        $total_konsolidasi = [];
        $urutan_cabang = 1;

        foreach($data_cabang as $cabang){
            $format_nama =  str_replace(' ', '_', strtolower($cabang->nama_cabang));
            $select_query = '
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(total_summary, 0)) as total_' . $format_nama;

            $data = Akun::selectRaw($select_query)
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
                        AND a.id_cabang = ' . $cabang->id_cabang . '
                    GROUP BY id_akun
                    UNION ALL
                    SELECT id_akun, sum( debet - credit ) AS total
                    FROM
                        saldo_balance sb
                    WHERE
                        tahun = ' . $tahun . '
                        AND bulan = ' . $bulan . '
                        AND id_cabang = ' . $cabang->id_cabang . '
                    GROUP BY id_akun
                ) summary
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 0)
            ->groupBy('new_header1', 'new_header2', 'new_header3')
            ->get()->toArray();

            if($urutan_cabang == 1){
                $data_konsolidasi = $data;
                for($i = 0; $i < count($data_konsolidasi); $i++){
                    $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['total_' . $format_nama];
                }
            }else{
                for($i = 0; $i < count($data_konsolidasi); $i++){
                    $data_konsolidasi[$i]['total_' . $format_nama] = $data[$i]['total_' . $format_nama];
                    $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['total_' . $format_nama];
                }
            }
            $urutan_cabang++;
        }

        for($i = 0; $i < count($data_konsolidasi); $i++){
            $data_konsolidasi[$i]['total_all'] = $total_konsolidasi[$i]['total_all'];
        }

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data_konsolidasi as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];

            if ($newHeader1 == "") {
                $newHeader1 = "00. Header1";
            }

            if ($newHeader2 == "") {
                $newHeader2 = "00. Header2";
            }

            if ($newHeader3 == "") {
                $newHeader3 = "00. Header3";
            }

            // Add new_header1 as a parent
            if (!isset($map[$newHeader1])) {
                $map[$newHeader1] = [
                    'header' => $newHeader1,
                    'children' => []
                ];

                foreach($list_cabang as $cabang){
                    $map[$newHeader1]['total_' . $cabang] = 0;
                }

                $map[$newHeader1]['total_all'] = 0;
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'children' => []
                    ];

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang] = 0;
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] = 0;
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    // input child 3
                    $array_item = [
                        'header' => $newHeader3
                    ];

                    foreach($list_cabang as $cabang){
                        $array_item['total_' . $cabang] =  $item['total_' . $cabang];
                    }

                    $array_item['total_all'] =  $item['total_all'];

                    $map[$newHeader1]['children'][$newHeader2]['children'][] = $array_item;
                    // end

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang] +=  $item['total_' . $cabang];
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] += $item['total_all'];
                }

                foreach($list_cabang as $cabang){
                    $map[$newHeader1]['total_' . $cabang] +=  $item['total_' . $cabang];
                }

                $map[$newHeader1]['total_all'] += $item['total_all'];

            } else {
                // Add new_header3 as a child of new_header1
                if (!empty($newHeader3)) {
                    $map[$newHeader1]['children'][] = [
                        'header' => $newHeader3
                    ];

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['children'][]['total_' . $cabang] +=  $item['total_' . $cabang];
                    }
                    $map[$newHeader1]['total_all'] += $item['total_all'];
                }
            }
        }

        // Convert the hash map to an array
        $data = [
            'data' => (object) array_values($map),
            'cabang' => $list_cabang
        ];

        return $data;
    }

    private function getDetailBalance($id_cabang, $tahun, $bulan)
    {
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
            $newHeader4 = $item['kode_akun'] . '.' . $item['nama_akun'];

            if ($newHeader1 == "") {
                $newHeader1 = "00. Header1";
            }

            if ($newHeader2 == "") {
                $newHeader2 = "00. Header2";
            }

            if ($newHeader3 == "") {
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
                } else {
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

    private function getDetailBalanceKonsolidasi($tahun, $bulan)
    {
        $data_cabang = Cabang::where('status_cabang', 1);
        $list_cabang = $data_cabang->selectRaw('*, REPLACE(LOWER(nama_cabang), " ", "_") as new_nama_cabang')->pluck('new_nama_cabang');
        $data_cabang = $data_cabang->get();

        $data_konsolidasi = [];
        $total_konsolidasi = [];
        $urutan_cabang = 1;

        foreach($data_cabang as $cabang){
            $format_nama =  str_replace(' ', '_', strtolower($cabang->nama_cabang));
            $select_query = '
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(total_summary, 0)) as total_' . $format_nama .',
                kode_akun,
                nama_akun';

            $data = Akun::selectRaw($select_query)
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
                        AND a.id_cabang =  ' . $cabang->id_cabang . '
                    GROUP BY id_akun
                    UNION ALL
                    SELECT id_akun, sum( debet - credit ) AS total
                    FROM
                        saldo_balance sb
                    WHERE
                        tahun = ' . $tahun . '
                        AND bulan = ' . $bulan . '
                        AND id_cabang =  ' . $cabang->id_cabang . '
                    GROUP BY id_akun
                ) summary
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 0)
            ->groupBy('new_header1', 'new_header2', 'new_header3', 'master_akun.id_akun')
            ->get()->toArray();

            if($urutan_cabang == 1){
                $data_konsolidasi = $data;
                for($i = 0; $i < count($data_konsolidasi); $i++){
                    $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['total_' . $format_nama];
                }
            }else{
                for($i = 0; $i < count($data_konsolidasi); $i++){
                    $data_konsolidasi[$i]['total_' . $format_nama] = $data[$i]['total_' . $format_nama];
                    $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['total_' . $format_nama];
                }
            }
            $urutan_cabang++;
        }

        for($i = 0; $i < count($data_konsolidasi); $i++){
            $data_konsolidasi[$i]['total_all'] = $total_konsolidasi[$i]['total_all'];
        }

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data_konsolidasi as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];
            $newHeader4 = $item['kode_akun'] . '.' . $item['nama_akun'];

            if ($newHeader1 == "") {
                $newHeader1 = "00. Header1";
            }

            if ($newHeader2 == "") {
                $newHeader2 = "00. Header2";
            }

            if ($newHeader3 == "") {
                $newHeader3 = "00. Header3";
            }

            // Add new_header1 as a parent
            if (!isset($map[$newHeader1])) {
                $map[$newHeader1] = [
                    'header' => $newHeader1,
                    'children' => []
                ];

                foreach($list_cabang as $cabang){
                    $map[$newHeader1]['total_' . $cabang] = 0;
                }

                $map[$newHeader1]['total_all'] = 0;
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'children' => []
                    ];

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang] = 0;
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] = 0;
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    if (!isset($map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3])) {
                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3] = [
                            'header' => $newHeader3,
                            'children' => []
                        ];

                        foreach($list_cabang as $cabang){
                            $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_' . $cabang] = 0;
                        }

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_all'] = 0;
                    }

                    // Add new_header4 as a child of new_header3
                    if (!empty($newHeader4)) {
                        $array_item = [
                            'header' => $newHeader4,
                        ];

                        foreach($list_cabang as $cabang){
                            $array_item['total_' . $cabang] =  $item['total_' . $cabang];
                        }

                        $array_item['total_all'] =  $item['total_all'];

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['children'][] = $array_item;

                        foreach($list_cabang as $cabang){
                            $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_' . $cabang] +=  $item['total_' . $cabang];
                        }

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_all'] += $item['total_all'];
                    }

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang] +=  $item['total_' . $cabang];
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] += $item['total_all'];
                } else {
                    // Add new_header3 as a child of new_header1
                    if (!empty($newHeader3)) {
                        $array_item = [
                            'header' => $newHeader3,
                        ];

                        foreach($list_cabang as $cabang){
                            $array_item['total_' . $cabang] =  $item['total_' . $cabang];
                        }

                        $array_item['total_all'] =  $item['total_all'];

                        $map[$newHeader1]['children'][] = $array_item;

                        foreach($list_cabang as $cabang){
                            $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang] +=  $item['total_' . $cabang];
                        }

                        $map[$newHeader1]['children'][$newHeader2]['total_all'] += $item['total_all'];
                    }
                }


                foreach($list_cabang as $cabang){
                    $map[$newHeader1]['total_' . $cabang] +=  $item['total_' . $cabang];
                }

                $map[$newHeader1]['total_all'] += $item['total_all'];
            } else {
                // maybe never execute
                // Add new_header4 as a child of new_header1
                if (!empty($newHeader4)) {
                    $array_item = [
                        'header' => $newHeader4,
                    ];

                    foreach($list_cabang as $cabang){
                        $array_item['total_' . $cabang] =  $item['total_' . $cabang];
                    }

                    $array_item['total_all'] =  $item['total_all'];

                    $map[$newHeader1]['children'][] = $array_item;

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['total_' . $cabang] +=  $item['total_' . $cabang];
                    }

                    $map[$newHeader1]['total_all'] += $item['total_all'];
                }
            }
        }

        // Convert the hash map to an array
        $data = [
            'data' => (object) array_values($map),
            'cabang' => $list_cabang
        ];

        return $data;
    }

    private function getInitBalance($id_cabang, $tahun, $bulan)
    {
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

            if ($newHeader1 == "") {
                $newHeader1 = "00. Header1";
            }

            if ($newHeader2 == "") {
                $newHeader2 = "00. Header2";
            }

            if ($newHeader3 == "") {
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

    private function getInitBalanceKonsolidasi($tahun, $bulan)
    {
        $data_cabang = Cabang::where('status_cabang', 1);
        $list_cabang = $data_cabang->selectRaw('*, REPLACE(LOWER(nama_cabang), " ", "_") as new_nama_cabang')->pluck('new_nama_cabang');
        $data_cabang = $data_cabang->get();

        $data_konsolidasi = [];
        $total_konsolidasi = [];
        $urutan_cabang = 1;

        foreach($data_cabang as $cabang){
            $format_nama =  str_replace(' ', '_', strtolower($cabang->nama_cabang));
            $select_query = '
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(total_summary, 0)) as total_' . $format_nama;

            $data = Akun::selectRaw($select_query)
                ->leftJoin(DB::raw('(
                    SELECT id_akun, sum( debet - credit ) AS total_summary
                    FROM
                        saldo_balance sb
                    WHERE
                        tahun = ' . $tahun . '
                        AND bulan = ' . $bulan . '
                        AND id_cabang = ' . $cabang->id_cabang .  '
                    GROUP BY id_akun
                ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
                ->where('isshown', 1)
                ->where('tipe_akun', 0)
                ->groupBy('new_header1', 'new_header2', 'new_header3')
                ->get()->toArray();

            if($urutan_cabang == 1){
                $data_konsolidasi = $data;
                for($i = 0; $i < count($data_konsolidasi); $i++){
                    $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['total_' . $format_nama];
                }
            }else{
                for($i = 0; $i < count($data_konsolidasi); $i++){
                    $data_konsolidasi[$i]['total_' . $format_nama] = $data[$i]['total_' . $format_nama];
                    $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['total_' . $format_nama];
                }
            }
            $urutan_cabang++;
        }

        for($i = 0; $i < count($data_konsolidasi); $i++){
            $data_konsolidasi[$i]['total_all'] = $total_konsolidasi[$i]['total_all'];
        }

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data_konsolidasi as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];

            if ($newHeader1 == "") {
                $newHeader1 = "00. Header1";
            }

            if ($newHeader2 == "") {
                $newHeader2 = "00. Header2";
            }

            if ($newHeader3 == "") {
                $newHeader3 = "00. Header3";
            }

            // Add new_header1 as a parent
            if (!isset($map[$newHeader1])) {
                $map[$newHeader1] = [
                    'header' => $newHeader1,
                    'children' => []
                ];

                foreach($list_cabang as $cabang){
                    $map[$newHeader1]['total_' . $cabang] = 0;
                }

                $map[$newHeader1]['total_all'] = 0;
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'children' => []
                    ];

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang] = 0;
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] = 0;
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    // input child 3
                    $array_item = [
                        'header' => $newHeader3
                    ];

                    foreach($list_cabang as $cabang){
                        $array_item['total_' . $cabang] =  $item['total_' . $cabang];
                    }

                    $array_item['total_all'] =  $item['total_all'];

                    $map[$newHeader1]['children'][$newHeader2]['children'][] = $array_item;
                    // end

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang] +=  $item['total_' . $cabang];
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] += $item['total_all'];
                }

                foreach($list_cabang as $cabang){
                    $map[$newHeader1]['total_' . $cabang] +=  $item['total_' . $cabang];
                }

                $map[$newHeader1]['total_all'] += $item['total_all'];

            } else {
                // Add new_header3 as a child of new_header1
                if (!empty($newHeader3)) {
                    $map[$newHeader1]['children'][] = [
                        'header' => $newHeader3
                    ];

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['children'][]['total_' . $cabang] +=  $item['total_' . $cabang];
                    }
                    $map[$newHeader1]['total_all'] += $item['total_all'];
                }
            }
        }

        // Convert the hash map to an array
        $data = [
            'data' => (object) array_values($map),
            'cabang' => $list_cabang
        ];

        return $data;
    }

    private function getInitDetailBalance($id_cabang, $tahun, $bulan)
    {
        $data = Akun::selectRaw('
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(total_summary, 0)) as total,
                kode_akun,
                nama_akun
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
            ->groupBy('new_header1', 'new_header2', 'new_header3', 'master_akun.id_akun')
            ->get();

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];
            $newHeader4 = $item['kode_akun'] . '.' . $item['nama_akun'];

            if ($newHeader1 == "") {
                $newHeader1 = "00. Header1";
            }

            if ($newHeader2 == "") {
                $newHeader2 = "00. Header2";
            }

            if ($newHeader3 == "") {
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
                } else {
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

    private function getInitDetailBalanceKonsolidasi($tahun, $bulan)
    {
        $data_cabang = Cabang::where('status_cabang', 1);
        $list_cabang = $data_cabang->selectRaw('*, REPLACE(LOWER(nama_cabang), " ", "_") as new_nama_cabang')->pluck('new_nama_cabang');
        $data_cabang = $data_cabang->get();

        $data_konsolidasi = [];
        $total_konsolidasi = [];
        $urutan_cabang = 1;

        foreach($data_cabang as $cabang){
            $format_nama =  str_replace(' ', '_', strtolower($cabang->nama_cabang));
            $select_query = '
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(total_summary, 0)) as total_' . $format_nama .',
                kode_akun,
                nama_akun';

            $data = Akun::selectRaw($select_query)
            ->leftJoin(DB::raw('(
                SELECT id_akun, sum( debet - credit ) AS total_summary
                FROM
                    saldo_balance sb
                WHERE
                    tahun = ' . $tahun . '
                    AND bulan = ' . $bulan . '
                    AND id_cabang = ' . $cabang->id_cabang .  '
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 0)
            ->groupBy('new_header1', 'new_header2', 'new_header3', 'master_akun.id_akun')
            ->get()->toArray();

            if($urutan_cabang == 1){
                $data_konsolidasi = $data;
                for($i = 0; $i < count($data_konsolidasi); $i++){
                    $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['total_' . $format_nama];
                }
            }else{
                for($i = 0; $i < count($data_konsolidasi); $i++){
                    $data_konsolidasi[$i]['total_' . $format_nama] = $data[$i]['total_' . $format_nama];
                    $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['total_' . $format_nama];
                }
            }
            $urutan_cabang++;
        }

        for($i = 0; $i < count($data_konsolidasi); $i++){
            $data_konsolidasi[$i]['total_all'] = $total_konsolidasi[$i]['total_all'];
        }

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data_konsolidasi as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];
            $newHeader4 = $item['kode_akun'] . '.' . $item['nama_akun'];

            if ($newHeader1 == "") {
                $newHeader1 = "00. Header1";
            }

            if ($newHeader2 == "") {
                $newHeader2 = "00. Header2";
            }

            if ($newHeader3 == "") {
                $newHeader3 = "00. Header3";
            }

            // Add new_header1 as a parent
            if (!isset($map[$newHeader1])) {
                $map[$newHeader1] = [
                    'header' => $newHeader1,
                    'children' => []
                ];

                foreach($list_cabang as $cabang){
                    $map[$newHeader1]['total_' . $cabang] = 0;
                }

                $map[$newHeader1]['total_all'] = 0;
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'children' => []
                    ];

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang] = 0;
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] = 0;
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    if (!isset($map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3])) {
                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3] = [
                            'header' => $newHeader3,
                            'children' => []
                        ];

                        foreach($list_cabang as $cabang){
                            $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_' . $cabang] = 0;
                        }

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_all'] = 0;
                    }

                    // Add new_header4 as a child of new_header3
                    if (!empty($newHeader4)) {
                        $array_item = [
                            'header' => $newHeader4,
                        ];

                        foreach($list_cabang as $cabang){
                            $array_item['total_' . $cabang] =  $item['total_' . $cabang];
                        }

                        $array_item['total_all'] =  $item['total_all'];

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['children'][] = $array_item;

                        foreach($list_cabang as $cabang){
                            $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_' . $cabang] +=  $item['total_' . $cabang];
                        }

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_all'] += $item['total_all'];
                    }

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang] +=  $item['total_' . $cabang];
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] += $item['total_all'];
                } else {
                    // Add new_header3 as a child of new_header1
                    if (!empty($newHeader3)) {
                        $array_item = [
                            'header' => $newHeader3,
                        ];

                        foreach($list_cabang as $cabang){
                            $array_item['total_' . $cabang] =  $item['total_' . $cabang];
                        }

                        $array_item['total_all'] =  $item['total_all'];

                        $map[$newHeader1]['children'][] = $array_item;

                        foreach($list_cabang as $cabang){
                            $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang] +=  $item['total_' . $cabang];
                        }

                        $map[$newHeader1]['children'][$newHeader2]['total_all'] += $item['total_all'];
                    }
                }


                foreach($list_cabang as $cabang){
                    $map[$newHeader1]['total_' . $cabang] +=  $item['total_' . $cabang];
                }

                $map[$newHeader1]['total_all'] += $item['total_all'];
            } else {
                // maybe never execute
                // Add new_header4 as a child of new_header1
                if (!empty($newHeader4)) {
                    $array_item = [
                        'header' => $newHeader4,
                    ];

                    foreach($list_cabang as $cabang){
                        $array_item['total_' . $cabang] =  $item['total_' . $cabang];
                    }

                    $array_item['total_all'] =  $item['total_all'];

                    $map[$newHeader1]['children'][] = $array_item;

                    foreach($list_cabang as $cabang){
                        $map[$newHeader1]['total_' . $cabang] +=  $item['total_' . $cabang];
                    }

                    $map[$newHeader1]['total_all'] += $item['total_all'];
                }
            }
        }

        // Convert the hash map to an array
        $data = [
            'data' => (object) array_values($map),
            'cabang' => $list_cabang
        ];

        return $data;
    }
}
