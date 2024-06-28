<?php

namespace App\Http\Controllers\Report;

use App\Exports\ReportVisit;
use App\Http\Controllers\Controller;
use App\Models\Master\Pelanggan;
use App\Salesman;
use App\Visit;
use DB;
use Excel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'marketing-tool/visit_report', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $idGrupUser = session()->get('user')['id_grup_pengguna'];
        $sales = Salesman::where('pengguna_id', session()->get('user')['id_pengguna'])->first();
        $activities = Visit::$progressIndicator;
        $initialActivities = Visit::$initialProgressIndicator;
        if ($request->ajax()) {
            if ($request->report_type == 'rekap') {
                return $this->getDataRecap('view', $request, $activities);
            } else {
                return $this->getDataDetail('view', $request);
            }
        }

        $cabang = session()->get('access_cabang');
        $salesmans = Salesman::select('id_salesman as id', 'nama_salesman as text')->get();
        return view('ops.visit.report', [
            "pageTitle" => "SCA OPS | Laporan Kunjungan | List",
            "cabang" => $cabang,
            'salesmans' => $salesmans,
            'activities' => $activities,
            'groupUser' => $idGrupUser,
            'idUser' => $sales ? $sales->id_salesman : '0',
            'initialActivities' => $initialActivities,
        ]);
    }

    public function getDataRecap($type, $request, $activities)
    {
        $arrayF = ['sales' => 'salesman.nama_salesman', 'date' => 'visit.visit_date', 'customer' => 'pelanggan.nama_pelanggan'];
        $data = Visit::select('visit.*', 'salesman.nama_salesman', 'pelanggan.nama_pelanggan')
            ->leftJoin('salesman', 'visit.id_salesman', 'salesman.id_salesman')
            ->leftJoin('pelanggan', 'visit.id_pelanggan', 'pelanggan.id_pelanggan')
        // ->where('visit.status', 2)
            ->orderBy($arrayF[$request->sort], $request->orderby);

        if ($request->date) {
            $explode = explode(' - ', $request->date);
            for ($i = 0; $i < count($explode); $i++) {
                if ($i == 0) {
                    $explode[$i] = $explode[$i] . ' 00:00:00';
                } else {
                    $explode[$i] = $explode[$i] . ' 23:59:59';
                }
            }

            $data = $data->whereBetween('visit_date', $explode);
        }

        if ($request->id_salesman) {
            $data = $data->where('visit.id_salesman', $request->id_salesman);
        }

        if ($request->id_pelanggan) {
            $data = $data->where('visit.id_pelanggan', $request->id_pelanggan);
        }

        $data = $data->get();
        $ac_values = [];
        foreach ($data as $d) {
            $prog = explode(', ', $d->progress_ind);
            foreach ($activities as $ac) {
                if (isset($ac_values[$ac])) {
                    $ac_values[$ac] = in_array($ac, $prog) ? $ac_values[$ac] += 1 : $ac_values[$ac] += 0;
                } else {
                    $ac_values[$ac] = in_array($ac, $prog) ? 1 : 0;
                }
            }

            if (!$d->progress_ind) {
                if (isset($ac_values['BELUM VISIT'])) {
                    $ac_values['BELUM VISIT'] = $ac_values['BELUM VISIT'] += 1;
                } else {
                    $ac_values['BELUM VISIT'] = 1;
                }
            }
        }

        if ($type == 'view') {
            $mainData = (string) view('ops.visit.template-report', [
                'datas' => $data,
                'activities' => $activities,
                'type' => 'main-data',
            ]);

            $recapData = (string) view('ops.visit.template-report', [
                'recap' => $ac_values,
                'type' => 'recap-data',
            ]);

            return response()->json([
                'result' => true,
                'htmlMainData' => $mainData,
                'htmlRecapData' => $recapData,
            ]);
        } else {
            return [
                'datas' => $data,
                'recap' => $ac_values,
            ];
        }
    }

    public function getDataDetail($type, $request)
    {
        $arrayF = ['sales' => 'salesman.nama_salesman', 'date' => 'visit.visit_date', 'customer' => 'pelanggan.nama_pelanggan'];
        $data = Visit::select('visit.*', 'salesman.nama_salesman', 'pelanggan.nama_pelanggan')
            ->leftJoin('salesman', 'visit.id_salesman', 'salesman.id_salesman')
            ->leftJoin('pelanggan', 'visit.id_pelanggan', 'pelanggan.id_pelanggan')
            ->where('visit.status', '!=', 0);

        if ($request->id_salesman) {
            $data = $data->where('visit.id_salesman', $request->id_salesman);
        }

        if ($request->id_pelanggan) {
            $data = $data->where('visit.id_pelanggan', $request->id_pelanggan);
        }

        if ($request->date) {
            $explode = explode(' - ', $request->date);
            $data = $data->whereBetween('visit_date', $explode);
        }

        $data = $data->orderBy($arrayF[$request->sort], $request->orderby)->orderBy('visit_code', $request->orderby);
        if ($type == 'view') {
            return DataTables::of($data)->make(true);
        } else {
            return $data = $data->get();
        }
    }

    public function getExcel(Request $request)
    {
        if (checkAccessMenu('marketing-tool/visit_report', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $activities = Visit::$progressIndicator;
        if ($request->report_type == 'rekap') {
            $result = $this->getDataRecap('excel', $request, $activities);
        } else {
            $result = $this->getDataDetail('excel', $request);
        }

        $array = [
            'result' => $result,
            'req' => $request,
            'activities' => $activities,
        ];

        return Excel::download(new ReportVisit('ops.visit.report_excel', $array), 'laporan kunjungan.xlsx');
    }

    public function getCustomer(Request $request)
    {
        $customerId = $request->search;
        $datas = DB::table('pelanggan')->select('nama_pelanggan as text', 'id_pelanggan as id')
            ->where(DB::raw('concat(nama_pelanggan," - ",alamat_pelanggan)'), 'like', '%' . $customerId . '%')
            ->where('status_pelanggan', '1')->get();

        return response()->json(['status' => 'success', 'datas' => $datas], 200);
    }

    public function getFirstCustomer(Request $request)
    {
        $id = $request->id;
        $data = Pelanggan::where('id_pelanggan', $id)->first();
        if (!$data) {
            return response()->json(['status' => 'error', 'message' => 'Data pelanggan tidak ditemukan'], 500);
        }

        $map = '<iframe src="https://www.google.com/maps?q=' . $data->nama_pelanggan . ' ' . $data->alamat_pelanggan . '&output=embed" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade""></iframe>';

        return response()->json(['status' => 'success', 'data' => $data, 'map' => $map], 200);
    }
}
