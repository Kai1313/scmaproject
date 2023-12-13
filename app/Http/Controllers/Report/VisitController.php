<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Salesman;
use App\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'marketing-tool/visit_report', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getData($request);
        }

        $cabang = session()->get('access_cabang');
        $salesmans = Salesman::select('id_salesman as id', 'nama_salesman as text')->where('status_salesman', '1')->get();
        return view('ops.visit.report', [
            "pageTitle" => "SCA OPS | Laporan Kunjungan | List",
            "cabang" => $cabang,
            'salesmans' => $salesmans,
        ]);
    }

    public function getData($request)
    {
        $data = Visit::select('visit.*', 'salesman.nama_salesman', 'pelanggan.nama_pelanggan')
            ->leftJoin('salesman', 'visit.id_salesman', 'salesman.id_salesman')
            ->leftJoin('pelanggan', 'visit.id_pelanggan', 'pelanggan.id_pelanggan')
            ->where('visit.status', '!=', 3);

        if ($request->daterangepicker) {
            $explode = explode(' - ', $request->daterangepicker);
            $data = $data->whereBetween('visit_date', $explode);
        }

        $data = $data->get();
        $activities = Visit::$progressIndicator;
        $html = (string) view('ops.visit.template-report', ['datas' => $data, 'activities' => $activities]);
        return response()->json(['result' => true, 'html' => $html]);
    }
}
