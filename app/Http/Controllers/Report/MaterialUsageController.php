<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\MaterialUsage;
use Illuminate\Http\Request;

class MaterialUsageController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_pemakaian', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = $this->getData($request);

            $html = '';
            $html .= view('report_ops.materialUsage.template', ['datas' => $data, 'type' => $request->type]);
            return response()->json([
                'html' => $html,
            ]);
        }

        // $duration = DB::table('setting')->where('code', 'U Duration')->first();
        // $countdown = $duration->value2;

        return view('report_ops.materialUsage.index', [
            "pageTitle" => "SCA OPS | Laporan Pemakaian | List",
            'typeReport' => ['Rekap', 'Detail'],
            // 'countDown' => $countdown,
        ]);
    }

    function print(Request $request) {
        if (checkAccessMenu('laporan_pemakaian', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request);
        $arrayCabang = [];
        foreach (session()->get('access_cabang') as $c) {
            $arrayCabang[$c['id']] = $c['text'];
        }

        $eCabang = explode(',', $request->id_cabang);
        $sCabang = [];
        foreach ($eCabang as $e) {
            $sCabang[] = $arrayCabang[$e];
        }

        return view('report_ops.materialUsage.print', [
            "pageTitle" => "SCA OPS | Laporan Pemakaian | Print",
            "datas" => $data,
            'cabang' => implode(', ', $sCabang),
            'date' => $request->date,
            'type' => $request->type,
        ]);
    }

    public function getData($request)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);
        $idGudang = explode(',', $request->id_gudang);
        $statusQc = $request->status_qc;

        $data = MaterialUsage::whereBetween('tanggal', $date)
            ->whereIn('id_cabang', $idCabang)->whereIn('id_gudang', $idGudang)
            ->orderBy('tanggal', 'desc')->get();
        return $data;
    }
}
