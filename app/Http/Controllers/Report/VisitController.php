<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Salesman;
use App\Visit;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VisitController extends Controller
{
    public function recapIndex(Request $request)
    {
        if (checkUserSession($request, 'marketing-tool/visit_recap_report', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getDataRecap($request);
        }

        $cabang = session()->get('access_cabang');
        $salesmans = Salesman::select('id_salesman as id', 'nama_salesman as text')->where('status_salesman', '1')->get();
        return view('ops.visit.recap_report', [
            "pageTitle" => "SCA OPS | Laporan Kunjungan | List",
            "cabang" => $cabang,
            'salesmans' => $salesmans,
        ]);
    }

    public function getDataRecap($request)
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

    public function index(Request $request)
    {
        if (checkUserSession($request, 'marketing-tool/visit_report', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = Visit::select('visit.*', 'salesman.nama_salesman', 'pelanggan.nama_pelanggan')
                ->leftJoin('salesman', 'visit.id_salesman', 'salesman.id_salesman')
                ->leftJoin('pelanggan', 'visit.id_pelanggan', 'pelanggan.id_pelanggan')
                ->where('visit.status', '!=', 3);
            if ($request->id_cabang) {
                $data = $data->where('id_cabang', $request->id_cabang);
            }

            if ($request->id_salesman) {
                $data = $data->where('id_salesman', $request->id_salesman);
            }

            if ($request->daterangepicker) {
                $explode = explode(' - ', $request->daterangepicker);
                $data = $data->whereBetween('visit_date', $explode);
            }

            if ($request->status) {
                $data = $data->where('status', $request->status);
            }

            if ($request->status_pelanggan) {
                $data = $data->where('visit.status_pelanggan', $request->status_pelanggan);
            }

            $data = $data->orderBy('visit_date', 'desc')->orderBy('visit_code', 'desc');

            $idUser = session()->get('user')['id_pengguna'];
            $idGrupUser = session()->get('user')['id_grup_pengguna'];

            return DataTables::of($data)
                ->make(true);
        }

        $salesman = Salesman::select('id_salesman as id', 'nama_salesman as text')->where('status_salesman', '1')->get();
        $customerCategory = Visit::$kategoriPelanggan;
        $cabang = session()->get('access_cabang');
        return view('ops.visit.report', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Laporan Kunjungan | List",
            'salesmans' => $salesman,
            'customerCategory' => $customerCategory,
        ]);
    }
}
