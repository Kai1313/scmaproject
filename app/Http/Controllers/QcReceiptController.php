<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserToken;
use App\QualityControl;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class QcReceiptController extends Controller
{
    public function index(Request $request)
    {
        $checkAuth = $this->checkUser($request);
        if ($checkAuth['status'] == false) {
            return view('exceptions.forbidden');
        }

        if ($request->ajax()) {
            $data = DB::table('qc');
            if (isset($request->c)) {
                $data = $data->where('qc.id_cabang', $request->c);
            }

            $data = $data->orderBy('qc.tanggal_qc', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {

                    $btn = '<ul class="horizontal-list">';
                    $btn .= '<li><a href="' . route('purchase-request-view', $row->purchase_request_id) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    $btn .= '<li><a href="' . route('purchase-request-entry', $row->purchase_request_id) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    $btn .= '<li><a href="' . route('purchase-request-delete', $row->purchase_request_id) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';

                    $btn .= '</ul>';

                    return $btn;
                })

                ->rawColumns(['action'])
                ->make(true);

        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.qualityControl.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | QC Permintaan Pembelian | List",
        ]);
    }

    public function entry($id = 0)
    {
        $data = QualityControl::find($id);
        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.qualityControl.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | QC Penerimaan Pembelian | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = QualityControl::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new QualityControl;
            }

            $data->fill($request->all());
            // if ($id == 0) {
            //     $data->purchase_request_code = PurchaseRequest::createcode($request->id_cabang);
            //     $data->approval_status = 0;
            //     $data->user_created = session()->get('user')['id_pengguna'];
            //     $data->void = 0;
            //     $data->purchase_request_user_id = session()->get('user')['id_pengguna'];
            // } else {
            //     $data->user_modified = session()->get('user')['id_pengguna'];
            // }

            $data->save();
            // $data->savedetails($request->details);

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('qc_receipt'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save qc receipt");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ]);
        }
    }

    public function viewData($id)
    {
        $data = QualityControl::find($id);

        return view('ops.qualityControl.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | QC Penerimaan Pembelian | Detail",
        ]);
    }

    public function destroy($id)
    {
        $data = QualityControl::find($id);
        if (!$data) {
            return response()->json([
                "result" => false,
                "message" => "Data tidak ditemukan",
            ]);
        }

        try {
            DB::beginTransaction();
            $data->void = 1;
            $data->void_user_id = session()->get('user')['id_pengguna'];
            $data->save();

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dibatalkan",
                "redirect" => route('qc_receipt'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void qc receipt");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal dibatalkan",
            ]);
        }
    }

    public function autoPurchasing(Request $request)
    {
        $cabang = $request->cabang;
        $datas = DB::table('pembelian')->select('nama_pembelian as text', 'id_pembelian as id')
            ->where('id_cabang', $cabang)
            ->get();

        return response()->json([
            'result' => true,
            'data' => $datas,
        ]);
    }

    public function autoItem(Request $request)
    {
        $idPembelian = $request->number;
        $datas = DB::table('pembelian_detail')
            ->select(DB::raw('sum(pembelian_detail.jumlah_pembelian_detail) as jumlah_pembelian_detail'), 'pembelian_detail.id_barang as id', 'barang.nama_barang as text', 'pembelian_detail.id_satuan_barang', 'nama_satuan_barang')
            ->leftJoin('barang', 'pembelian_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pembelian_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
            ->leftJoin('qc', function ($qc) {
                $qc->on('pembelian_detail.id_pembelian', '=', 'qc.id_pembelian')
                    ->on('pembelian_detail.id_barang', '=', 'qc.id_barang')
                    ->where('status_qc', 3);
            })
            ->where('pembelian_detail.id_pembelian', $idPembelian)
            ->groupBy('pembelian_detail.id_barang')->get();

        return response()->json([
            'result' => true,
            'data' => $datas,
        ]);
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
