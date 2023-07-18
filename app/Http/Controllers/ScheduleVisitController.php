<?php

namespace App\Http\Controllers;

use App\Visit;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class ScheduleVisitController extends Controller
{
    public function index(Request $request)
    {
        // if (checkUserSession($request, 'pre_visit', 'show') == false) {
        //     return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        // }

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
                    'v.user_created',
                )
                ->whereIn('status', ['0', '1'])
                ->leftJoin('salesman as s', 's.id_salesman', '=', 'v.id_salesman')
                ->leftJoin('pelanggan as p', 'p.id_pelanggan', '=', 'v.id_pelanggan');

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

            return Datatables::of($data)
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
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new Visit;
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
                "redirect" => route('pre_visit-entry', $data->id_pemakaian),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save jadwal kunjungan");
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

        $data = Visit::find($id);
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

}
