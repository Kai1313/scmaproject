<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\PurchaseDownPayment;
use Illuminate\Http\Request;

class PurchaseDownPaymentController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_uang_muka_pembelian', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = $this->getData($request);

            $html = '';
            $html .= view('report_ops.purchaseDownPayment.template', [
                'datas' => $data,
            ]);
            return response()->json([
                'html' => $html,
            ]);
        }

        return view('report_ops.purchaseDownPayment.index', [
            "pageTitle" => "SCA OPS | Laporan Uang Muka Pembelian | List",
            'typeReport' => ['Rekap', 'Detail'],
        ]);
    }

    function print(Request $request) {
        if (checkAccessMenu($request, 'laporan_uang_muka_pembelian', 'print') == false) {
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

        return view('report_ops.purchaseDownPayment.print', [
            "pageTitle" => "SCA OPS | Laporan Uang Muka Pembelian | Print",
            "datas" => $data,
            'cabang' => implode(', ', $sCabang),
            'date' => $request->date,
        ]);
    }

    public function getData($request)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);

        $data = PurchaseDownPayment::whereBetween('tanggal', $date)
            ->whereIn('id_cabang', $idCabang)->where('void', 0);

        $data = $data->orderBy('tanggal', 'asc')->get();
        return $data;
    }
}
