<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
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

        return view('report_ops.purchaseDownPayment.print', [
            "pageTitle" => "SCA OPS | Laporan Uang Muka Pembelian | Print",
            "datas" => $data,
            'cabang' => implode(', ', $sCabang),
            'date' => $request->date,
        ]);
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
            's.nama_slip',
            'mu.nama_mata_uang',
            'ump.nominal'
        )
            ->leftJoin('cabang as c', 'ump.id_cabang', 'c.id_cabang')
            ->leftJoin('permintaan_pembelian as pp', 'ump.id_permintaan_pembelian', 'pp.id_permintaan_pembelian')
            ->leftJoin('master_slip as s', 'ump.id_slip', 's.id_slip')
            ->leftJoin('mata_uang as mu', 'ump.id_mata_uang', 'mu.id_mata_uang')->orderBy('tanggal', 'asc');

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }
}
