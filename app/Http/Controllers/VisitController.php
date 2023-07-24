<?php

namespace App\Http\Controllers;

use App\Models\Master\Pelanggan;
use App\Models\Master\Setting;
use App\Pengguna;
use App\Visit;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Yajra\DataTables\DataTables;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'pemakaian', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('pemakaian_header')
                ->select(
                    'id_pemakaian',
                    'kode_pemakaian',
                    'tanggal',
                    'g.nama_gudang',
                    'c.nama_cabang',
                    'user_created',
                    'catatan',
                    'is_qc',
                    'void'
                )
                ->leftJoin('gudang as g', 'pemakaian_header.id_gudang', '=', 'g.id_gudang')
                ->leftJoin('cabang as c', 'pemakaian_header.id_cabang', '=', 'c.id_cabang');
            if (isset($request->c)) {
                $data = $data->where('pemakaian_header.id_cabang', $request->c);
            }

            if ($request->show_void == 'false') {
                $data = $data->where('pemakaian_header.void', '0');
            }

            $data = $data->orderBy('pemakaian_header.dt_created', 'desc');

            $idUser = session()->get('user')['id_pengguna'];
            $idGrupUser = session()->get('user')['id_grup_pengguna'];
            $filterUser = DB::table('pengguna')
                ->where(function ($w) {
                    $w->where('id_grup_pengguna', session()->get('user')['id_grup_pengguna'])->orWhere('id_grup_pengguna', 1);
                })
                ->where('status_pengguna', '1')->pluck('id_pengguna')->toArray();
            $accessVoid = getSetting('Pemakaian Void');
            $arrayAccessVoid = explode(',', $accessVoid);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($filterUser, $idUser, $idGrupUser, $arrayAccessVoid) {
                    if ($row->void == '1') {
                        $btn = '<label class="label label-default">Batal</label>';
                        $btn .= '<ul class="horizontal-list">';
                        $btn .= '<li><a href="' . route('material_usage-view', $row->id_pemakaian) . '" class="btn btn-info btn-xs mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        $btn .= '</ul>';
                        return $btn;
                    } else {
                        $btn = '<ul class="horizontal-list">';
                        $btn .= '<li><a href="' . route('material_usage-view', $row->id_pemakaian) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        if (in_array($idUser, $filterUser) || $idUser == $row->user_created) {
                            $btn .= '<li><a href="' . route('material_usage-entry', $row->id_pemakaian) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                        }

                        if (in_array($idGrupUser, $arrayAccessVoid) || $idUser == $row->user_created) {
                            $btn .= '<li><a href="' . route('material_usage-delete', $row->id_pemakaian) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                        }

                        $btn .= '</ul>';
                        return $btn;
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $cabang = session()->get('access_cabang');
        return view('ops.materialUsage.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Pemakaian | List",
        ]);
    }

    public function entry($id = 0)
    {
        // if (checkAccessMenu('pemakaian', $id == 0 ? 'create' : 'edit') == false) {
        //     return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        // }
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

    public function saveEntry(Request $request, $id = 0)
    {
        $data = MaterialUsage::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new MaterialUsage;
            }

            $data->fill($request->except('is_qc'));
            if ($id == 0) {
                $data->kode_pemakaian = MaterialUsage::createcode($request->id_cabang);
                $data->user_created = session()->get('user')['id_pengguna'];
                $data->is_qc = isset($request->is_qc) ? $request->is_qc : 0;
            } else {
                $data->user_modified = session()->get('user')['id_pengguna'];
            }

            $data->save();

            $checkStock = $data->checkStockDetails($request->details);
            if ($checkStock['result'] == false) {
                DB::rollback();
                return response()->json([
                    "result" => $checkStock['result'],
                    "message" => $checkStock['message'],
                ], 500);
            }

            $data->savedetails($request->details);

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('material_usage-entry', $data->id_pemakaian),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save material usage");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
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

    public function submitLocation(Request $req): JsonResponse
    {
        try {
            if ($req->param == 'set_location') {
                $data = Pelanggan::findOrFail($req->pelanggan_id);
                $data->latitude_pelanggan = $req->latitude;
                $data->longitude_pelanggan = $req->longitude;
                $data->save();
            }
            return response()->json([
                "result" => true,
                "data" => $req->only(['latitude', 'longitude']),
                "message" => "Data lokasi pelanggan berhasil ditentukan",
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
