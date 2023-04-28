<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\SalesDownPayment;
use Illuminate\Http\Request;

class SalesDownPaymentController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_uang_muka_penjualan', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = $this->getData($request);

            $html = '';
            $html .= view('report_ops.salesDownPayment.template', [
                'datas' => $data,
            ]);
            return response()->json([
                'html' => $html,
            ]);
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

        return view('report_ops.salesDownPayment.print', [
            "pageTitle" => "SCA OPS | Laporan Uang Muka Penjualan | Print",
            "datas" => $data,
            'cabang' => implode(', ', $sCabang),
            'date' => $request->date,
        ]);
    }

    public function getData($request)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);

        $data = SalesDownPayment::whereBetween('tanggal', $date)
            ->whereIn('id_cabang', $idCabang)->where('void', 0);

        $data = $data->orderBy('tanggal', 'asc')->get();
        return $data;
    }
}
