<?php

namespace App\Http\Controllers;

use App\Exports\ReportProfitAndLossExport;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;

class ReportProfitAndLossController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'report/profit_loss', 'show') == false) {
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
            $start_date = date('Y-m-d', strtotime($request->start));
            $end_date = date('Y-m-d', strtotime($request->end));

            $data = $this->getData($id_cabang, $start_date, $end_date, $year, $month, $type);

            return response()->json([
                "result" => true,
                "data" => $data,
            ]);
        } catch (\Exception $e) {
            $message = "Failed to get populate profit and loss for view";
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
            $start_date = date('Y-m-d', strtotime($request->start));
            $end_date = date('Y-m-d', strtotime($request->end));
            $type = $request->type;
            $monthName = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

            $data_profit_loss = $this->getData($id_cabang, $start_date, $end_date, $year, $month, $type);

            if ($id_cabang == null) {
                $nama_cabang = 'all';
                $list_cabang = $data_profit_loss['cabang'];
                $data_profit_loss = $data_profit_loss['data'];
            } else {
                $data_cabang = Cabang::find($id_cabang);
                $nama_cabang = $data_cabang->nama_cabang;
                $list_cabang = null;
            }

            $periode_table =  date('d M Y', strtotime($start_date)) . ' s/d ' . date('d M Y', strtotime($end_date));
            $periode = date('d M Y', strtotime($start_date)) . ' s/d ' . date('d M Y', strtotime($end_date));

            $data = [
                'nama_cabang' => $nama_cabang,
                'list_cabang' => $list_cabang,
                'periode_table' => $periode_table,
                'periode' => $periode,
                'type' => ucwords(str_replace('_', ' ', $type)),
                'data' => $data_profit_loss,
            ];

            if (!empty($data["data"])) {
                $pdf = PDF::loadView('accounting.report.profit_loss.print', $data);
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
            $message = "Failed to print report profit loss for pdf";
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
            $start_date = date('Y-m-d', strtotime($request->start));
            $end_date = date('Y-m-d', strtotime($request->end));
            $monthName = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

            $data_profit_loss = $this->getData($id_cabang, $start_date, $end_date, $year, $month, $type);

            if ($id_cabang == null) {
                $nama_cabang = 'all';
                $list_cabang = $data_profit_loss['cabang'];
                $data_profit_loss = $data_profit_loss['data'];
            } else {
                $data_cabang = Cabang::find($id_cabang);
                $nama_cabang = $data_cabang->nama_cabang;
                $list_cabang = null;
            }

            $periode_table =  date('d M Y', strtotime($start_date)) . ' s/d ' . date('d M Y', strtotime($end_date));
            $periode = date('d M Y', strtotime($start_date)) . ' s/d ' . date('d M Y', strtotime($end_date));

            // Log::debug(json_encode($data_profit_loss));
            $data = [
                'nama_cabang' => $nama_cabang,
                'list_cabang' => $list_cabang,
                'periode_table' => $periode_table,
                'periode' => $periode,
                'type' => str_replace('_', ' ', $type),
                'data' => $data_profit_loss,
            ];

            // dd(count($data["data"]));

            // dd($data);
            if (!empty($data["data"])) {
                return Excel::download(new ReportProfitAndLossExport($data), 'ReportProfitLoss.xlsx');
            } else {
                return response()->json([
                    "result" => false,
                    "message" => "Tidak ada data",
                ]);
            }
        } catch (\Exception $e) {
            $message = "Failed to print report profit loss for excel";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    private function getData($id_cabang, $start, $end, $year, $month, $type)
    {
        if ($id_cabang == null) {
            if ($type == 'recap') {
                Log::debug('recap cabang null');
                $data_balance = $this->getSummaryBalanceKonsolidasi($start, $end);
            } else if ($type == 'detail') {
                Log::debug('detail cabang null');
                $data_balance = $this->getDetailBalanceKonsolidasi($start, $end);
            } else if ($type == 'awal') {
                Log::debug('awal cabang null');
                $data_balance = $this->getInitBalanceKonsolidasi($year, $month);
            } else {
                Log::debug('else cabang null');
                $data_balance = $this->getInitDetailBalanceKonsolidasi($year, $month);
            }
        } else {
            if ($type == 'recap') {
                Log::debug('recap cabang ada');
                $data_balance = $this->getSummaryBalance($id_cabang, $start, $end);
            } else if ($type == 'detail') {
                Log::debug('detail cabang ada');
                $data_balance = $this->getDetailBalance($id_cabang, $start, $end);
            } else if ($type == 'awal') {
                Log::debug('awal cabang ada');
                $data_balance = $this->getInitBalance($id_cabang, $year, $month);
            } else {
                Log::debug('else cabang ada');
                $data_balance = $this->getInitDetailBalance($id_cabang, $year, $month);
            }
        }

        return $data_balance;
    }

    private function getSummaryBalance($id_cabang, $start, $end)
    {
        $start_saldo_awal = date('Y-m-01', strtotime($start));
        $exp_start_date = explode('-', $start);

        $table_query = '
            SELECT id_akun, sum(sum_posisi_credit) AS sum_posisi_credit, sum(sum_posisi_debet) AS sum_posisi_debet
            FROM
                (
                SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                FROM
                    jurnal_header a
                    INNER JOIN jurnal_detail b ON a.id_jurnal = b.id_jurnal
                WHERE
                    void = 0
                    AND a.id_cabang = ' . $id_cabang . '
                    AND tanggal_jurnal BETWEEN "' . $start . '" AND "' . $end . '"
                    AND ((a.id_transaksi NOT LIKE "Closing 1%" AND a.id_transaksi NOT LIKE "Closing 2%") OR a.id_transaksi IS NULL)
                GROUP BY id_akun
        ';

        if(intval($exp_start_date[2]) > 1){
            $table_query .= '
                UNION ALL
                SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                FROM
                    jurnal_header a
                    INNER JOIN jurnal_detail b ON a.id_jurnal = b.id_jurnal
                WHERE
                    void = 0
                    AND a.id_cabang = ' . $id_cabang . '
                    AND tanggal_jurnal BETWEEN "' . $start_saldo_awal . '" AND "' . date('Y-m-d', strtotime($start . "-1 days")) . '"
                    AND ((a.id_transaksi NOT LIKE "Closing 1%" AND a.id_transaksi NOT LIKE "Closing 2%") OR a.id_transaksi IS NULL)
                GROUP BY id_akun
            ';
        }

        $table_query .= '
                UNION ALL
                SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                FROM
                    saldo_balance sb
                WHERE
                    tahun = ' . $exp_start_date[0] . '
                    AND bulan = ' . $exp_start_date[1] . '
                    AND id_cabang = ' . $id_cabang . '
                GROUP BY id_akun
            ) summary
            GROUP BY id_akun
        ';

        $data = Akun::selectRaw('
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(sum_posisi_credit, 0)) as sum_posisi_credit,
                SUM(IFNULL(sum_posisi_debet, 0)) as sum_posisi_debet,
                master_akun.posisi_debet,
                master_akun.id_akun
            ')            
            ->leftJoin(DB::raw('(' . $table_query . ') as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 1)
            ->where('master_akun.id_cabang', $id_cabang)
            ->groupBy('new_header1', 'new_header2', 'new_header3')
            ->get();

        $total = [];

        $total['grand_total'] = 0;

        // Log::debug(json_encode($data));

        $summary_data = [
            'transaction_data' => $data,
            'total' => $total,
        ];

        // Log::debug(json_encode($data));

        $data = $this->getMapSummary($summary_data);

        // Log::debug(json_encode($data));

        $data = [
            'data' => (Object) $data['map'],
            'total' => $data['total'],
        ];

        return $data;
    }

    private function getSummaryBalanceKonsolidasi($start, $end)
    {
        $data_cabang = Cabang::where('status_cabang', 1)->selectRaw('*, REPLACE(LOWER(nama_cabang), " ", "_") as new_nama_cabang')->get();

        $data_konsolidasi = [];
        $total_konsolidasi = [];
        $urutan_cabang = 1;
        $start_saldo_awal = date('Y-m-01', strtotime($start));
        $exp_start_date = explode('-', $start);

        foreach ($data_cabang as $cabang) {
            $format_nama = str_replace(' ', '_', strtolower($cabang->nama_cabang));
            $select_query = '
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(sum_posisi_credit, 0)) as sum_posisi_credit_' . $format_nama . ',
                SUM(IFNULL(sum_posisi_debet, 0)) as sum_posisi_debet_' . $format_nama . ',
                master_akun.posisi_debet';
            
            $table_query = '
                SELECT id_akun, sum(sum_posisi_credit) AS sum_posisi_credit, sum(sum_posisi_debet) AS sum_posisi_debet
                FROM
                    (
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                    FROM
                        jurnal_header a
                        INNER JOIN jurnal_detail b ON a.id_jurnal = b.id_jurnal
                    WHERE
                        void = 0
                        AND a.id_cabang = ' . $cabang->id_cabang . '
                        AND tanggal_jurnal BETWEEN "' . $start . '" AND "' . $end . '"
                        AND ((a.id_transaksi NOT LIKE "Closing 1%" AND a.id_transaksi NOT LIKE "Closing 2%") OR a.id_transaksi IS NULL)
                    GROUP BY id_akun
                ';

            if(intval($exp_start_date[2]) > 1){
                $table_query .= '
                    UNION ALL
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                    FROM
                        jurnal_header a
                        INNER JOIN jurnal_detail b ON a.id_jurnal = b.id_jurnal
                    WHERE
                        void = 0
                        AND a.id_cabang = ' . $cabang->id_cabang . '
                        AND tanggal_jurnal BETWEEN "' . $start_saldo_awal . '" AND "' . date('Y-m-d', strtotime($start . "-1 days")) . '"
                        AND ((a.id_transaksi NOT LIKE "Closing 1%" AND a.id_transaksi NOT LIKE "Closing 2%") OR a.id_transaksi IS NULL)
                    GROUP BY id_akun
                ';
            }

            $table_query .= '
                    UNION ALL
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                    FROM
                        saldo_balance sb
                    WHERE
                        tahun = ' . $exp_start_date[0] . '
                        AND bulan = ' . $exp_start_date[1] . '
                        AND id_cabang = ' . $cabang->id_cabang . '
                    GROUP BY id_akun
                ) summary
                GROUP BY id_akun
            ';

            $data = Akun::selectRaw($select_query)
                ->leftJoin(DB::raw('(' . $table_query . ') as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
                ->where('isshown', 1)
                ->where('tipe_akun', 1)
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

        $total = [];

        foreach ($data_cabang as $cabang) {
            $total['grand_total_' . $cabang->new_nama_cabang] = 0;
        }

        $total['grand_total'] = 0;

        // Log::debug(json_encode($data_konsolidasi));

        $summary_data = [
            'transaction_data' => $data_konsolidasi,
            'list_cabang' => $data_cabang,
            'total' => $total,
        ];

        $map_konsolidasi = $this->getMapSummaryKonsolidasi($summary_data);

        // Convert the hash map to an array
        $data = [
            'data' => $map_konsolidasi['map'],
            'total' => $map_konsolidasi['total'],
            'cabang' => $data_cabang,
        ];

        return $data;
    }

    private function getDetailBalance($id_cabang, $start, $end)
    {
        $start_saldo_awal = date('Y-m-01', strtotime($start));
        $exp_start_date = explode('-', $start);

        $table_query = '
            SELECT id_akun, sum(sum_posisi_credit) AS sum_posisi_credit, sum(sum_posisi_debet) AS sum_posisi_debet
            FROM
                (
                SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                FROM
                    jurnal_header a
                    INNER JOIN jurnal_detail b ON a.id_jurnal = b.id_jurnal
                WHERE
                    void = 0
                    AND a.id_cabang = ' . $id_cabang . '
                    AND tanggal_jurnal BETWEEN "' . $start . '" AND "' . $end . '"
                    AND ((a.id_transaksi NOT LIKE "Closing 1%" AND a.id_transaksi NOT LIKE "Closing 2%") OR a.id_transaksi IS NULL)
                GROUP BY id_akun
        ';

        if(intval($exp_start_date[2]) > 1){
            $table_query .= '
                UNION ALL
                SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                FROM
                    jurnal_header a
                    INNER JOIN jurnal_detail b ON a.id_jurnal = b.id_jurnal
                WHERE
                    void = 0
                    AND a.id_cabang = ' . $id_cabang . '
                    AND tanggal_jurnal BETWEEN "' . $start_saldo_awal . '" AND "' . date('Y-m-d', strtotime($start . "-1 days")) . '"
                    AND ((a.id_transaksi NOT LIKE "Closing 1%" AND a.id_transaksi NOT LIKE "Closing 2%") OR a.id_transaksi IS NULL)
                GROUP BY id_akun
            ';
        }

        $table_query .= '
                UNION ALL
                SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                FROM
                    saldo_balance sb
                WHERE
                    tahun = ' . $exp_start_date[0] . '
                    AND bulan = ' . $exp_start_date[1] . '
                    AND id_cabang = ' . $id_cabang . '
                GROUP BY id_akun
            ) summary
            GROUP BY id_akun
        ';

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
            ->leftJoin(DB::raw('(' . $table_query . ') as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 1)
            ->where('master_akun.id_cabang', $id_cabang)
            ->groupBy('new_header1', 'new_header2', 'new_header3', 'master_akun.id_akun')
            ->get();

        $total = [];

        $total['grand_total'] = 0;

        $detail_data = [
            'transaction_data' => $data,
            'period' => $start . '-' . $end,
            'start' => $start,
            'end' => $end,
            'id_cabang' => $id_cabang,
            'total' => $total,
        ];

        $data = $this->getMapDetail($detail_data);

        $data = [
            'data' => (Object) $data['map'],
            'total' => $data['total'],
        ];

        return $data;
    }

    private function getDetailBalanceKonsolidasi($start, $end)
    {
        $data_cabang = Cabang::where('status_cabang', 1)->selectRaw('*, nama_cabang, REPLACE(LOWER(nama_cabang), " ", "_") as new_nama_cabang')->get();

        $data_konsolidasi = [];
        $total_konsolidasi = [];
        $urutan_cabang = 1;

        $start_saldo_awal = date('Y-m-01', strtotime($start));
        $exp_start_date = explode('-', $start);

        foreach ($data_cabang as $cabang) {
            $format_nama = str_replace(' ', '_', strtolower($cabang->nama_cabang));
            $select_query = '
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                SUM(IFNULL(sum_posisi_credit, 0)) as sum_posisi_credit_' . $format_nama . ',
                SUM(IFNULL(sum_posisi_debet, 0)) as sum_posisi_debet_' . $format_nama . ',
                master_akun.kode_akun,
                master_akun.nama_akun,
                master_akun.id_akun,
                master_akun.posisi_debet';

            $table_query = '
                SELECT id_akun, sum(sum_posisi_credit) AS sum_posisi_credit, sum(sum_posisi_debet) AS sum_posisi_debet
                FROM
                    (
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                    FROM
                        jurnal_header a
                        INNER JOIN jurnal_detail b ON a.id_jurnal = b.id_jurnal
                    WHERE
                        void = 0
                        AND a.id_cabang =  ' . $cabang->id_cabang . '
                        AND tanggal_jurnal BETWEEN "' . $start . '" AND "' . $end . '"
                        AND ((a.id_transaksi NOT LIKE "Closing 1%" AND a.id_transaksi NOT LIKE "Closing 2%") OR a.id_transaksi IS NULL)
                    GROUP BY id_akun
            ';

            if(intval($exp_start_date[2]) > 1){
                $table_query .= '
                    UNION ALL
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                    FROM
                        jurnal_header a
                        INNER JOIN jurnal_detail b ON a.id_jurnal = b.id_jurnal
                    WHERE
                        void = 0
                        AND a.id_cabang =  ' . $cabang->id_cabang . '
                        AND tanggal_jurnal BETWEEN "' . $start_saldo_awal . '" AND "' . date('Y-m-d', strtotime($start . "-1 days")) . '"
                        AND ((a.id_transaksi NOT LIKE "Closing 1%" AND a.id_transaksi NOT LIKE "Closing 2%") OR a.id_transaksi IS NULL)
                    GROUP BY id_akun
                ';
            }

            $table_query .= '
                    UNION ALL
                    SELECT id_akun, sum( credit - debet ) AS sum_posisi_credit, sum( debet - credit ) AS sum_posisi_debet
                    FROM
                        saldo_balance sb
                    WHERE
                        tahun = ' . $exp_start_date[0] . '
                        AND bulan = ' . $exp_start_date[1] . '
                        AND id_cabang =  ' . $cabang->id_cabang . '
                    GROUP BY id_akun
                ) summary
                GROUP BY id_akun
            ';

            $data = Akun::selectRaw($select_query)
                ->leftJoin(DB::raw('(' . $table_query . ') as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
                ->where('isshown', 1)
                ->where('tipe_akun', 1)
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

        $total = [];
        // Log::info("data end");
        // Log::info(json_encode($data_konsolidasi));

        foreach ($data_cabang as $cabang) {
            $total['grand_total_' . $cabang->new_nama_cabang] = 0;
        }

        $total['grand_total'] = 0;

        $detail_data = [
            'transaction_data' => $data_konsolidasi,
            'period' => $start . '-' . $end,
            'start' => $start,
            'end' => $end,
            'list_cabang' => $data_cabang,
            'total' => $total,
        ];

        $map_konsolidasi = $this->getMapDetailKonsolidasi($detail_data);
        // Log::info("data map");
        // Log::info($map_konsolidasi);

        // Convert the hash map to an array
        $data = [
            'data' => $map_konsolidasi['map'],
            'total' => $map_konsolidasi['total'],
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
                SUM(IFNULL(total_summary, 0)) as total
            ')
            ->leftJoin(DB::raw('(
                SELECT id_akun, sum( credit - debet ) AS total_summary
                FROM
                    saldo_balance sb
                WHERE
                    tahun = ' . $tahun . '
                    AND bulan = ' . $bulan . '
                    AND id_cabang = ' . $id_cabang . '
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 1)
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
                SUM(IFNULL(total_summary, 0)) as total_' . $format_nama;

            $data = Akun::selectRaw($select_query)
                ->leftJoin(DB::raw('(
                    SELECT id_akun, sum( credit - debet ) AS total_summary
                    FROM
                        saldo_balance sb
                    WHERE
                        tahun = ' . $tahun . '
                        AND bulan = ' . $bulan . '
                        AND id_cabang = ' . $cabang->id_cabang . '
                    GROUP BY id_akun
                ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
                ->where('isshown', 1)
                ->where('tipe_akun', 1)
                ->groupBy('new_header1', 'new_header2', 'new_header3')
                ->get()->toArray();

            if ($urutan_cabang == 1) {
                $data_konsolidasi = $data;
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['total_' . $format_nama];
                }
            } else {
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $data_konsolidasi[$i]['total_' . $format_nama] = $data[$i]['total_' . $format_nama];
                    $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['total_' . $format_nama];
                }
            }
            $urutan_cabang++;
        }

        for ($i = 0; $i < count($data_konsolidasi); $i++) {
            $data_konsolidasi[$i]['total_all'] = $total_konsolidasi[$i]['total_all'];
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
                SUM(IFNULL(total_summary, 0)) as total,
                kode_akun,
                nama_akun,
                master_akun.id_akun
            ')
            ->leftJoin(DB::raw('(
                SELECT id_akun, sum( credit - debet ) AS total_summary
                FROM
                    saldo_balance sb
                WHERE
                    tahun = ' . $tahun . '
                    AND bulan = ' . $bulan . '
                    AND id_cabang = ' . $id_cabang . '
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
            ->where('isshown', 1)
            ->where('tipe_akun', 1)
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
                SUM(IFNULL(total_summary, 0)) as total_' . $format_nama . ',
                kode_akun,
                nama_akun,
                master_akun.id_akun';

            $data = Akun::selectRaw($select_query)
                ->leftJoin(DB::raw('(
                SELECT id_akun, sum( credit - debet ) AS total_summary
                FROM
                    saldo_balance sb
                WHERE
                    tahun = ' . $tahun . '
                    AND bulan = ' . $bulan . '
                    AND id_cabang = ' . $cabang->id_cabang . '
                GROUP BY id_akun
            ) as jurnal'), 'master_akun.id_akun', '=', 'jurnal.id_akun')
                ->where('isshown', 1)
                ->where('tipe_akun', 1)
                ->groupBy('new_header1', 'new_header2', 'new_header3', 'master_akun.kode_akun')
                ->get()->toArray();

            if ($urutan_cabang == 1) {
                $data_konsolidasi = $data;
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $total_konsolidasi[$i]['total_all'] = $data_konsolidasi[$i]['total_' . $format_nama];
                }
            } else {
                for ($i = 0; $i < count($data_konsolidasi); $i++) {
                    $data_konsolidasi[$i]['total_' . $format_nama] = $data[$i]['total_' . $format_nama];
                    $total_konsolidasi[$i]['total_all'] += $data_konsolidasi[$i]['total_' . $format_nama];
                }
            }
            $urutan_cabang++;
        }

        for ($i = 0; $i < count($data_konsolidasi); $i++) {
            $data_konsolidasi[$i]['total_all'] = $total_konsolidasi[$i]['total_all'];
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
        $total = $summary_data['total'];

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
            } else {
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
                $total['grand_total'] += $item['sum_posisi_credit'];
            } else {
                // Add new_header3 as a child of new_header1
                if (!empty($newHeader3)) {
                    $map[$newHeader1]['children'][] = [
                        'header' => $newHeader3,
                        'total' => $item_total,
                    ];
                    $map[$newHeader1]['total'] += $item_total;
                    $total['grand_total'] += $item['sum_posisi_credit'];
                }
            }
        }

        // Convert the hash map to an array
        $data = ['map' => array_values($map), 'total' => $total];

        return $data;
    }

    private function getMapSummaryKonsolidasi($summary_data)
    {
        $data_konsolidasi = $summary_data['transaction_data'];
        $list_cabang = $summary_data['list_cabang'];
        $total = $summary_data['total'];
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
                        } else {
                            $array_item['total_' . $cabang->new_nama_cabang] = $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                        }
                    }

                    $array_item['total_all'] = $item['total_all'];

                    $map[$newHeader1]['children'][$newHeader2]['children'][] = $array_item;
                    // end

                    foreach ($list_cabang as $cabang) {
                        if ($posisi_debet == true || $posisi_debet == null) {
                            $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                        } else {
                            $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                        }
                    }

                    $map[$newHeader1]['children'][$newHeader2]['total_all'] += $item['total_all'];
                }

                foreach ($list_cabang as $cabang) {
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $map[$newHeader1]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                    } else {
                        $map[$newHeader1]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                    }
                    $total['grand_total_' . $cabang->new_nama_cabang] += $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                }

                $map[$newHeader1]['total_all'] += $item['total_all'];
                $total['grand_total'] += $item['total_credit'];

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
                        $total['grand_total_' . $cabang->new_nama_cabang] += $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                    }
                    $map[$newHeader1]['total_all'] += $item['total_all'];
                    $total['grand_total'] += $item['total_credit'];
                }
            }
        }

        $data = ['map' => (object) array_values($map), 'total' => $total];

        return $data;
    }

    private function getMapDetail($detail_data)
    {
        $data = $detail_data['transaction_data'];
        if(isset($detail_data['start'])){
            $start_date = date('Y-m-d', strtotime($detail_data['start']));
            $end_date = date('Y-m-d', strtotime($detail_data['end']));
        }else{
            $start_date = date('Y-m-d', strtotime($detail_data['period'] . '-1'));
            $end_date = date('Y-m-t', strtotime($detail_data['period'] . '-1'));
        }
        $id_cabang = $detail_data['id_cabang'];
        $total = $detail_data['total'];

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
            } else {
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
                $total['grand_total'] += $item['sum_posisi_credit'];
            } else {
                // maybe never execute
                // Add new_header4 as a child of new_header1
                if (!empty($newHeader4)) {
                    $map[$newHeader1]['children'][] = [
                        'header' => $newHeader4,
                        'total' => $item_total,
                    ];
                    $map[$newHeader1]['total'] += $item_total;
                    $total['grand_total'] += $item['sum_posisi_credit'];
                }
            }
        }

        // Convert the hash map to an array
        $data = ['map' => array_values($map), 'total' => $total];

        return $data;
    }

    private function getMapDetailKonsolidasi($detail_data)
    {
        $data_konsolidasi = $detail_data['transaction_data'];
        if(isset($detail_data['start'])){
            $start_date = date('Y-m-d', strtotime($detail_data['start']));
            $end_date = date('Y-m-d', strtotime($detail_data['end']));
        }else{
            $start_date = date('Y-m-d', strtotime($detail_data['period'] . '-1'));
            $end_date = date('Y-m-t', strtotime($detail_data['period'] . '-1'));
        }
        $list_cabang = $detail_data['list_cabang'];
        $total = $detail_data['total'];

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
                            'posisi_debet' => $item['posisi_debet'],
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                        ];

                        foreach ($list_cabang as $cabang) {
                            if ($posisi_debet == true || $posisi_debet == null) {
                                $array_item['total_' . $cabang->new_nama_cabang] = $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                            } else {
                                $array_item['total_' . $cabang->new_nama_cabang] = $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                            }
                        }

                        $array_item['total_all'] = $item['total_all'];

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['children'][] = $array_item;

                        foreach ($list_cabang as $cabang) {
                            if ($posisi_debet == true || $posisi_debet == null) {
                                $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                            } else {
                                $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                            }
                        }

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['total_all'] += $item['total_all'];
                    }

                    foreach ($list_cabang as $cabang) {
                        if ($posisi_debet == true || $posisi_debet == null) {
                            $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                        } else {
                            $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
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
                            } else {
                                $array_item['total_' . $cabang->new_nama_cabang] = $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                            }
                        }

                        $array_item['total_all'] = $item['total_all'];

                        $map[$newHeader1]['children'][] = $array_item;

                        foreach ($list_cabang as $cabang) {
                            if ($posisi_debet == true || $posisi_debet == null) {
                                $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                            } else {
                                $map[$newHeader1]['children'][$newHeader2]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                            }
                        }

                        $map[$newHeader1]['children'][$newHeader2]['total_all'] += $item['total_all'];
                    }
                }

                foreach ($list_cabang as $cabang) {
                    if ($posisi_debet == true || $posisi_debet == null) {
                        $map[$newHeader1]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_debet_' . $cabang->new_nama_cabang];
                    } else {
                        $map[$newHeader1]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                    }
                    $total['grand_total_' . $cabang->new_nama_cabang] += $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                }

                $map[$newHeader1]['total_all'] += $item['total_all'];
                $total['grand_total'] += $item['total_credit'];
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
                        } else {
                            $map[$newHeader1]['total_' . $cabang->new_nama_cabang] += $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                        }
                        $total['grand_total_' . $cabang->new_nama_cabang] += $item['sum_posisi_credit_' . $cabang->new_nama_cabang];
                    }

                    $map[$newHeader1]['total_all'] += $item['total_all'];
                    $total['grand_total'] += $item['total_credit'];
                }
            }
        }

        // Log::debug($total);

        $data = ['map' => (object) array_values($map), 'total' => $total];

        return $data;
    }
}
