<?php

namespace App\Http\Controllers;

use App\Exports\ReportSlipExport;
use App\Models\Master\Cabang;
use App\Models\Master\Slip;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;

class ReportSlipController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'report/slip', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = getCabang();
        $data_slip = Slip::where('id_cabang', $data_cabang[0]->id_cabang)->get();

        $data = [
            "pageTitle" => "SCA Accounting | Report Slip",
            "data_slip" => $data_slip,
            "data_cabang" => $data_cabang,
        ];

        return view('accounting.report.slip.index', $data);
    }

    public function populate2(Request $request)
    {
        // if (checkAccessMenu('report_slip', 'view') == false) {
        //     return response()->json([
        //         "result" => false,
        //         "message" => "Error, anda tidak punya akses!",
        //     ]);
        // }

        $cabang = $request->cabang;
        $slip = $request->slip;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $from = "'" . $start_date . "'";
        $to = "'" . $end_date . "'";

        $slip_db = Slip::find($slip);
        Log::debug($slip);

        $saldo_awal = DB::table("jurnal_header as head")
            ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
            ->join('master_akun as akun', 'akun.id_akun', 'det.id_akun')
            ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
            ->selectRaw('head.tanggal_jurnal,
                "" as kode_jurnal,
                "" as nama_slip,
                akun.nama_akun,
                "Saldo Awal" as keterangan,
                "" as id_transaksi,
                det.debet,
                det.credit')
            ->where('head.void', 0)
            ->where('head.id_cabang', $cabang)
            ->where('head.id_slip', $slip)
            ->where('det.id_akun', $slip_db->id_akun)
            ->whereRaw("head.tanggal_jurnal BETWEEN $from AND $to")
            ->groupBy('det.id_akun')
            ->orderBy('head.tanggal_jurnal', 'DESC')
            ->get();

        Log::debug($saldo_awal);
        $mutasis = DB::table("jurnal_header as head")
            ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
            ->join('master_akun as akun', 'akun.id_akun', 'det.id_akun')
            ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
            ->selectRaw('head.tanggal_jurnal,
                head.kode_jurnal,
                slip.nama_slip,
                akun.nama_akun,
                det.keterangan,
                det.id_transaksi,
                det.debet,
                det.credit')
            ->where('head.void', 0)
            ->where('head.id_cabang', $cabang)
            ->where('head.id_slip', $slip)
            ->where('det.id_akun', '!=', $slip_db->id_akun)
            ->whereRaw("head.tanggal_jurnal BETWEEN $from AND $to")
            ->orderBy('head.tanggal_jurnal', 'DESC')
            ->get();

        return [
            'saldo_awal' => $saldo_awal,
            'mutasis' => $mutasis,
            'cabang' => $cabang,
            'from' => $start_date,
            'to' => $end_date,
        ];
    }

    public function populate(Request $request)
    {
        // if (checkAccessMenu('report_slip', 'view') == false) {
        //     return response()->json([
        //         "result" => false,
        //         "message" => "Error, anda tidak punya akses!",
        //     ]);
        // }

        try {
            $cabang = $request->cabang;
            $slip = $request->slip;
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $from = "'" . $start_date . "'";
            $to = "'" . $end_date . "'";

            $slip_db = Slip::find($slip);

            // Init Datatable
            $offset = $request->start;
            $limit = $request->length;
            $keyword = $request->search['value'];
            $sort = [];
            $order = ($request->order) ? $request->order : [];
            foreach ($order as $key => $order) {
                $columnIdx = $order['column'];
                $sortDir = $order['dir'];
                $sort[] = [
                    'column' => $request->columns[$columnIdx]['name'],
                    'dir' => $sortDir,
                ];
            }
            $draw = $request->draw;
            $current_page = $offset / $limit + 1;

            // Start Query
            $saldo_awal = DB::table("jurnal_header as head")
                ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
                ->join('master_akun as akun', 'akun.id_akun', 'det.id_akun')
                ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
                ->selectRaw('"' . $request->start_date . '" as tanggal_jurnal,
                "" as kode_jurnal,
                "" as nama_slip,
                akun.nama_akun,
                "Saldo Awal" as keterangan,
                "" as id_transaksi,
                det.debet,
                det.credit')
                ->where('head.void', 0)
                ->where('head.id_cabang', $cabang)
                ->where('head.id_slip', $slip)
                ->where('det.id_akun', $slip_db->id_akun)
                ->whereRaw("head.tanggal_jurnal BETWEEN $from AND $to")
                ->groupBy('det.id_akun')
                ->orderBy('head.tanggal_jurnal', 'DESC');

            $mutasis = DB::table("jurnal_header as head")
                ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
                ->join('master_akun as akun', 'akun.id_akun', 'det.id_akun')
                ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
                ->selectRaw('head.id_jurnal,
                head.tanggal_jurnal,
                head.kode_jurnal,
                slip.nama_slip,
                akun.nama_akun,
                det.keterangan,
                det.id_transaksi,
                det.debet,
                det.credit')
                ->where('head.void', 0)
                ->where('head.id_cabang', $cabang)
                ->where('head.id_slip', $slip)
                ->where('det.id_akun', '!=', $slip_db->id_akun)
                ->whereRaw("head.tanggal_jurnal BETWEEN $from AND $to");

            if (isset($keyword)) {
                $mutasis->where(function ($query) use ($keyword) {
                    $query->orWhere("akun.kode_akun", "LIKE", "%$keyword%")
                        ->orWhere("akun.nama_akun", "LIKE", "%$keyword%")
                        ->orWhere("slip.kode_slip", "LIKE", "%$keyword%")
                        ->orWhere("slip.nama_slip", "LIKE", "%$keyword%")
                        ->orWhere("head.kode_jurnal", "LIKE", "%$keyword%")
                        ->orWhere("head.id_transaksi", "LIKE", "%$keyword%")
                        ->orWhere("det.keterangan", "LIKE", "%$keyword%");
                });
            }

            $filtered_data = $mutasis->get();
            if ($sort && $sort[0]["column"]) {
                if (!is_array($sort)) {
                    $message = "Invalid array for parameter sort";
                    $data = [
                        "result" => false,
                        "message" => $message,
                    ];
                    return response()->json($data);
                }

                foreach ($sort as $key => $s) {
                    $column = $s["column"];
                    $directon = $s["dir"];

                    if ($column != "") {
                        $mutasis->orderBy($column, $directon);
                    }
                }
            } else {
                $mutasis->orderBy("head.tanggal_jurnal", "DESC");
            }

            // pagination
            if ($current_page) {
                $page = $current_page;
                $limit_data = $mutasis->count();
                if ($limit) {
                    $limit_data = $limit;
                }
                $offset = ($page - 1) * $limit_data;
                if ($offset < 0) {
                    $offset = 0;
                }
                if ($limit != -1) {
                    $mutasis->skip($offset)->take($limit_data);
                }
            }

            $saldo_awal = $saldo_awal->get();
            $mutasis = $mutasis->get();

            $result = $saldo_awal->merge($mutasis);

            $balance = 0;
            //Sum Balance
            foreach ($result as $key => $value) {
                $balance -= $value->credit;
                $balance += $value->debet;

                $value->balance = $balance;
            }

            $table['draw'] = $draw;
            $table['recordsTotal'] = $mutasis->count() + $saldo_awal->count();
            $table['recordsFiltered'] = $filtered_data->count();
            $table['data'] = $result;
            return json_encode($table);
        } catch (\Exception $e) {
            $message = "Failed to get populate report slip.";
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
        // if (checkAccessMenu('report_slip', 'print') == false) {
        //     return response()->json([
        //         "result" => false,
        //         "message" => "Error, anda tidak punya akses!",
        //     ]);
        // }

        Log::debug($request->all());

        try {
            return Excel::download(new ReportSlipExport($request->cabang, $request->slip, $request->start_date, $request->end_date), 'ReportSlips.xlsx');
        } catch (\Exception $e) {
            Log::error("Error when export excel report slip");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Error when export excel report slip",
            ]);
        }
    }

    public function exportPdf(Request $request)
    {
        // if (checkAccessMenu('report_slip', 'print') == false) {
        //     return response()->json([
        //         "result" => false,
        //         "message" => "Error, anda tidak punya akses!",
        //     ]);
        // }

        try {

            $cabang = $request->cabang;
            $slip = $request->slip;
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $from = "'" . $start_date . "'";
            $to = "'" . $end_date . "'";

            $slip_db = Slip::find($slip);
            Log::debug($slip);

            $saldo_awal = DB::table("jurnal_header as head")
                ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
                ->join('master_akun as akun', 'akun.id_akun', 'det.id_akun')
                ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
                ->selectRaw('"' . $request->start_date . '" as tanggal_jurnal,
                    "" as kode_jurnal,
                    "" as nama_slip,
                    akun.nama_akun,
                    "Saldo Awal" as keterangan,
                    "" as id_transaksi,
                    det.debet,
                    det.credit')
                ->where('head.void', 0)
                ->where('head.id_cabang', $cabang)
                ->where('head.id_slip', $slip)
                ->where('det.id_akun', $slip_db->id_akun)
                ->whereRaw("head.tanggal_jurnal BETWEEN $from AND $to")
                ->groupBy('det.id_akun')
                ->orderBy('head.tanggal_jurnal', 'DESC')
                ->get();

            Log::debug($saldo_awal);
            $mutasis = DB::table("jurnal_header as head")
                ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
                ->join('master_akun as akun', 'akun.id_akun', 'det.id_akun')
                ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
                ->selectRaw('head.tanggal_jurnal,
                    head.kode_jurnal,
                    slip.nama_slip,
                    akun.nama_akun,
                    det.keterangan,
                    det.id_transaksi,
                    det.debet,
                    det.credit')
                ->where('head.void', 0)
                ->where('head.id_cabang', $cabang)
                ->where('head.id_slip', $slip)
                ->where('det.id_akun', '!=', $slip_db->id_akun)
                ->whereRaw("head.tanggal_jurnal BETWEEN $from AND $to")
                ->orderBy('head.tanggal_jurnal', 'DESC')
                ->get();

            $cabang = Cabang::find($cabang);
            $slip = Slip::find($slip);

            foreach ($saldo_awal as $key => $value) {
                $notes = str_replace("\n", '<br>', $value->keterangan);
                $value->keterangan = $notes;
            }

            foreach ($mutasis as $key => $value) {
                $notes = str_replace("\n", '<br>', $value->keterangan);
                $value->keterangan = $notes;
            }

            $data = [
                'saldo_awal' => $saldo_awal,
                'mutasis' => $mutasis,
                'cabang' => $cabang,
                'slip' => $slip,
                'from' => $start_date,
                'to' => $end_date,
            ];

            // return view('accounting.report.slip.print', $data);

            if (count($saldo_awal) > 0 && count($mutasis) > 0) {
                $pdf = PDF::loadView('accounting.report.slip.print', $data);
                $pdf->setPaper('a4', 'landscape');

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
                    'status' => false,
                    'message' => 'No data found',
                ]);
            }
        } catch (\Exception $e) {
            $message = "Failed to print report slip";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    public function getSlip(Request $request)
    {
        try {
            $slip = Slip::where('id_cabang', $request->cabang)->get();
            return response()->json([
                "result" => true,
                "message" => 'Success get slip data',
                "data" => $slip,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "result" => false,
                "message" => 'Error when get slip data',
            ]);
        }
    }
}
