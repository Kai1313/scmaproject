<?php

namespace App\Http\Controllers\Report;

use App\Exports\ReportSalesDownPaymentExport;
use App\Http\Controllers\Controller;
use DB;
use Excel;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;

class SalesDownPaymentController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_uang_muka_penjualan', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getData($request, 'datatable');
        }

        return view('report_ops.salesDownPayment.index', [
            "pageTitle" => "SCA OPS | Laporan Uang Muka Penjualan | List",
            'typeReport' => ['Rekap', 'Detail'],
        ]);
    }

    function print(Request $request) {
        if (checkAccessMenu('laporan_uang_muka_penjualan', 'print') == false) {
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

        $array = [
            "datas" => $data,
            'cabang' => implode(', ', $sCabang),
            'date' => $request->date,
            'type' => $request->type,
        ];

        $pdf = PDF::loadView('report_ops.salesDownPayment.print', $array);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('laporan uang muka penjualan.pdf');
    }

    public function getExcel(Request $request)
    {
        if (checkAccessMenu('laporan_uang_muka_penjualan', 'print') == false) {
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

        $array = [
            "datas" => $data,
            'cabang' => implode(', ', $sCabang),
            'date' => $request->date,
            'type' => $request->type,
        ];
        return Excel::download(new ReportSalesDownPaymentExport('report_ops.salesDownPayment.excel', $array), 'laporan uang muka penjualan.xlsx');
    }

    public function getData($request, $type)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);

        $data = DB::table('uang_muka_penjualan as ump')->select(
            'ump.tanggal',
            'c.nama_cabang',
            'ump.kode_uang_muka_penjualan',
            'pp.nama_permintaan_penjualan',
            'p.nama_pelanggan',
            's.nama_slip',
            'mu.nama_mata_uang',
            'ump.nominal'
        )
            ->leftJoin('cabang as c', 'ump.id_cabang', 'c.id_cabang')
            ->leftJoin('permintaan_penjualan as pp', 'ump.id_permintaan_penjualan', 'pp.id_permintaan_penjualan')
            ->leftJoin('master_slip as s', 'ump.id_slip', 's.id_slip')
            ->leftJoin('mata_uang as mu', 'ump.id_mata_uang', 'mu.id_mata_uang')
            ->leftJoin('pelanggan as p', 'pp.id_pelanggan', 'p.id_pelanggan')
            ->whereBetween('ump.tanggal', $date)
            ->whereIn('ump.id_cabang', $idCabang)
            ->orderBy('tanggal', 'asc');

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }
}
