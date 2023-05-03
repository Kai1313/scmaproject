<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class MaterialUsageController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_pemakaian', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getData($request, 'datatable');
        }

        return view('report_ops.materialUsage.index', [
            "pageTitle" => "SCA OPS | Laporan Pemakaian | List",
            'typeReport' => ['Rekap', 'Detail'],
        ]);
    }

    function print(Request $request) {
        if (checkAccessMenu('laporan_pemakaian', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request, 'print');
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

    public function getData($request, $type)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);
        $idGudang = explode(',', $request->id_gudang);
        $statusQc = $request->status_qc;

        $data = DB::table('pemakaian_header as mu')->select(
            'tanggal',
            'kode_pemakaian',
            'c.nama_cabang',
            'g.nama_gudang',
            'catatan'
        )
            ->leftJoin('cabang as c', 'mu.id_cabang', 'c.id_cabang')
            ->leftJoin('gudang as g', 'mu.id_gudang', 'g.id_gudang')
            ->whereIn('mu.id_cabang', $idCabang)->whereIn('mu.id_gudang', $idGudang)
            ->orderBy('tanggal', 'asc');

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }
}
