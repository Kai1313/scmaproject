<?php

namespace App\Http\Controllers;

use App\Models\Master\Setting;
use App\Penjualan;
use App\Visit;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class ScheduleVisitController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'marketing-tool/pre_visit', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('visit as v')
                ->select(
                    'v.id',
                    'v.visit_code',
                    'v.visit_date',
                    's.nama_salesman',
                    'p.nama_pelanggan',
                    'p.alamat_pelanggan',
                    'v.pre_visit_desc',
                    'v.status',
                    'v.user_created'
                )
                ->whereIn('status', ['0', '1'])
                ->leftJoin('salesman as s', 's.id_salesman', '=', 'v.id_salesman')
                ->leftJoin('pelanggan as p', 'p.id_pelanggan', '=', 'v.id_pelanggan');
            if (isset($request->c)) {
                $data = $data->where('v.id_cabang', $request->c);
            }

            if (!$request->order) {
                $data = $data->orderBy('v.created_at', 'desc');
            }

            $idUser = session()->get('user')['id_pengguna'];
            $idGrupUser = session()->get('user')['id_grup_pengguna'];

            return Datatables::of($data)
                ->addColumn('action', function ($row) use ($idUser, $idGrupUser) {
                    if ($row->status == '0') {
                        $btn = '<label class="label label-default">Batal</label>';
                        $btn .= '<ul class="horizontal-list">';
                        $btn .= '<li><a href="' . route('pre_visit-view', $row->id) . '" class="btn btn-info btn-xs mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        $btn .= '</ul>';
                        return $btn;
                    } elseif ($row->status == '1') {
                        $btn = '<ul class="horizontal-list">';
                        // $btn .= '<li><a href="' . route('pre_visit-view', $row->id) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        if ($idUser == $row->user_created) {
                            $btn .= '<li><a href="' . route('pre_visit-entry', $row->id) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                            $btn .= '<li><a href="' . route('visit-entry', $row->id) . '" class="btn btn-success btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Buat Kunjungan</a></li>';
                        }

                        if ($idGrupUser == '1') {
                            $btn .= '<li><a href="' . route('pre_visit-void', $row->id) . '" class="btn btn-danger btn-xs mr-1 mb-1 btn-destroy"><i class="glyphicon glyphicon-trash"></i> Batal</a></li>';
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

    public function entry($id = 0)
    {
        // if (checkAccessMenu('pemakaian', $id == 0 ? 'create' : 'edit') == false) {
        //     return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        // }

        $data = Visit::find($id);
        $pelanggan = DB::table('pelanggan')->get();
        $salesman = DB::table('salesman')->get();
        $cabang = session()->get('access_cabang');
        return view('ops.scheduleVisit.form', [
            'data' => $data,
            'cabang' => $cabang,
            'salesman' => $salesman,
            'pelanggan' => $pelanggan,
            "pageTitle" => "SCA OPS | Pemakaian | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = Visit::find($id);
        // try {
        DB::beginTransaction();
        if (!$data) {
            $data = new Visit;
        }

        $checkCustomer = Penjualan::where('id_pelanggan', $request->id_pelanggan)->orderBy('tanggal_penjualan', 'DESC')->first();

        if ($checkCustomer) {
            $maxTanggalPenjualan = Setting::where('code', 'Treshold Customer Old')
                ->where('id_cabang', $request->id_cabang)
                ->first();

            if (!$maxTanggalPenjualan) {
                $maxTanggalPenjualan = Setting::create([
                    "id_cabang" => $request->id_cabang,
                    "code" => 'Treshold Customer Old',
                    "description" => 'Treshold Customer Old',
                    "tipe" => 1,
                    "value1" => "",
                    "value2" => "24",
                    "user_created" => 1,
                    "dt_created" => now(),
                    "user_modified" => 1,
                    "dt_modified" => now(),
                ]);
            }
            $this_month = Carbon::now();
            $start_month = Carbon::parse($checkCustomer->tanggal_penjualan);
            $diff = $start_month->diffInMonths($this_month);
            if ($diff >= $maxTanggalPenjualan->value2) {
                $data->status_pelanggan = 'OLD CUSTOMER';
            } else {
                $data->status_pelanggan = 'EXISTING CUSTOMER';
            }
        } else {
            $data->status_pelanggan = 'NEW CUSTOMER';
        }

        $data->fill($request->all());
        if ($id == 0) {
            $data->visit_code = Visit::createcode($request->id_cabang);
            $data->user_created = session()->get('user')['id_pengguna'];
        } else {
            $data->user_modified = session()->get('user')['id_pengguna'];
        }

        $data->save();

        DB::commit();
        return response()->json([
            "result" => true,
            "message" => "Data berhasil disimpan",
            "redirect" => route('pre_visit'),
        ], 200);
        // } catch (\Exception $e) {
        //     DB::rollback();
        //     Log::error("Error when save jadwal kunjungan");
        //     Log::error($e);
        //     return response()->json([
        //         "result" => false,
        //         "message" => "Data gagal tersimpan",
        //         'trace' => $e->getTrace(),
        //     ], 500);
        // }
    }

    public function viewData($id)
    {
        if (checkAccessMenu('marketing-tool/pre_visit', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = Visit::find($id);
        $accessQC = getSetting('Pemakaian QC');
        return view('ops.scheduleVisit.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Pemakaian | Detail",
            'accessQc' => in_array(session()->get('user')['id_grup_pengguna'], explode(',', $accessQC)) ? '1' : '0',
        ]);
    }

    public function void($id)
    {
        if (checkAccessMenu('marketing-tool/pre_visit', 'delete') == false) {
            return response()->json(['message' => 'Tidak mempunyai akses'], 500);
        }

        $data = Visit::find($id);
        if (!$data) {
            return response()->json([
                "result" => false,
                "message" => "Data tidak ditemukan",
            ], 500);
        }

        try {
            DB::beginTransaction();
            $data->status = 3;
            $data->save();

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dibatalkan",
                "redirect" => route('pre_visit'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void visit");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal dibatalkan",
            ], 500);
        }
    }

    public function appendMap(Request $req)
    {
        return view('ops.scheduleVisit.map', [
            'req' => $req,
        ]);
    }
}
