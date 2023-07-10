<?php

namespace App\Http\Controllers;

use App\Exports\ReportProfitAndLossExport;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\SaldoBalance;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Excel;
use Illuminate\Support\Facades\DB;
use PDF;

class ReportProfitAndLossController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'report_profit_loss', 'show') == false) {
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
            "pageTitle" => "SCA Accounting | Report Laba Rugi",
            "data_cabang" => $data_cabang,
        ];

        return view('accounting.report.profit_loss.index', $data);
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
            $message = "Failed to get populate profit and loss for view";
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
                $pdf = PDF::loadView('accounting.report.profit_loss.print', $data);
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
        // try {
        // dd($request->all());
        // Init Data
        $id_cabang = $request->id_cabang;
        $month = $request->month;
        $year = $request->year;
        $type = $request->type;

        $data_cabang = Cabang::find($id_cabang);
        $nama_cabang = $data_cabang->nama_cabang;

        $data_profit_loss = $this->getData($id_cabang, $year, $month, $type);

        // Log::debug(json_encode($data_profit_loss));
        $data = [
            'cabang' => $nama_cabang,
            'periode' => date('M Y', strtotime($year . '-' . $month . '-1')),
            'type' => $type,
            'data' => $data_profit_loss
        ];

        // dd(count($data["data"]));
        if (!empty($data["data"])) {
            return Excel::download(new ReportProfitAndLossExport($data), 'ReportProfitLoss.xlsx');
        } else {
            return response()->json([
                "result" => False,
                "message" => "Tidak ada data"
            ]);
        }
        // }
        // catch (\Exception $e) {
        //     $message = "Failed to print general ledger for excel";
        //     Log::error($message);
        //     Log::error($e);
        //     return response()->json([
        //         "result"=>False,
        //         "message"=>$message
        //     ]);
        // }
    }

    private function getData($id_cabang, $tahun, $bulan, $type)
    {
        if ($type == 'recap') {
            $data_balance = $this->getSummaryProfitLoss($id_cabang, $tahun, $bulan);
        } else if ($type == 'detail') {
            $data_balance = $this->getDetailProfitLoss($id_cabang, $tahun, $bulan);
        }

        return $data_balance;
    }

    private function getSummaryProfitLoss($id_cabang, $tahun, $bulan)
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
            ->where('tipe_akun', 1)
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

    private function getDetailProfitLoss($id_cabang, $tahun, $bulan)
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
            ->where('tipe_akun', 1)
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
}
