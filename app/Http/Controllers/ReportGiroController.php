<?php

namespace App\Http\Controllers;

use App\Exports\ReportGiroExport;
use App\Models\Master\Cabang;
use App\Models\Master\Slip;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;

class ReportGiroController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'report/giro', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = getCabang();
        $data_slip = Slip::where('jenis_slip', 3)->where('id_cabang', $data_cabang[0]->id_cabang)->get();
        $data_status = array(
            array(
                'value' => 'All',
                'title' => 'All',
            ),
            array(
                'value' => '0',
                'title' => 'Belum Cair',
            ),
            array(
                'value' => '1',
                'title' => 'Cair',
            ),
            array(
                'value' => '2',
                'title' => 'Tolak',
            ),
        );

        $data = [
            "pageTitle" => "SCA Accounting | Report Giro",
            "data_slip" => $data_slip,
            "data_cabang" => $data_cabang,
            "data_status" => $data_status,
        ];

        return view('accounting.report.giro.index', $data);
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
        $tipe = $request->tipe;
        $tanggal = $request->tanggal;
        $status = $request->status;

        try {
            $giro = DB::table("jurnal_header as head")
                ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
                ->join('saldo_transaksi as saldo', 'saldo.id_jurnal', 'head.id_jurnal')
                ->selectRaw('head.id_jurnal,
                    head.tanggal_jurnal,
                    head.kode_jurnal,
                    head.no_giro,
                    head.tanggal_giro,
                    head.tanggal_giro_jt,
                    saldo.total')
                ->where('head.void', 0)
                ->where('head.id_cabang', $cabang)
                ->where('head.jenis_jurnal', $tipe)
                ->where('head.tanggal_giro_jt', '<=', $tanggal);

            if ($slip != 'All') {
                $giro = $giro->where('head.id_slip', $slip);
            }

            if ($status != 'All') {
                if ($status == 0) {
                    $giro = $giro->where('saldo.sisa', '>', 0);
                    $giro = $giro->where('saldo.status_giro', $status);
                } else {
                    $giro = $giro->where('saldo.sisa', 0);
                    $giro = $giro->where('saldo.status_giro', $status);
                }
            }

            $giro = $giro->groupBy('det.id_jurnal')
                ->orderBy('head.tanggal_jurnal', 'DESC');

            $data = $giro->get();
            foreach ($data as $key => $value) {
                $cair = DB::table('jurnal_header as head')
                    ->join('saldo_transaksi as saldo', 'saldo.id_jurnal', 'head.id_jurnal')
                    ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
                    ->selectRaw('head.id_jurnal,
                        head.kode_jurnal,
                        head.tanggal_giro_jt,
                        slip.nama_slip')
                    ->where('head.id_jurnal', $value->id_jurnal)
                    ->where('saldo.sisa', '=', 0)
                    ->where('saldo.status_giro', '=', 1)
                    ->first();

                $value->cair_id_jurnal = isset($cair) ? $cair->id_jurnal : '';
                $value->cair_kode_jurnal = isset($cair) ? $cair->kode_jurnal : '';
                $value->cair_tanggal_jurnal = isset($cair) ? $cair->tanggal_giro_jt : '';
                $value->cair_slip = isset($cair) ? $cair->nama_slip : '';

                $tolak = DB::table('jurnal_header as head')
                    ->join('saldo_transaksi as saldo', 'saldo.id_jurnal', 'head.id_jurnal')
                    ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
                    ->selectRaw('head.id_jurnal,
                        head.kode_jurnal,
                        head.tanggal_giro_jt,
                        slip.nama_slip')
                    ->where('head.id_jurnal', $value->id_jurnal)
                    ->where('saldo.sisa', '=', 0)
                    ->where('saldo.status_giro', '=', 2)
                    ->first();

                $value->tolak_id_jurnal = isset($tolak) ? $tolak->id_jurnal : '';
                $value->tolak_kode_jurnal = isset($tolak) ? $tolak->kode_jurnal : '';
                $value->tolak_tanggal_jurnal = isset($tolak) ? $tolak->tanggal_giro_jt : '';
            }

            return response()->json([
                "result" => true,
                "data" => $data,
            ]);
        } catch (\Exception $e) {
            Log::debug($e);

            return response()->json([
                "result" => false,
                "data" => $data,
            ]);
        }

        return $data;
    }

    public function populate(Request $request)
    {
        try {
            $cabang = $request->cabang;
            $slip = $request->slip;
            $tipe = $request->tipe;
            $tanggal = $request->tanggal;
            $status = $request->status;

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
            $giro = DB::table("jurnal_header as head")
                ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
                ->join('saldo_transaksi as saldo', 'saldo.id_jurnal', 'head.id_jurnal')
                ->selectRaw('head.id_jurnal,
                    head.tanggal_jurnal,
                    head.kode_jurnal,
                    head.no_giro,
                    head.tanggal_giro,
                    head.tanggal_giro_jt,
                    saldo.total')
                ->where('head.void', 0)
                ->where('head.id_cabang', $cabang)
                ->where('head.jenis_jurnal', $tipe)
                ->where('head.tanggal_giro_jt', '<=', $tanggal);

            if (isset($keyword)) {
                $giro->where(function ($query) use ($keyword) {
                    $query->orWhere("head.kode_jurnal", "LIKE", "%$keyword%");
                });
            }

            if ($slip != 'All') {
                $giro = $giro->where('head.id_slip', $slip);
            }

            if ($status != 'All') {
                if ($status == 0) {
                    $giro = $giro->where('saldo.sisa', '>', 0);
                    $giro = $giro->where('saldo.status_giro', $status);
                } else {
                    $giro = $giro->where('saldo.sisa', 0);
                    $giro = $giro->where('saldo.status_giro', $status);
                }
            }

            $giro = $giro->groupBy('det.id_jurnal');

            $filtered_data = $giro->get();

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
                        $giro->orderBy($column, $directon);
                    }
                }
            } else {
                $giro->orderBy('head.tanggal_jurnal', 'DESC');
            }

            // pagination
            if ($current_page) {
                $page = $current_page;
                $limit_data = $giro->count();
                if ($limit) {
                    $limit_data = $limit;
                }
                $offset = ($page - 1) * $limit_data;
                if ($offset < 0) {
                    $offset = 0;
                }
                $giro->skip($offset)->take($limit_data);
            }

            $data = $giro->get();

            foreach ($data as $key => $value) {
                $cair = DB::table('jurnal_header as head')
                    ->join('jurnal_detail as det', 'det.id_jurnal', 'det.id_jurnal')
                    ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
                    ->selectRaw('head.kode_jurnal,
                    head.tanggal_jurnal,
                    slip.nama_slip')
                    ->where('det.id_transaksi', $value->kode_jurnal)
                    ->where('head.tanggal_jurnal', $value->tanggal_giro_jt)
                    ->where('head.status_giro', 1)
                    ->where('head.void', '0')
                    ->first();

                $value->cair_kode_jurnal = isset($cair) ? $cair->kode_jurnal : '';
                $value->cair_tanggal_giro = isset($cair) ? $cair->tanggal_jurnal : '';
                $value->cair_slip = isset($cair) ? $cair->nama_slip : '';

                $tolak = DB::table('jurnal_header as head')
                    ->join('jurnal_detail as det', 'det.id_jurnal', 'det.id_jurnal')
                    ->selectRaw('head.kode_jurnal,
                    head.tanggal_jurnal')
                    ->where('det.id_transaksi', $value->kode_jurnal)
                    ->where('head.tanggal_jurnal', $value->tanggal_giro_jt)
                    ->where('head.status_giro', 2)
                    ->where('head.void', '0')
                    ->first();

                $value->tolak_kode_jurnal = isset($tolak) ? $tolak->kode_jurnal : '';
                $value->tolak_tanggal_giro = isset($tolak) ? $tolak->tanggal_jurnal : '';
            }
            // Log::debug($data);

            $table['draw'] = $draw;
            $table['recordsTotal'] = $giro->count();
            $table['recordsFiltered'] = $filtered_data->count();
            $table['data'] = $data;
            return json_encode($table);
        } catch (\Exception $e) {
            $message = "Failed to get populate report giro.";
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
            return Excel::download(new ReportGiroExport($request->cabang, $request->slip, $request->tipe, $request->tanggal, $request->status), 'ReportGiros.xlsx');
        } catch (\Exception $e) {
            Log::error("Error when export excel report giro");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Error when export excel report giro",
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
            $tipe = $request->tipe;
            $tanggal = $request->tanggal;
            $status = $request->status;

            Log::debug($request->all());

            $giro = DB::table("jurnal_header as head")
                ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
                ->join('saldo_transaksi as saldo', 'saldo.id_jurnal', 'head.id_jurnal')
                ->selectRaw('head.id_jurnal,
                    head.tanggal_jurnal,
                    head.kode_jurnal,
                    head.no_giro,
                    head.tanggal_giro,
                    head.tanggal_giro_jt,
                    saldo.total')
                ->where('head.void', 0)
                ->where('head.id_cabang', $cabang)
                ->where('head.jenis_jurnal', $tipe)
                ->where('head.tanggal_giro_jt', '<=', $tanggal);

            if ($slip != 'All') {
                $giro = $giro->where('head.id_slip', $slip);
            }

            if ($status != 'All') {
                if ($status == 0) {
                    $giro = $giro->where('saldo.sisa', '>', 0);
                    $giro = $giro->where('saldo.status_giro', $status);
                } else {
                    $giro = $giro->where('saldo.sisa', 0);
                    $giro = $giro->where('saldo.status_giro', $status);
                }
            }

            $giro = $giro->groupBy('det.id_jurnal')
                ->orderBy('head.tanggal_jurnal', 'DESC');

            $data = $giro->get();

            foreach ($data as $key => $value) {
                $cair = DB::table('jurnal_header as head')
                    ->join('jurnal_detail as det', 'det.id_jurnal', 'det.id_jurnal')
                    ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
                    ->selectRaw('head.kode_jurnal,
                    head.tanggal_jurnal,
                    slip.nama_slip')
                    ->where('det.id_transaksi', $value->kode_jurnal)
                    ->where('head.tanggal_jurnal', $value->tanggal_giro_jt)
                    ->where('head.status_giro', 1)
                    ->where('head.void', '0')
                    ->first();

                $value->cair_kode_jurnal = isset($cair) ? $cair->kode_jurnal : '';
                $value->cair_tanggal_giro = isset($cair) ? $cair->tanggal_jurnal : '';
                $value->cair_slip = isset($cair) ? $cair->nama_slip : '';

                $tolak = DB::table('jurnal_header as head')
                    ->join('jurnal_detail as det', 'det.id_jurnal', 'det.id_jurnal')
                    ->selectRaw('head.kode_jurnal,
                    head.tanggal_jurnal')
                    ->where('det.id_transaksi', $value->kode_jurnal)
                    ->where('head.tanggal_jurnal', $value->tanggal_giro_jt)
                    ->where('head.status_giro', 2)
                    ->where('head.void', '0')
                    ->first();

                $value->tolak_kode_jurnal = isset($tolak) ? $tolak->kode_jurnal : '';
                $value->tolak_tanggal_giro = isset($tolak) ? $tolak->tanggal_jurnal : '';
            }

            $cabang = $cabang == 'All' ? 'All' : Cabang::find($cabang)->nama_cabang;
            $slip = $slip == 'All' ? 'All' : Slip::find($slip)->nama_slip;
            $tipe = $tipe == 'PG' ? 'Piutang Giro' : 'Hutang Giro';

            if ($status == '0') {
                $status = 'Belum Cair';
            } else if ($status == '1') {
                $status = 'Cair';
            } else if ($status == '2') {
                $status = 'Tolak';
            } else {
                $status = 'All';
            }

            $datas = [
                'data' => $data,
                'cabang' => $cabang,
                'slip' => $slip,
                'tanggal' => $tanggal,
                'tipe' => $tipe,
                'status' => $status,
            ];

            // return view('accounting.report.slip.print', $data);

            if (count($data) > 0) {
                $pdf = PDF::loadView('accounting.report.giro.print', $datas);
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
            $message = "Failed to print report giro";
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
            $slip = Slip::where('id_cabang', $request->cabang);

            if ($request->tipe == 'PG') {
                $slip = $slip->where('jenis_slip', 2);
            }

            if ($request->tipe == 'HG') {
                $slip = $slip->where('jenis_slip', 3);
            }

            $slip = $slip->get();

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
