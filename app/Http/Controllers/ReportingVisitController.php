<?php

namespace App\Http\Controllers;

use App\Models\Master\Pelanggan;
use App\Models\Master\Setting;
use App\PermintaanPenjualan;
use App\Salesman;
use App\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Str;

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
    public function update(Request $req, $id)
    {
        return DB::transaction(function () use ($req, $id) {

            try {
                $data = Visit::find($id);
                Log::info("Berhasil update reporting visit", $data->toArray());
                $file = $req->file('proofment_1');
                if ($file != null) {
                    $path = 'asset/visit';
                    $id = Str::uuid($req->id . '1')->toString();
                    $name = $id . '.' . $file->getClientOriginalExtension();
                    $foto = $path . '/' . $name;
                    if (is_file($foto)) {
                        unlink($foto);
                    }

                    if (!file_exists($path)) {
                        $oldmask = umask(0);
                        mkdir($path, 0777, true);
                        umask($oldmask);
                    }


                    $img = \Image::make(file_get_contents($file))
                        ->encode($file->getClientOriginalExtension(), 12)
                        ->fit(150);
                    $img->save($foto);
                    $proofment_1 = url('/') . '/' .  $foto;
                } else {
                    $proofment_1 = $data->proofment_1;
                }

                $file = $req->file('proofment_2');
                if ($file != null) {
                    $path = 'asset/visit';
                    $id = Str::uuid($req->id . '2')->toString();
                    $name = $id . '.' . $file->getClientOriginalExtension();
                    $foto = $path . '/' . $name;
                    if (is_file($foto)) {
                        unlink($foto);
                    }

                    if (!file_exists($path)) {
                        $oldmask = umask(0);
                        mkdir($path, 0777, true);
                        umask($oldmask);
                    }


                    $img = \Image::make(file_get_contents($file))
                        ->encode($file->getClientOriginalExtension(), 12)
                        ->fit(500);
                    $img->save($foto);
                    $proofment_2 = url('/') . '/' . $foto;
                } else {
                    $proofment_2 = $data->proofment_2;
                }

                $data->update([
                    "visit_title" => request('visit_title'),
                    "visit_desc" => request('visit_desc'),
                    "progress_ind" => request('progress_ind'),
                    "range_potensial" => request('range_potensial'),
                    "proofment_1" => $proofment_1,
                    "proofment_2" => $proofment_2,
                    "permintaan_penjualan_id" => request('sales_order_id'),
                    "total" => filter_var(request('total'), FILTER_SANITIZE_NUMBER_FLOAT) * 1,
                ]);

                return response()->json([
                    "result" => true,
                    "data" => $data,
                    'url' => route('visit'),
                    "message" => "Berhiasil mensubmit report",
                ], 200);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    "result" => true,
                    "data" => $req->only(['latitude', 'longitude']),
                    "message" => $th->getMessage(),
                ], 500);
            }
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
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
