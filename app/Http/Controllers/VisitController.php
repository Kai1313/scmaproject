<?php

namespace App\Http\Controllers;

use App\Salesman;
use App\Visit;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'marketing-tool/visit', 'show') == false) {
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
                $data = $data->where('status_pelanggan', $request->status_pelanggan);
            }

            $data = $data->orderBy('created_at', 'desc');

            $idUser = session()->get('user')['id_pengguna'];
            $idGrupUser = session()->get('user')['id_grup_pengguna'];

            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    return view('ops.visit.action', compact('data'));
                })
            // ->addColumn('detail', function ($data) {
            //     return view('ops.visit.detail', compact('data'));
            // })
                ->editColumn('status', function ($data) {
                    switch ($data->status) {
                        case '0':
                            return "<label class='label label-danger'>Batal Visit</label>";
                            break;
                        case '1':
                            return "<label class='label label-warning'>Belum Visit</label>";
                            break;
                        case '2':
                            $html = '';
                            if ($data->visit_type == 'LOKASI') {
                                $html .= "<label class='label label-primary'>Sudah Visit " . $data->visit_type . "</label>";
                            } else {
                                $html .= "<label class='label label-success'>Sudah Visit " . $data->visit_type . "</label>";
                            }
                            return $html;
                            break;
                        default:
                            return '';
                            break;
                    }
                })

                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        $salesman = Salesman::select('id_salesman as id', 'nama_salesman as text')->where('status_salesman', '1')->get();
        $customerCategory = Visit::$kategoriPelanggan;
        $cabang = session()->get('access_cabang');
        return view('ops.visit.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Visit | List",
            'salesmans' => $salesman,
            'customerCategory' => $customerCategory,
        ]);
    }
}
