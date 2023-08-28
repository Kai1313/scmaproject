<?php

namespace App\Http\Controllers;

use App\MaterialUsage;
use App\Models\Master\Pelanggan;
use App\Models\Master\Setting;
use App\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'marketing-tool/visit', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = Visit::with(['salesman', 'cabang', 'pelanggan'])->where('status', '!=', 3);
            if ($request->id_cabang != '') {
                $data = $data->where('id_cabang', $request->id_cabang);
            }

            if ($request->id_salesman != '') {
                $data = $data->where('id_salesman', $request->id_salesman);
            }

            if ($request->progress_ind != '') {
                if ($request->progress_ind == 0) {
                    $data = $data->whereNull('progress_ind');
                } else {
                    $data = $data->where('progress_ind', $request->progress_ind);
                }
            }

            if ($request->daterangepicker != '') {
                $data = $data->whereBetween('visit_date', [dateStore(explode(' - ', $request->daterangepicker)[0]), dateStore(explode(' - ', $request->daterangepicker)[1])]);
            }

            if ($request->status != '') {
                $data = $data->where('status', $request->status);
            }

            if ($request->status_pelanggan != '') {
                $data = $data->where('status_pelanggan', $request->status_pelanggan);
            }

            $data = $data->orderBy('created_at', 'desc');

            $idUser = session()->get('user')['id_pengguna'];
            $idGrupUser = session()->get('user')['id_grup_pengguna'];

            return DataTables::eloquent($data)
                ->addColumn('action', function ($data) {
                    return view('ops.visit.action', compact('data'));
                })
                ->addColumn('detail', function ($data) {
                    return view('ops.visit.detail', compact('data'));
                })
                ->editColumn('status', function ($data) {
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
                            return '';
                            break;
                    }
                })
                ->editColumn('progress_ind', function ($data) {
                    if ($data->progress_ind == null) {
                        return "<label class='label label-warning'>Belum Report</label>";
                    } else {
                        return "<label class='label label-primary'>Sudah Report</label>";
                    }
                })
                ->rawColumns(['action', 'status', 'progress_ind', 'detail'])
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

        // Setting::create([
        //     "id_cabang" => 2,
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
        if (checkAccessMenu('marketing-tool/visit', 'show') == false) {
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
        if (checkAccessMenu('marketing-tool/visit', 'delete') == false) {
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

    public function cancelVisit(Request $req): JsonResponse
    {
        try {
            $message = 'Berhasil membatalkan visit';
            $url = url('jadwal_kunjungan/index');
            $data = Visit::findOrFail($req->id);
            $data->alasan_pembatalan = $req->alasan_pembatalan;
            $data->status = 0;
            $data->save();
            Log::info("Berhasil cancel visit", $req->all());
            return response()->json([
                "result" => true,
                'url' => $url,
                "message" => $message,
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
            return response()->json([
                "result" => true,
                "data" => $req->only(['latitude', 'longitude']),
                "message" => $th->getMessage(),
            ], 500);
        }
    }
}
