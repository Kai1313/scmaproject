<?php

namespace App\Http\Controllers;

use App\Exports\ReportBalanceExport;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use Psy\Util\Json;

class ReportBalanceController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'report/balance', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = getCabang();

        $data_cabang = $data_cabang->toArray();

        if (count($data_cabang) > 1) {
            $all = (object) [
                "id_cabang" => "",
                "nama_cabang" => "ALL",
                "kode_cabang" => "ALL",
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
                "result" => false,
                "message" => $message,
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

            if ($id_cabang == null) {
                $nama_cabang = 'all';
                $list_cabang = $data_balance['cabang'];
                $data_balance = $data_balance['data'];
            } else {
                $data_cabang = Cabang::find($id_cabang);
                $nama_cabang = $data_cabang->nama_cabang;
                $list_cabang = null;
            }

            $data = [
                'nama_cabang' => $nama_cabang,
                'list_cabang' => $list_cabang,
                'periode_table' => $monthName[$month - 1] . ' ' . $year,
                'periode' => date('M Y', strtotime($year . '-' . $month . '-1')),
                'type' => ucwords(str_replace('_', ' ', $type)),
                'data' => $data_balance,
            ];

            if (!empty($data["data"])) {
                $pdf = PDF::loadView('accounting.report.balance.print', $data);
                $pdf->setPaper('a4', 'potrait');
                $headers = [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="download.pdf"',
                ];
                return response()->json([
                    "result" => true,
                    "pdfData" => base64_encode($pdf->output()),
                    "pdfHeaders" => $headers,
                ]);
            } else {
                return response()->json([
                    "result" => false,
                    "message" => "Tidak ada data",
                ]);
            }
        } catch (\Exception $e) {
            $message = "Failed to print general ledger for pdf";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
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

            if ($id_cabang == null) {
                $nama_cabang = 'all';
                $list_cabang = $data_balance['cabang'];
                $data_balance = $data_balance['data'];
            } else {
                $data_cabang = Cabang::find($id_cabang);
                $nama_cabang = $data_cabang->nama_cabang;
                $list_cabang = null;
            }

            // Log::debug(json_encode($data_balance));
            $data = [
                'nama_cabang' => $nama_cabang,
                'list_cabang' => $list_cabang,
                'periode_table' => $monthName[$month - 1] . ' ' . $year,
                'periode' => date('M Y', strtotime($year . '-' . $month . '-1')),
                'type' => str_replace('_', ' ', $type),
                'data' => $data_balance,
            ];

            // dd($data);

            // dd(count($data["data"]));
            if (!empty($data["data"])) {
                return Excel::download(new ReportBalanceExport($data), 'ReportBalance.xlsx');
            } else {
                return response()->json([
                    "result" => false,
                    "message" => "Tidak ada data",
                ]);
            }
        } catch (\Exception $e) {
            $message = "Failed to print general ledger for excel";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    private function getData($id_cabang, $tahun, $bulan, $type)
    {
        if ($id_cabang == null) {
            if ($type == 'recap') {
                $data_balance = $this->getSummaryBalanceKonsolidasi($tahun, $bulan);
            } else if ($type == 'detail') {
                $data_balance = $this->getDetailBalanceKonsolidasi($tahun, $bulan);
            } else if ($type == 'awal') {
                $data_balance = $this->getInitBalanceKonsolidasi($tahun, $bulan);
            } else {
                $data_balance = $this->getInitDetailBalanceKonsolidasi($tahun, $bulan);
            }
        } else {
            if ($type == 'recap') {
                $data_balance = $this->getSummaryBalance($id_cabang, $tahun, $bulan);
            } else if ($type == 'detail') {
                $data_balance = $this->getDetailBalance($id_cabang, $tahun, $bulan);
            } else if ($type == 'awal') {
                $data_balance = $this->getInitBalance($id_cabang, $tahun, $bulan);
            } else {
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
                SUM(IFNULL(sum_posisi_credit, 0)) as sum_posisi_credit,
                SUM(IFNULL(sum_posisi_debet, 0)) as sum_posisi_debet,
                master_akun.posisi_debet
            ')
            ->leftJoin(DB::raw('(
                SELECT id_akun, sum(sum_posisi_credit) AS sum_posisi_credit, sum(sum_posisi_debet) AS sum_posisi_debet
                FROM
                    (
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
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
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
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

        $summary_data = [
            'transaction_data' => $data,
        ];

        $data = $this->getMapSummary($summary_data);

        return (object) $data;
    }

    private function getSummaryBalanceKonsolidasi($tahun, $bulan)
    {
        $data_cabang = Cabang::where('status_cabang', 1)->selectRaw('*, REPLACE(LOWER(nama_cabang), " ", "_") as new_nama_cabang')->get();

        $data_konsolidasi = [];
        $total_konsolidasi = [];
        $urutan_cabang = 1;

        foreach ($data_cabang as $cabang) {
            $format_nama = str_replace(' ', '_', strtolower($cabang->nama_cabang));
            $select_query = '
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(sum_posisi_credit, 0)) as sum_posisi_credit_' . $format_nama .',
                SUM(IFNULL(sum_posisi_debet, 0)) as sum_posisi_debet_' . $format_nama .',
                master_akun.posisi_debet';

            $data = Akun::selectRaw($select_query)
                ->leftJoin(DB::raw('(
                SELECT id_akun, sum(sum_posisi_credit) AS sum_posisi_credit, sum(sum_posisi_debet) AS sum_posisi_debet
                FROM
                    (
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
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
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
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

            if ($urutan_cabang == 1) {
                $data_konsolidasi = $data;
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $posisi_debet = $data_konsolidasi[$i]['posisi_debet'];
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama];
                    } else {
                        $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                    }

                    $total_konsolidasi[$i]['total_credit'] = $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                }
            } else {
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $posisi_debet = $data_konsolidasi[$i]['posisi_debet'];
                    $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama] = $data[$i]['sum_posisi_debet_' . $format_nama];
                    $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama] = $data[$i]['sum_posisi_credit_' . $format_nama];
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama];
                    } else {
                        $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                    }

                    $total_konsolidasi[$i]['total_credit'] += $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                }
            }
            $urutan_cabang++;
        }

        for ($i = 0; $i < count($data_konsolidasi); $i++) {
            $data_konsolidasi[$i]['total_all'] = $total_konsolidasi[$i]['total_all'];
            $data_konsolidasi[$i]['total_credit'] = $total_konsolidasi[$i]['total_credit'];
        }

        $summary_data = [
            'transaction_data' => $data_konsolidasi,
            'list_cabang' => $data_cabang,
        ];

        $map_konsolidasi = $this->getMapSummaryKonsolidasi($summary_data);

        // Convert the hash map to an array
        $data = [
            'data' => $map_konsolidasi,
            'cabang' => $data_cabang,
        ];

        return $data;
    }

    private function getDetailBalance($id_cabang, $tahun, $bulan)
    {
        $data = Akun::selectRaw('
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                kode_akun,
                nama_akun,
                master_akun.id_akun,
                SUM(IFNULL(sum_posisi_credit, 0)) as sum_posisi_credit,
                SUM(IFNULL(sum_posisi_debet, 0)) as sum_posisi_debet,
                master_akun.posisi_debet
            ')
            ->leftJoin(DB::raw('(
                SELECT id_akun, sum(sum_posisi_credit) AS sum_posisi_credit, sum(sum_posisi_debet) AS sum_posisi_debet
                FROM
                    (
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
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
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
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

        $detail_data = [
            'transaction_data' => $data,
            'period' => $tahun . '-' . $bulan,
            'id_cabang' => $id_cabang,
        ];

        $data = $this->getMapDetail($detail_data);

        return (object) $data;
    }

    private function getDetailBalanceKonsolidasi($tahun, $bulan)
    {
        $data_cabang = Cabang::where('status_cabang', 1)->selectRaw('*, nama_cabang, REPLACE(LOWER(nama_cabang), " ", "_") as new_nama_cabang')->get();

        $data_konsolidasi = [];
        $total_konsolidasi = [];
        $urutan_cabang = 1;

        foreach ($data_cabang as $cabang) {
            $format_nama = str_replace(' ', '_', strtolower($cabang->nama_cabang));
            $select_query = '
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(sum_posisi_credit, 0)) as sum_posisi_credit_' . $format_nama .',
                SUM(IFNULL(sum_posisi_debet, 0)) as sum_posisi_debet_' . $format_nama . ',
                master_akun.kode_akun,
                master_akun.nama_akun,
                master_akun.id_akun,
                master_akun.posisi_debet';

            $data = Akun::selectRaw($select_query)
                ->leftJoin(DB::raw('(
                SELECT id_akun, sum(sum_posisi_credit) AS sum_posisi_credit, sum(sum_posisi_debet) AS sum_posisi_debet
                FROM
                    (
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
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
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
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
                ->groupBy('new_header1', 'new_header2', 'new_header3', 'master_akun.kode_akun')
                ->get()->toArray();

            if ($urutan_cabang == 1) {
                $data_konsolidasi = $data;
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $posisi_debet = $data_konsolidasi[$i]['posisi_debet'];
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama];
                    } else {
                        $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                    }

                    $total_konsolidasi[$i]['total_credit'] = $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                }
            } else {
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $posisi_debet = $data_konsolidasi[$i]['posisi_debet'];
                    $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama] = $data[$i]['sum_posisi_debet_' . $format_nama];
                    $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama] = $data[$i]['sum_posisi_credit_' . $format_nama];
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama];
                    } else {
                        $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                    }

                    $total_konsolidasi[$i]['total_credit'] += $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                }
            }
            $urutan_cabang++;
        }

        for ($i = 0; $i < count($data_konsolidasi); $i++) {
            $data_konsolidasi[$i]['total_all'] = $total_konsolidasi[$i]['total_all'];
            $data_konsolidasi[$i]['total_credit'] = $total_konsolidasi[$i]['total_credit'];
        }

        $detail_data = [
            'transaction_data' => $data_konsolidasi,
            'period' => $tahun . '-' . $bulan,
            'list_cabang' => $data_cabang,
        ];

        $map_konsolidasi = $this->getMapDetailKonsolidasi($detail_data);

        // Convert the hash map to an array
        $data = [
            'data' => $map_konsolidasi,
            'cabang' => $data_cabang,
        ];

        return $data;
    }

    private function getInitBalance($id_cabang, $tahun, $bulan)
    {
        $data = Akun::selectRaw('
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(sum_posisi_credit, 0)) as sum_posisi_credit,
                SUM(IFNULL(sum_posisi_debet, 0)) as sum_posisi_debet,
                master_akun.posisi_debet
            ')
            ->leftJoin(DB::raw('(
                SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                FROM
                    saldo_balance sb
                WHERE
                    tahun = ' . $tahun . '
                    AND bulan = ' . $bulan . '
                    AND id_cabang = ' . $id_cabang . '
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 0)
            ->where('master_akun.id_cabang', $id_cabang)
            ->groupBy('new_header1', 'new_header2', 'new_header3')
            ->get();

        $summary_data = [
            'transaction_data' => $data,
        ];

        $data = $this->getMapSummary($summary_data);

        return (object) $data;
    }

    private function getInitBalanceKonsolidasi($tahun, $bulan)
    {
        $data_cabang = Cabang::where('status_cabang', 1)->selectRaw('*, REPLACE(LOWER(nama_cabang), " ", "_") as new_nama_cabang')->get();

        $data_konsolidasi = [];
        $total_konsolidasi = [];
        $urutan_cabang = 1;

        foreach ($data_cabang as $cabang) {
            $format_nama = str_replace(' ', '_', strtolower($cabang->nama_cabang));
            $select_query = '
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(sum_posisi_credit, 0)) as sum_posisi_credit_' . $format_nama .',
                SUM(IFNULL(sum_posisi_debet, 0)) as sum_posisi_debet_' . $format_nama .',
                master_akun.posisi_debet';

            $data = Akun::selectRaw($select_query)
                ->leftJoin(DB::raw('(
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                    FROM
                        saldo_balance sb
                    WHERE
                        tahun = ' . $tahun . '
                        AND bulan = ' . $bulan . '
                        AND id_cabang = ' . $cabang->id_cabang . '
                    GROUP BY id_akun
                ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
                ->where('isshown', 1)
                ->where('tipe_akun', 0)
                ->groupBy('new_header1', 'new_header2', 'new_header3')
                ->get()->toArray();

            if ($urutan_cabang == 1) {
                $data_konsolidasi = $data;
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $posisi_debet = $data_konsolidasi[$i]['posisi_debet'];
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama];
                    } else {
                        $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                    }

                    $total_konsolidasi[$i]['total_credit'] = $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                }
            } else {
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $posisi_debet = $data_konsolidasi[$i]['posisi_debet'];
                    $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama] = $data[$i]['sum_posisi_debet_' . $format_nama];
                    $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama] = $data[$i]['sum_posisi_credit_' . $format_nama];
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama];
                    } else {
                        $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                    }

                    $total_konsolidasi[$i]['total_credit'] += $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                }
            }
            $urutan_cabang++;
        }

        for ($i = 0; $i < count($data_konsolidasi); $i++) {
            $data_konsolidasi[$i]['total_all'] = $total_konsolidasi[$i]['total_all'];
            $data_konsolidasi[$i]['total_credit'] = $total_konsolidasi[$i]['total_credit'];
        }

        $summary_data = [
            'transaction_data' => $data_konsolidasi,
            'list_cabang' => $data_cabang,
        ];

        $map_konsolidasi = $this->getMapSummaryKonsolidasi($summary_data);

        // Convert the hash map to an array
        $data = [
            'data' => $map_konsolidasi,
            'cabang' => $data_cabang,
        ];

        return $data;
    }

    private function getInitDetailBalance($id_cabang, $tahun, $bulan)
    {
        $data = Akun::selectRaw('
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                kode_akun,
                nama_akun,
                master_akun.id_akun,
                SUM(IFNULL(sum_posisi_credit, 0)) as sum_posisi_credit,
                SUM(IFNULL(sum_posisi_debet, 0)) as sum_posisi_debet,
                master_akun.posisi_debet
            ')
            ->leftJoin(DB::raw('(
                SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                FROM
                    saldo_balance sb
                WHERE
                    tahun = ' . $tahun . '
                    AND bulan = ' . $bulan . '
                    AND id_cabang = ' . $id_cabang . '
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 0)
            ->where('master_akun.id_cabang', $id_cabang)
            ->groupBy('new_header1', 'new_header2', 'new_header3', 'master_akun.id_akun')
            ->get();

        // Convert the hash map to an array
        $detail_data = [
            'transaction_data' => $data,
            'period' => $tahun . '-' . $bulan,
            'id_cabang' => $id_cabang,
        ];

        $data = $this->getMapDetail($detail_data);

        return (object) $data;
    }

    private function getInitDetailBalanceKonsolidasi($tahun, $bulan)
    {
        $data_cabang = Cabang::where('status_cabang', 1)->selectRaw('*, REPLACE(LOWER(nama_cabang), " ", "_") as new_nama_cabang')->get();

        $data_konsolidasi = [];
        $total_konsolidasi = [];
        $urutan_cabang = 1;

        foreach ($data_cabang as $cabang) {
            $format_nama = str_replace(' ', '_', strtolower($cabang->nama_cabang));
            $select_query = '
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(sum_posisi_credit, 0)) as sum_posisi_credit_' . $format_nama .',
                SUM(IFNULL(sum_posisi_debet, 0)) as sum_posisi_debet_' . $format_nama . ',
                master_akun.kode_akun,
                master_akun.nama_akun,
                master_akun.id_akun,
                master_akun.posisi_debet';

            $data = Akun::selectRaw($select_query)
                ->leftJoin(DB::raw('(
                SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                FROM
                    saldo_balance sb
                WHERE
                    tahun = ' . $tahun . '
                    AND bulan = ' . $bulan . '
                    AND id_cabang = ' . $cabang->id_cabang . '
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
                ->where('isshown', 1)
                ->where('tipe_akun', 0)
                ->groupBy('new_header1', 'new_header2', 'new_header3', 'master_akun.kode_akun')
                ->get()->toArray();

            if ($urutan_cabang == 1) {
                $data_konsolidasi = $data;
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $posisi_debet = $data_konsolidasi[$i]['posisi_debet'];
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama];
                    } else {
                        $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                    }

                    $total_konsolidasi[$i]['total_credit'] = $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                }
            } else {
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $posisi_debet = $data_konsolidasi[$i]['posisi_debet'];
                    $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama] = $data[$i]['sum_posisi_debet_' . $format_nama];
                    $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama] = $data[$i]['sum_posisi_credit_' . $format_nama];
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['sum_posisi_debet_' . $format_nama];
                    } else {
                        $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                    }

                    $total_konsolidasi[$i]['total_credit'] += $data_konsolidasi[$i]['sum_posisi_credit_' . $format_nama];
                }
            }
            $urutan_cabang++;
        }

        for ($i = 0; $i < count($data_konsolidasi); $i++) {
            $data_konsolidasi[$i]['total_all'] = $total_konsolidasi[$i]['total_all'];
            $data_konsolidasi[$i]['total_credit'] = $total_konsolidasi[$i]['total_credit'];
        }

        $detail_data = [
            'transaction_data' => $data_konsolidasi,
            'period' => $tahun . '-' . $bulan,
            'list_cabang' => $data_cabang,
        ];

        $map_konsolidasi = $this->getMapDetailKonsolidasi($detail_data);

        // Convert the hash map to an array
        $data = [
            'data' => $map_konsolidasi,
            'cabang' => $data_cabang,
        ];

        return $data;
    }

    private function getMapSummary($summary_data)
    {
        $data = $summary_data['transaction_data'];

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];
            $posisi_debet = $item['posisi_debet'];

            if ($posisi_debet == true || $posisi_debet == null) {
                $item_total = $item['sum_posisi_debet'];
            }else{
                $item_total = $item['sum_posisi_credit'];
            }

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
                    'children' => [],
                ];
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'total' => 0,
                        'children' => [],
                    ];
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    $map[$newHeader1]['children'][$newHeader2]['children'][] = [
                        'header' => $newHeader3,
                        'total' => $item_total,
                    ];

                    $map[$newHeader1]['children'][$newHeader2]['total'] += $item_total;
                }
                $map[$newHeader1]['total'] += $item_total;
            } else {
                // Add new_header3 as a child of new_header1
                if (!empty($newHeader3)) {
                    $map[$newHeader1]['children'][] = [
                        'header' => $newHeader3,
                        'total' => $item_total,
                    ];
                    $map[$newHeader1]['total'] += $item_total;
                }
            }
        }

        // Convert the hash map to an array
        $data = array_values($map);

        return $data;
    }

    private function getMapSummaryKonsolidasi($summary_data)
    {
        $data_konsolidasi = $summary_data['transaction_data'];
        $list_cabang = $summary_data['list_cabang'];
        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data_konsolidasi as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];
            $posisi_debet = $item['posisi_debet'];

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
                    'children' => [],
                ];

                foreach ($list_cabang as $cabang) {
                    $map[$newHeader1]['total_' . $cabang->new_nama_cabang] = 0;
                }

                $map[$newHeader1]['total_all'] = 0;
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'children' => [],
                    ];

                    foreach ($list_cabang as $cabang) {
                        $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] = 0;
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] = 0;
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    // input child 3
                    $array_item = [
                        'header' => $newHeader3,
                    ];

                    foreach ($list_cabang as $cabang) {
                        if ($posisi_debet == true || $posisi_debet == null) {
                            $array_item['total_' . $cabang->new_nama_cabang] = $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                        }else{
                            $array_item['total_' . $cabang->new_nama_cabang] =  $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                        }
                    }

                    $array_item['total_all'] = $item['total_all'];

                    $map[$newHeader1]['children'][$newHeader2]['children'][] = $array_item;
                    // end

                    foreach ($list_cabang as $cabang) {
                        if ($posisi_debet == true || $posisi_debet == null) {
                            $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                        }else{
                            $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] +=  $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                        }
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] += $item['total_all'];
                }

                foreach ($list_cabang as $cabang) {
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $map[$newHeader1]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                    }else{
                        $map[$newHeader1]['total_' . $cabang->new_nama_cabang] +=  $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                    }
                }

                $map[$newHeader1]['total_all'] += $item['total_all'];

            } else {
                // Add new_header3 as a child of new_header1
                if (!empty($newHeader3)) {
                    $map[$newHeader1]['children'][] = [
                        'header' => $newHeader3,
                    ];

                    foreach ($list_cabang as $cabang) {
                        if ($posisi_debet == true || $posisi_debet == null) {
                            $map[$newHeader1]['children'][]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                        }else{
                            $map[$newHeader1]['children'][]['total_' . $cabang->new_nama_cabang] +=  $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                        }
                    }
                    $map[$newHeader1]['total_all'] += $item['total_all'];
                }
            }
        }

        $data = (object) array_values($map);

        return $data;
    }

    private function getMapDetail($detail_data)
    {
        $data = $detail_data['transaction_data'];
        $start_date = date('Y-m-d', strtotime($detail_data['period'] . '-1'));
        $end_date = date('Y-m-t', strtotime($detail_data['period'] . '-1'));
        $id_cabang = $detail_data['id_cabang'];

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];
            $newHeader4 = $item['kode_akun'] . '.' . $item['nama_akun'];
            $posisi_debet = $item['posisi_debet'];

            if ($posisi_debet == true || $posisi_debet == null) {
                $item_total = $item['sum_posisi_debet'];
            }else{
                $item_total = $item['sum_posisi_credit'];
            }

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
                    'children' => [],
                ];
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'total' => 0,
                        'children' => [],
                    ];
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    if (!isset($map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3])) {
                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3] = [
                            'header' => $newHeader3,
                            'total' => 0,
                            'children' => [],
                        ];
                    }

                    // Add new_header4 as a child of new_header3
                    if (!empty($newHeader4)) {
                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['children'][] = [
                            'header' => $newHeader4,
                            'akun' => $item['id_akun'],
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                            'id_cabang' => $id_cabang,
                            'total' => $item_total,
                        ];

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total'] += $item_total;
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total'] += $item_total;
                } else {
                    // Add new_header3 as a child of new_header1
                    if (!empty($newHeader3)) {
                        $map[$newHeader1]['children'][] = [
                            'header' => $newHeader3,
                            'total' => $item_total,
                        ];
                        $map[$newHeader1]['children'][$newHeader2]['total'] += $item_total;
                    }
                }
                $map[$newHeader1]['total'] += $item_total;
            } else {
                // maybe never execute
                // Add new_header4 as a child of new_header1
                if (!empty($newHeader4)) {
                    $map[$newHeader1]['children'][] = [
                        'header' => $newHeader4,
                        'total' => $item_total,
                    ];
                    $map[$newHeader1]['total'] += $item_total;
                }
            }
        }

        // Convert the hash map to an array
        $data = array_values($map);

        return $data;
    }

    private function getMapDetailKonsolidasi($detail_data)
    {
        $data_konsolidasi = $detail_data['transaction_data'];
        $start_date = date('Y-m-d', strtotime($detail_data['period'] . '-1'));
        $end_date = date('Y-m-t', strtotime($detail_data['period'] . '-1'));
        $list_cabang = $detail_data['list_cabang'];

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data_konsolidasi as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];
            $newHeader4 = $item['kode_akun'] . '.' . $item['nama_akun'];
            $posisi_debet = $item['posisi_debet'];

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
                    'children' => [],
                ];

                foreach ($list_cabang as $cabang) {
                    $map[$newHeader1]['total_' . $cabang->new_nama_cabang] = 0;
                }

                $map[$newHeader1]['total_all'] = 0;
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'children' => [],
                    ];

                    foreach ($list_cabang as $cabang) {
                        $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] = 0;
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] = 0;
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    if (!isset($map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3])) {
                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3] = [
                            'header' => $newHeader3,
                            'children' => [],
                        ];

                        foreach ($list_cabang as $cabang) {
                            $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_' . $cabang->new_nama_cabang] = 0;
                        }

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_all'] = 0;
                    }

                    // Add new_header4 as a child of new_header3
                    if (!empty($newHeader4)) {
                        $array_item = [
                            'header' => $newHeader4,
                            'akun' => $item['id_akun'],
                            'kode_akun' => $item['kode_akun'],
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                        ];

                        foreach ($list_cabang as $cabang) {
                            if ($posisi_debet == true || $posisi_debet == null) {
                                $array_item['total_' . $cabang->new_nama_cabang] = $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                            }else{                                
                                $array_item['total_' . $cabang->new_nama_cabang] =  $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                            }
                        }

                        $array_item['total_all'] = $item['total_all'];

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['children'][] = $array_item;

                        foreach ($list_cabang as $cabang) {
                            if ($posisi_debet == true || $posisi_debet == null) {
                                $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                            }else{
                                $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_' . $cabang->new_nama_cabang] +=  $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                            }
                        }

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_all'] += $item['total_all'];
                    }

                    foreach ($list_cabang as $cabang) {
                        if ($posisi_debet == true || $posisi_debet == null) {
                            $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                        }else{                                
                            $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] +=  $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                        }
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] += $item['total_all'];
                } else {
                    // Add new_header3 as a child of new_header1
                    if (!empty($newHeader3)) {
                        $array_item = [
                            'header' => $newHeader3,
                        ];

                        foreach ($list_cabang as $cabang) {
                            if ($posisi_debet == true || $posisi_debet == null) {
                                $array_item['total_' . $cabang->new_nama_cabang] = $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                            }else{                                
                                $array_item['total_' . $cabang->new_nama_cabang] =  $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                            }    
                        }

                        $array_item['total_all'] = $item['total_all'];

                        $map[$newHeader1]['children'][] = $array_item;

                        foreach ($list_cabang as $cabang) {
                            if ($posisi_debet == true || $posisi_debet == null) {
                                $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                            }else{                                
                                $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] +=  $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                            }
                        }

                        $map[$newHeader1]['children'][$newHeader2]['total_all'] += $item['total_all'];
                    }
                }

                foreach ($list_cabang as $cabang) {
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $map[$newHeader1]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                    }else{                                
                        $map[$newHeader1]['total_' . $cabang->new_nama_cabang] +=  $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                    }
                }

                $map[$newHeader1]['total_all'] += $item['total_all'];
            } else {
                // maybe never execute
                // Add new_header4 as a child of new_header1
                if (!empty($newHeader4)) {
                    $array_item = [
                        'header' => $newHeader4,
                    ];

                    foreach ($list_cabang as $cabang) {
                        $array_item['total_' . $cabang->new_nama_cabang] = $item['total_' . $cabang->new_nama_cabang];
                    }

                    $array_item['total_all'] = $item['total_all'];

                    $map[$newHeader1]['children'][] = $array_item;

                    foreach ($list_cabang as $cabang) {
                        if ($posisi_debet == true || $posisi_debet == null) {
                            $map[$newHeader1]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                        }else{                                
                            $map[$newHeader1]['total_' . $cabang->new_nama_cabang] +=  $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                        }
                    }

                    $map[$newHeader1]['total_all'] += $item['total_all'];
                }
            }
        }

        $data = (object) array_values($map);

        return $data;
    }
}
