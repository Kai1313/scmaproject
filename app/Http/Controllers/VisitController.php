<?php

namespace App\Http\Controllers;

use App\MaterialUsage;
use App\Models\Master\Pelanggan;
use App\Models\Master\Setting;
use App\Pengguna;
use App\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        // dd(checkPenjualan(273, 1, '2022-05-25', '2022-05-25'));
        if (checkUserSession($request, 'pemakaian', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }


        if ($request->ajax()) {
            $data = Visit::where(function ($q) use ($request) {
                if ($request->id_cabang != '') {
                    $q->where('id_cabang', $request->id_cabang);
                }

                if ($request->id_salesman != '') {
                    $q->where('id_salesman', $request->id_salesman);
                }

                if ($request->progress_ind != '') {
                    if ($request->progress_ind == 0) {
                        $q->whereNull('progress_ind');
                    } else {
                        $q->where('progress_ind', $request->progress_ind);
                    }
                }

                if ($request->status != '') {
                    $q->where('status', $request->status);
                }
            })->orderBy('created_at', 'desc');

            // if ($request->show_void == 'false') {
            //     $data = $data->where('pemakaian_header.void', '0');
            // }

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

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('actions', function ($row) use ($idUser) {
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
                ->addColumn('action', function ($data) {
                    return view('ops.visit.action', compact('data'));
                })
                ->addColumn('nama_cabang', function ($data) {
                    return $data->nama_cabang;
                })
                ->addColumn('nama_salesman', function ($data) {
                    return $data->nama_salesman;
                })
                ->addColumn('nama_pelanggan', function ($data) {
                    return $data->nama_pelanggan;
                })
                ->addColumn('detail', function ($data) {
                    return view('ops.visit.detail', compact('data'));
                })
                ->addColumn('status', function ($data) {
                    switch ($data->status) {
                        case '0':
                            return "<label class='label label-danger'>Batal Visit</label>";
                            break;
                        case '1':
                            return "<label class='label label-warning'>Belum Visit</label>";
                            break;
                        case '2':
                            return "<label class='label label-primary'>Sudah Visit</label>";
                            break;
                        default:
                            # code...
                            break;
                    }
                })
                ->addColumn('status_report', function ($data) {
                    if ($data->progress_ind == null) {
                        return "<label class='label label-warning'>Belum Report</label>";
                    } else {
                        return "<label class='label label-primary'>Sudah Report</label>";
                    }
                })
                ->rawColumns(['action', 'status', 'status_report'])
                ->make(true);
        }

        $cabang = session()->get('access_cabang');
        return view('ops.visit.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Visit | List",
        ]);
    }

    public function entry($id = 0)
    {
        // if (checkAccessMenu('pemakaian', $id == 0 ? 'create' : 'edit') == false) {
        //     return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        // }
        // Setting::create([
        //     "id_cabang" => 1,
        //     "code" => "Range Checkin Kunjungan",
        //     "description" => "Range Checkin Kunjungan",
        //     "tipe" => 1,
        //     "value1" => "",
        //     "value2" => "100",
        //     "user_created" => 1,
        //     "dt_created" => now(),
        //     "user_modified" => 1,
        //     "dt_modified" => now(),
        // ]);

        $data = Visit::find($id);

        $range = Setting::where('code', 'Range Checkin Kunjungan')
            ->where('id_cabang', $data->id_cabang)
            ->first();
        $pelanggan = DB::table('pelanggan')->get();
        $salesman = DB::table('salesman')->get();
        $cabang = session()->get('access_cabang');
        return view('ops.visit.form', [
            'data' => $data,
            'cabang' => $cabang,
            'salesman' => $salesman,
            'pelanggan' => $pelanggan,
            'range' => $range,
            "pageTitle" => "SCA OPS | Kunjungan | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function viewData($id)
    {
        if (checkAccessMenu('pemakaian', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MaterialUsage::find($id);
        $accessQC = getSetting('Pemakaian QC');
        return view('ops.materialUsage.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Pemakaian | Detail",
            'accessQc' => in_array(session()->get('user')['id_grup_pengguna'], explode(',', $accessQC)) ? '1' : '0',
        ]);
    }

    public function destroy($id)
    {
        if (checkAccessMenu('pemakaian', 'delete') == false) {
            return response()->json(['message' => 'Tidak mempunyai akses'], 500);
        }

        $data = MaterialUsage::find($id);
        if (!$data) {
            return response()->json([
                "result" => false,
                "message" => "Data tidak ditemukan",
            ], 500);
        }

        try {
            DB::beginTransaction();
            $data->void = 1;
            $data->void_user_id = session()->get('user')['id_pengguna'];
            $data->save();

            $data->voidDetails();

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dibatalkan",
                "redirect" => route('material_usage'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void pemakaian");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal dibatalkan",
            ], 500);
        }
    }

    public function updateVisit(Request $req): JsonResponse
    {
        try {
            $message = '';
            $url = url('visit.reporting.index');
            if ($req->param == 'set_location') {
                $data = Pelanggan::findOrFail($req->pelanggan_id);
                $data->latitude_pelanggan = $req->latitude;
                $data->longitude_pelanggan = $req->longitude;
                $data->save();
                $message = "Data lokasi pelanggan berhasil ditentukan";
                $url = null;
            } elseif ($req->param == 'checkin') {
                $data = Visit::findOrFail($req->id);
                $data->latitude_visit = $req->latitude_visit;
                $data->longitude_visit = $req->longitude_visit;
                $data->visit_type = 'LOKASI';
                $data->status = 2;
                $data->save();
                $message = "Berhasil checkin";
                $url = route('visit.reporting.show', [$req->id]);
            } else {
                $data = Visit::findOrFail($req->id);
                $data->visit_type = strtoupper($req->param);
                $data->status = 2;
                $data->save();
                $message = "Berhasil mengupdate status visit";
                $url = route('visit.reporting.show', [$req->id]);
            }



            Log::info("Berhasil update status visit", $data->toArray());
            return response()->json([
                "result" => true,
                "data" => $req->only(['latitude', 'longitude']),
                'url' => $url,
                "message" => $message,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "result" => true,
                "data" => $req->only(['latitude', 'longitude']),
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    function cancelVisit(Request $req): JsonResponse
    {
        try {
            $message = 'Berhasil membatalkan visit';
            $url = url('jadwal_kunjungan/index');
            $data = Visit::findOrFail($req->id);
            $data->alasan_pembatalan = $req->alasan_pembatalan;
            $data->status = 0;
            $data->save();
            return response()->json([
                "result" => true,
                'url' => $url,
                "message" => $message,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "result" => true,
                "data" => $req->only(['latitude', 'longitude']),
                "message" => $th->getMessage(),
            ], 500);
        }
    }
}
