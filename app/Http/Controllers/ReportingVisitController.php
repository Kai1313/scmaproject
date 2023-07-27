<?php

namespace App\Http\Controllers;

use App\Models\Master\Pelanggan;
use App\Models\Master\Setting;
use App\PermintaanPenjualan;
use App\Salesman;
use App\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ReportingVisitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        // if (checkUserSession($request, 'pre_visit', 'show') == false) {
        //     return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        // }

        if ($req->ajax()) {
            $data = Visit::where(function ($q) use ($req) {
            });

            // if ($request->show_void == 'false') {
            //     $data = $data->where('pemakaian_header.void', '0');
            // }

            $data = $data->orderBy('v.created_at', 'desc');
            $idUser = session()->get('user')['id_pengguna'];
            $idGrupUser = session()->get('user')['id_grup_pengguna'];

            // dd($idUser);
            // $filterUser = DB::table('pengguna')
            //     ->where(function ($w) {
            //         $w->where('id_grup_pengguna', session()->get('user')['id_grup_pengguna'])->orWhere('id_grup_pengguna', 1);
            //     })
            //     ->where('status_pengguna', '1')->pluck('id_pengguna')->toArray();
            // $accessVoid = getSetting('Pemakaian Void');
            // $arrayAccessVoid = explode(',', $accessVoid);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($idUser) {
                    if ($row->status == '0') {
                        $btn = '<label class="label label-default">Batal</label>';
                        $btn .= '<ul class="horizontal-list">';
                        $btn .= '<li><a href="' . route('pre_visit-view', $row->id) . '" class="btn btn-info btn-xs mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        $btn .= '</ul>';
                        return $btn;
                    } elseif ($row->status == '1') {
                        $btn = '<ul class="horizontal-list">';
                        $btn .= '<li><a href="' . route('pre_visit-view', $row->id) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        if ($idUser == $row->user_created) {
                            $btn .= '<li><a href="' . route('pre_visit-entry', $row->id) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                            $btn .= '<li><a href="' . route('visit-entry', $row->id) . '" class="btn btn-success btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Buat Kunjungan</a></li>';
                        }
                        $btn .= '</ul>';
                        return $btn;
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $cabang = session()->get('access_cabang');
        return view('ops.scheduleVisit.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Pemakaian | List",
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Visit::find($id);

        $range = Setting::where('code', 'Range Checkin Kunjungan')
            ->where('id_cabang', $data->id_cabang)
            ->first();
        $pelanggan = Pelanggan::get();
        $salesman = Salesman::where('status_salesman', '1')->get();
        $cabang = session()->get('access_cabang');
        return view('ops.reportingVisit.form', [
            'data' => $data,
            'cabang' => $cabang,
            'salesman' => $salesman,
            'pelanggan' => $pelanggan,
            'range' => $range,
            "pageTitle" => "SCA OPS | Kunjungan | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function select(Request $req)
    {
        switch ($req->param) {
            case 'sales_order_id':
                return PermintaanPenjualan::select(DB::raw("id_permintaan_penjualan as id"), DB::raw("nama_permintaan_penjualan as text"), 'permintaan_penjualan.*')
                    ->whereDoesntHave('visit')
                    ->where(function ($q) use ($req) {
                        $q->where(DB::raw("UPPER(nama_permintaan_penjualan)"), 'like', '%' . strtoupper($req->q) . '%');
                    })
                    ->paginate(10);
                break;
            case 'id_salesman':
                return Salesman::select(DB::raw("id_salesman as id"), DB::raw("nama_salesman as text"), 'salesman.*')
                    ->where(function ($q) use ($req) {
                        $q->where(DB::raw("concat(UPPER(nama_salesman),' ',UPPER(kode_salesman))"), 'like', '%' . strtoupper($req->q) . '%');
                    })
                    ->paginate(10);
                break;

            default:
                # code...
                break;
        }
    }
}
