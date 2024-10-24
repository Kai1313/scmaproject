<?php

namespace App\Http\Controllers\Report;

use App\Exports\ReportMaterialUsageExport;
use App\Http\Controllers\Controller;
use DB;
use Excel;
use Illuminate\Http\Request;
use PDF;
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

    public function print(Request $request)
    {
        if (checkAccessMenu('laporan_pemakaian', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request, 'print');
        $arrayCabang = [];
        $arrayGudang = [];
        foreach (session()->get('access_cabang') as $c) {
            $arrayCabang[$c['id']] = $c['text'];
            foreach ($c['gudang'] as $g) {
                $arrayGudang[$g['id']] = $g['text'];
            }
        }

        $eCabang = explode(',', $request->id_cabang);
        $eGudang = explode(',', $request->id_gudang);
        $sCabang = [];
        $sGudang = [];
        foreach ($eCabang as $e) {
            $sCabang[] = $arrayCabang[$e];
        }

        foreach ($eGudang as $eg) {
            $sGudang[] = $arrayGudang[$eg];
        }

        $array = [
            "datas" => $data,
            'cabang' => implode(', ', $sCabang),
            'gudang' => implode(', ', $sGudang),
            'date' => $request->date,
            'type' => $request->type,
        ];

        $pdf = PDF::loadView('report_ops.materialUsage.print', $array);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('laporan pemakaian.pdf');
    }

    public function getExcel(Request $request)
    {
        if (checkAccessMenu('laporan_pemakaian', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request, 'print');
        $arrayCabang = [];
        $arrayGudang = [];
        foreach (session()->get('access_cabang') as $c) {
            $arrayCabang[$c['id']] = $c['text'];
            foreach ($c['gudang'] as $g) {
                $arrayGudang[$g['id']] = $g['text'];
            }
        }

        $eCabang = explode(',', $request->id_cabang);
        $eGudang = explode(',', $request->id_gudang);
        $sCabang = [];
        $sGudang = [];
        foreach ($eCabang as $e) {
            $sCabang[] = $arrayCabang[$e];
        }

        foreach ($eGudang as $eg) {
            $sGudang[] = $arrayGudang[$eg];
        }

        $array = [
            "datas" => $data,
            'cabang' => implode(', ', $sCabang),
            'gudang' => implode(', ', $sGudang),
            'date' => $request->date,
            'type' => $request->type,
        ];
        return Excel::download(new ReportMaterialUsageExport('report_ops.materialUsage.excel', $array), 'laporan pemakaian.xlsx');
    }

    public function getData($request, $type)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);
        $idGudang = explode(',', $request->id_gudang);
        $reportType = $request->type;
        switch ($reportType) {
            case 'Rekap':
                $data = DB::table('pemakaian_header as mu')->select(
                    'tanggal',
                    'kode_pemakaian',
                    'c.nama_cabang',
                    'g.nama_gudang',
                    'mu.jenis_pemakaian',
                    'catatan'
                )
                    ->leftJoin('cabang as c', 'mu.id_cabang', 'c.id_cabang')
                    ->leftJoin('gudang as g', 'mu.id_gudang', 'g.id_gudang')
                    ->whereBetween('tanggal', $date)
                    ->whereIn('mu.id_cabang', $idCabang)->whereIn('mu.id_gudang', $idGudang)
                    ->orderBy('tanggal', 'asc');
                break;
            case 'Detail':
                $data = DB::table('pemakaian_detail as pd')->select(
                    'ph.tanggal',
                    'ph.kode_pemakaian',
                    'c.nama_cabang',
                    'g.nama_gudang',
                    'ph.jenis_pemakaian',
                    'pd.kode_batang',
                    'b.nama_barang',
                    'pd.jumlah',
                    'pd.jumlah_zak',
                    'pd.weight_zak',
                    'ph.catatan as catatan_header',
                    'pd.catatan as catatan_detail',
                    'nama_satuan_barang'
                )
                    ->leftJoin('pemakaian_header as ph', 'pd.id_pemakaian', 'ph.id_pemakaian')
                    ->leftJoin('cabang as c', 'ph.id_cabang', 'c.id_cabang')
                    ->leftJoin('gudang as g', 'ph.id_gudang', 'g.id_gudang')
                    ->leftJoin('barang as b', 'pd.id_barang', 'b.id_barang')
                    ->leftJoin('satuan_barang as sb', 'pd.id_satuan_barang', 'sb.id_satuan_barang')
                    ->whereBetween('tanggal', $date)
                    ->whereIn('ph.id_cabang', $idCabang)->whereIn('ph.id_gudang', $idGudang)
                    ->orderBy('ph.tanggal', 'asc');
                break;

            default:
                $data = [];
                break;
        }

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }
}
