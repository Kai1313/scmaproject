<?php

namespace App\Http\Controllers;

use App\Exports\ReportSlipExport;
use App\Models\Master\Cabang;
use App\Models\Master\Slip;
use Illuminate\Http\Request;
use Excel;
use PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportSlipController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'general_ledger', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = Cabang::all();
        $data_slip = Slip::all();

        $data = [
            "pageTitle" => "SCA Accounting | Report Slip",
            "data_slip" => $data_slip,
            "data_cabang" => $data_cabang,
        ];

        return view('accounting.report.slip.index', $data);
    }

    public function populate(Request $request)
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
            ->orderBy('head.tanggal_jurnal', 'ASC')
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
            ->orderBy('head.tanggal_jurnal', 'ASC')
            ->get();

        return [
            'saldo_awal' => $saldo_awal,
            'mutasis' => $mutasis,
            'cabang' => $cabang,
            'from' => $start_date,
            'to' => $end_date
        ];
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
            ->orderBy('head.tanggal_jurnal', 'ASC')
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
            ->orderBy('head.tanggal_jurnal', 'ASC')
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
            'to' => $end_date
        ];

        // return view('accounting.report.slip.print', $data);

        if(count($saldo_awal) > 0 && count($mutasis) > 0){
            $pdf = PDF::loadView('accounting.report.slip.print', $data);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->stream('ReportSlips.pdf');
        }else{
            return response()->json([
                'status' => false,
                'message' => 'No data found'
            ]);
        }

    }
}
