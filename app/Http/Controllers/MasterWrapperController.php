<?php

namespace App\Http\Controllers;

use App\MasterWrapper;
use App\Models\User;
use App\Models\UserToken;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class MasterWrapperController extends Controller
{
    public function index(Request $request)
    {
        $checkAuth = $this->checkUser($request);
        if ($checkAuth['status'] == false) {
            return view('exceptions.forbidden');
        }

        if ($request->ajax()) {
            $data = DB::table('master_wrapper')
                ->select('id_wrapper', 'nama_wrapper', 'weight', 'catatan', 'path2', 'path', 'dt_created as created_at');
            if (isset($request->c)) {
                $data = $data->where('id_cabang', $request->c);
            }

            $data = $data->orderBy('master_wrapper.dt_created', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="horizontal-list">';
                    $btn .= '<li><a href="' . route('master-wrapper-view', $row->id_wrapper) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    $btn .= '<li><a href="' . route('master-wrapper-entry', $row->id_wrapper) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    $btn .= '<li><a href="' . route('master-wrapper-delete', $row->id_wrapper) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Hapus</a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->editColumn('path2', function ($row) use ($request) {
                    if ($request->show_img == "true") {
                        return '<img src="' . env('FTP_GET_FILE') . $row->path2 . '" width="100">';
                    } else {
                        return '<span style="color:#a9a9a9;">Gambar tidak ditampilkan</span>';
                    }
                })
                ->rawColumns(['action', 'path2'])
                ->make(true);
        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.master.wrapper.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Master Wrapper | List",
        ]);
    }

    public function entry($id = 0)
    {
        $data = MasterWrapper::find($id);
        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.master.wrapper.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Master Wrapper | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id)
    {
        $data = MasterWrapper::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new MasterWrapper;
                $data['dt_created'] = date('Y-m-d H:i:s');
            } else {
                $data['dt_modified'] = date('Y-m-d H:i:s');
            }

            $checkData = DB::table('master_wrapper')
                ->where('id_cabang', $request->id_cabang)
                ->where('nama_wrapper', $request->nama_wrapper)
                ->where('id_wrapper', '!=', $id)->first();
            if ($checkData) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Nama " . $request->nama_wrapper . " sudah ada",
                ]);
            }

            $data->fill($request->all());
            $data['weight'] = normalizeNumber($request->weight);
            $data->save();

            $data->uploadfile($request, $data);

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('master-wrapper'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save wrapper");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ]);
        }
    }

    public function viewData($id)
    {
        $data = MasterWrapper::find($id);

        return view('ops.master.wrapper.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Master Wrapper | Detail",
        ]);
    }

    public function destroy($id)
    {
        $data = MasterWrapper::find($id);
        if (!$data) {
            return response()->json(['message' => 'data tidak ditemukan'], 500);
        }

        if ($data && $data->path) {
            \Storage::delete([$data->path, $data->path2]);
        }

        try {
            DB::beginTransaction();
            $data->delete();
            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dihapus",
                "redirect" => route('master-wrapper'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when delete biaya");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal dihapus",
            ]);
        }
    }

    public function checkUser($request)
    {
        $user_id = $request->user_id;
        if ($user_id != '' && $request->session()->has('token') == false || $request->session()->has('token') == true) {
            if ($request->session()->has('token') == true) {
                $user_id = $request->session()->get('user')->id_pengguna;
            }
            $user = User::where('id_pengguna', $user_id)->first();
            $token = UserToken::where('id_pengguna', $user_id)->where('status_token_pengguna', 1)->whereRaw("waktu_habis_token_pengguna > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", Carbon::now()->format('Y-m-d H:i:s'))->first();

            $sql = "SELECT
                a.id_pengguna,
                a.id_grup_pengguna,
                d.id_menu,
                d.nama_menu,
                c.lihat_akses_menu,
                c.tambah_akses_menu,
                c.ubah_akses_menu,
                c.hapus_akses_menu,
                c.cetak_akses_menu
            FROM
                pengguna a,
                grup_pengguna b,
                akses_menu c,
                menu d
            WHERE
                a.id_grup_pengguna = b.id_grup_pengguna
                AND b.id_grup_pengguna = c.id_grup_pengguna
                AND c.id_menu = d.id_menu
                AND a.id_pengguna = $user_id
                AND d.keterangan_menu = 'Accounting'
                AND d.status_menu = 1";
            $access = DB::connection('mysql')->select($sql);

            $user_access = array();
            foreach ($access as $value) {
                $user_access[$value->nama_menu] = ['show' => $value->lihat_akses_menu, 'create' => $value->tambah_akses_menu, 'edit' => $value->ubah_akses_menu, 'delete' => $value->hapus_akses_menu, 'print' => $value->cetak_akses_menu];
            }

            $idGroup = $user->id_grup_pengguna;
            $menu_access = DB::table('menu')->select('menu.id_menu', 'kepala_menu', 'alias_menu', 'lihat_akses_menu', 'tingkatan_menu', 'nama_menu')
                ->leftJoin('akses_menu', 'menu.id_menu', '=', 'akses_menu.id_menu')
                ->where('akses_menu.id_grup_pengguna', $idGroup)
                ->where('lihat_akses_menu', '1')
                ->where('alias_menu', 'not like', '%detail')
                ->get();
            $request->session()->put('menu_access', $menu_access);

            if ($token && $request->session()->has('token') == false) {
                $request->session()->put('token', $token->nama_token_pengguna);
                $request->session()->put('user', $user);
                $request->session()->put('access', $user_access);
            } else if ($request->session()->has('token')) {
            } else {
                $request->session()->flush();
            }

            $session = $request->session()->get('access');

            return ['status' => true];
        } else {
            $request->session()->flush();
            return ['status' => false];
        }
    }
}
