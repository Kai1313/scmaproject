<?php

namespace App\Http\Controllers\Report;

use App\Exports\ReportPurchaseDownPaymentExport;
use App\Http\Controllers\Controller;
use DB;
use Excel;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;

class PurchaseDownPaymentController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_uang_muka_pembelian', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getData($request, 'datatable');
        }

        return view('report_ops.purchaseDownPayment.index', [
            "pageTitle" => "SCA OPS | Laporan Uang Muka Pembelian | List",
            'typeReport' => ['Rekap', 'Detail'],
        ]);
    }

    function print(Request $request) {
        if (checkAccessMenu('laporan_uang_muka_pembelian', 'print') == false) {
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

        $pdf = PDF::loadView('report_ops.purchaseDownPayment.print', $array);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('laporan uang muka pembelian.pdf');
    }

    public function getExcel(Request $request)
    {
        if (checkAccessMenu('laporan_uang_muka_pembelian', 'print') == false) {
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
        return Excel::download(new ReportPurchaseDownPaymentExport('report_ops.purchaseDownPayment.excel', $array), 'laporan uang muka pembelian.xlsx');
    }

    public function getData($request, $type)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);

        $data = DB::table('uang_muka_pembelian as ump')->select(
            'ump.tanggal',
            'c.nama_cabang',
            'ump.kode_uang_muka_pembelian',
            'pp.nama_permintaan_pembelian',
            'p.nama_pemasok',
            's.nama_slip',
            'mu.nama_mata_uang',
            'ump.nominal'
        )
            ->leftJoin('cabang as c', 'ump.id_cabang', 'c.id_cabang')
            ->leftJoin('permintaan_pembelian as pp', 'ump.id_permintaan_pembelian', 'pp.id_permintaan_pembelian')
            ->leftJoin('master_slip as s', 'ump.id_slip', 's.id_slip')
            ->leftJoin('mata_uang as mu', 'ump.id_mata_uang', 'mu.id_mata_uang')
            ->leftJoin('pemasok as p', 'pp.id_pemasok', 'p.id_pemasok')
            ->whereBetween('ump.tanggal', $date)
            ->whereIn('ump.id_cabang', $idCabang)
            ->where('void', 0)
            ->orderBy('ump.tanggal', 'asc');

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }
}
