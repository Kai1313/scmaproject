<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserToken;
use App\MoveWarehouse;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class ReceivedFromBranchController extends Controller
{
    public function index(Request $request)
    {
        $checkAuth = $this->checkUser($request);
        if ($checkAuth['status'] == false) {
            return view('exceptions.forbidden');
        }

        if ($request->ajax()) {
            $data = DB::table('pindah_gudang')
                ->select('id_pindah_gudang', 'type', 'nama_gudang', 'tanggal_pindah_gudang', 'kode_pindah_gudang', 'nama_cabang', 'status_pindah_gudang', 'keterangan_pindah_gudang', 'transporter')
                ->leftJoin('gudang', 'pindah_gudang.id_gudang', '=', 'gudang.id_gudang')
                ->leftJoin('cabang', 'pindah_gudang.id_cabang_tujuan', '=', 'cabang.id_cabang')
                ->where('type', 1);
            if (isset($request->c)) {
                $data = $data->where('pindah_gudang.id_cabang', $request->c);
            }

            $data = $data->orderBy('pindah_gudang.tanggal_pindah_gudang', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="horizontal-list">';
                    $btn .= '<li><a href="' . route('send_to_branch-view', $row->id_pindah_gudang) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    $btn .= '<li><a href="' . route('send_to_branch-entry', $row->id_pindah_gudang) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    $btn .= '<li><a href="' . route('send_to_branch-delete', $row->id_pindah_gudang) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->editColumn('status_pindah_gudang', function ($row) {
                    if ($row->status_pindah_gudang == '0') {
                        return '<label class="label label-warning">Dalam Perjalanan</label>';
                    } else if ($row->status_pindah_gudang == '1') {
                        return '<label class="label label-success">Diterima</label>';
                    } else {
                        return '';
                    }
                })
                ->rawColumns(['action', 'status_pindah_gudang'])
                ->make(true);
        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.receivedFromBranch.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Terima Dari Cabang | List",
        ]);
    }

    public function entry($id = 0)
    {
        $data = [];
        $cabang = DB::table('cabang')->select('nama_cabang as text', 'id_cabang as id')->where('status_cabang', 1)->get();

        return view('ops.receivedFromBranch.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Terima Dari Cabang | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = MoveWarehouse::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new MoveWarehouse;
            }

            $data->fill($request->all());
            if ($id == 0) {
                $data->status_pindah_gudang = 1;
                $data->tujuan_pindah_gudang = $request->id_cabang;
                $data->type = 1;
            }

            $data->user_pindah_gudang = session()->get('user')['id_pengguna'];
            $data->date_pindah_gudang = date('Y-m-d H:i:s');
            $data->save();
            // $data->saveDetails($request->details);

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('received_from_branch'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save purchase request");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ]);
        }
    }

    public function viewData($id)
    {
        $data = [];

        return view('ops.purchaseRequest.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Terima Dari Cabang | Detail",
        ]);
    }

    public function destroy($id)
    {
        // $data = PurchaseRequest::find($id);
        // if (!$data) {
        //     return response()->json([
        //         "result" => false,
        //         "message" => "Data tidak ditemukan",
        //     ]);
        // }

        // try {
        //     DB::beginTransaction();
        //     $data->void = 1;
        //     $data->void_user_id = session()->get('user')['id_pengguna'];
        //     $data->save();

        //     DB::commit();
        //     return response()->json([
        //         "result" => true,
        //         "message" => "Data berhasil dibatalkan",
        //         "redirect" => route('purchase-request'),
        //     ]);
        // } catch (\Exception $e) {
        //     DB::rollback();
        //     Log::error("Error when void purchase request");
        //     Log::error($e);
        //     return response()->json([
        //         "result" => false,
        //         "message" => "Data gagal dibatalkan",
        //     ]);
        // }
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

    public function autoCode(Request $request)
    {
        $idCabang = $request->cabang;
        $data = MoveWarehouse::where('status_pindah_gudang', 0)
            ->select('kode_pindah_gudang as text', 'kode_pindah_gudang as id', 'id_pindah_gudang', 'transporter', 'nomor_polisi', 'nama_gudang', 'keterangan_pindah_gudang')
            ->leftJoin('gudang', 'pindah_gudang.id_gudang', '=', 'gudang.id_gudang')
            ->where('id_cabang_tujuan', $idCabang)->get();

        return response()->json([
            'status' => 200,
            'data' => $data,
            'message' => '',
        ]);
    }

    public function getDetailItem(Request $request)
    {
        $data = MoveWarehouse::find($request->id);
        $datas = [];
        if ($data) {
            $datas = $data->formatReceivedDetail;
        }

        return response()->json([
            'status' => 200,
            'data' => $datas,
            'message' => '',
        ]);
    }
}
