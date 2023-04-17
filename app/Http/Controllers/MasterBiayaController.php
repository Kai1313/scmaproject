<?php

namespace App\Http\Controllers;

use App\MasterBiaya;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class MasterBiayaController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'master_biaya', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('master_biaya as mb')
                ->select('id_biaya', 'nama_biaya', 'ispph', 'isppn', 'value_pph', 'aktif', 'mb.dt_created as created_at', 'ma.nama_akun as akun_biaya', 'man.nama_akun as akun_pph')
                ->leftJoin('master_akun as ma', 'id_akun_biaya', '=', 'ma.id_akun')
                ->leftJoin('master_akun as man', 'id_akun_pph', '=', 'man.id_akun');
            if (isset($request->c)) {
                $data = $data->where('mb.id_cabang', $request->c);
            }

            $data = $data->orderBy('mb.dt_created', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="horizontal-list">';
                    $btn .= '<li><a href="' . route('master-biaya-view', $row->id_biaya) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    $btn .= '<li><a href="' . route('master-biaya-entry', $row->id_biaya) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    $btn .= '<li><a href="' . route('master-biaya-delete', $row->id_biaya) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Hapus</a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->editColumn('isppn', function ($row) {
                    return '<i class="fa fa-' . ($row->isppn ? 'check' : 'times') . '" aria-hidden="true"></i>';
                })
                ->editColumn('ispph', function ($row) {
                    return '<i class="fa fa-' . ($row->ispph ? 'check' : 'times') . '" aria-hidden="true"></i>';
                })
                ->editColumn('aktif', function ($row) {
                    return '<i class="fa fa-' . ($row->aktif ? 'check' : 'times') . '" aria-hidden="true"></i>';
                })
                ->rawColumns(['action', 'isppn', 'ispph', 'aktif'])
                ->make(true);
        }

        $cabang = session()->get('access_cabang');
        return view('ops.master.biaya.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Master Biaya | List",
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('master_biaya', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MasterBiaya::find($id);
        $akunBiaya = DB::table('master_akun')->where('isshown', 1)->get();
        $cabang = session()->get('access_cabang');
        return view('ops.master.biaya.form', [
            'data' => $data,
            'akunBiaya' => $akunBiaya,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Master Biaya | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id)
    {
        $data = MasterBiaya::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new MasterBiaya;
                $dat['user_created'] = session()->get('user')['id_pengguna'];
            } else {
                $data['user_modified'] = session()->get('user')['id_pengguna'];
            }

            $checkData = DB::table('master_biaya')
                ->where('id_cabang', $request->id_cabang)
                ->where('nama_biaya', $request->nama_biaya)
                ->where('id_biaya', '!=', $id)->first();
            if ($checkData) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Nama " . $request->nama_biaya . " sudah ada",
                ], 500);
            }

            $data->fill($request->all());
            $data['isppn'] = isset($request->isppn) ? $request->isppn : 0;
            $data['ispph'] = isset($request->ispph) ? $request->ispph : 0;
            $data['value_pph'] = normalizeNumber($request->value_pph);
            if ($data['ispph'] == 0) {
                $data['value_pph'] = null;
                $data['id_akun_pph'] = null;
            }

            $data['aktif'] = isset($request->aktif) ? $request->aktif : 0;
            $data->save();
            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil tersimpan",
                "redirect" => route('master-biaya'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save biaya");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function viewData($id)
    {
        if (checkAccessMenu('master_biaya', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MasterBiaya::find($id);
        return view('ops.master.biaya.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Master Biaya | Detail",
        ]);
    }

    public function destroy($id)
    {
        if (checkAccessMenu('master_biaya', 'delete') == false) {
            return response()->json(['message' => 'Tidak mempunyai akses'], 500);
        }

        $data = MasterBiaya::find($id);
        if (!$data) {
            return response()->json([
                "result" => false,
                "message" => "Data tidak ditemukan",
            ], 500);
        }

        try {
            DB::beginTransaction();
            $data->delete();
            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dihapus",
                "redirect" => route('master-biaya'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when delete biaya");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal dihapus",
            ], 500);
        }
    }
}
