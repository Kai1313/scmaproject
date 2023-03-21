<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserToken;
use App\PurchaseDownPayment;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class PurchaseDownPaymentController extends Controller
{
    public function index(Request $request)
    {
        $checkAuth = $this->checkUser($request);
        if ($checkAuth['status'] == false) {
            return view('exceptions.forbidden');
        }

        if ($request->ajax()) {
            $data = DB::table('uang_muka_pembelian as ump')->select('id_uang_muka_pembelian', 'kode_uang_muka_pembelian', 'tanggal', 'pp.nama_permintaan_pembelian', DB::raw("concat(mu.kode_mata_uang,' - ',mu.nama_mata_uang) as nama_mata_uang"), 'nama_pemasok', 'rate', 'nominal', 'total', 'catatan', 'void')
                ->leftJoin('permintaan_pembelian as pp', 'ump.id_permintaan_pembelian', '=', 'pp.id_permintaan_pembelian')
                ->leftJoin('pemasok as p', 'pp.id_pemasok', '=', 'p.id_pemasok')
                ->leftJoin('mata_uang as mu', 'ump.id_mata_uang', '=', 'mu.id_mata_uang');

            if (isset($request->c)) {
                $data = $data->where('ump.id_cabang', $request->c);
            }

            if ($request->show_void == 'false') {
                $data = $data->where('ump.void', '0');
            }

            $data = $data->orderBy('ump.dt_created', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    if ($row->void == '1') {
                        $btn = '<label class="label label-default">Batal</label>';
                    } else {
                        $btn = '<ul class="horizontal-list">';
                        $btn .= '<li><a href="' . route('purchase-down-payment-view', $row->id_uang_muka_pembelian) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        $btn .= '<li><a href="' . route('purchase-down-payment-entry', $row->id_uang_muka_pembelian) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                        $btn .= '<li><a href="' . route('purchase-down-payment-delete', $row->id_uang_muka_pembelian) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                        $btn .= '</ul>';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.purchaseDownPayment.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Uang Muka Pembelian | List",
        ]);
    }

    public function entry($id = 0)
    {
        $data = PurchaseDownPayment::find($id);
        $remainingPayment = 0;
        if ($data) {
            $totalPO = DB::table('permintaan_pembelian')
                ->where('id_permintaan_pembelian', $data->id_permintaan_pembelian)
                ->value('mtotal_permintaan_pembelian');
            $totalPayment = DB::table('uang_muka_pembelian')
                ->where('id_permintaan_pembelian', $data->id_permintaan_pembelian)
                ->where('id_uang_muka_pembelian', '!=', $data->id_uang_muka_pembelian)
                ->where('void', 0)->sum('nominal');
            $remainingPayment = $totalPO - $totalPayment;
        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();
        $slip = DB::table('master_slip')->select('id_slip as id', DB::raw("CONCAT(kode_slip,' - ',nama_slip) as text"))
            ->get();
        return view('ops.purchaseDownPayment.form', [
            'data' => $data,
            'cabang' => $cabang,
            'maxPayment' => $remainingPayment,
            "pageTitle" => "SCA OPS | Uang Muka Pembelian | " . ($id == 0 ? 'Create' : 'Edit'),
            "slip" => $slip,
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = PurchaseDownPayment::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new PurchaseDownPayment;
            }

            $data->fill($request->all());
            if ($id == 0) {
                $data->kode_uang_muka_pembelian = PurchaseDownPayment::createcode($request->id_cabang);
                $data->user_created = session()->get('user')['id_pengguna'];
                $data->void = 0;
            } else {
                $data->user_modified = session()->get('user')['id_pengguna'];
            }

            $data->rate = normalizeNumber($request->rate);
            $data->nominal = normalizeNumber($request->nominal);
            $data->total = normalizeNumber($request->total);
            $data->konversi_nominal = normalizeNumber($request->konversi_nominal);
            $data->save();

            $resApi = $this->callApiJournal($data);
            $convertResApi = (array) json_decode($resApi);
            if ($convertResApi['result'] == false) {
                DB::rollback();
                Log::error($convertResApi['message']);
                Log::error($convertResApi);
                return response()->json([
                    "result" => false,
                    "message" => $convertResApi['message'],
                ]);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('purchase-down-payment'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save purchase down payment");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ]);
        }
    }

    public function viewData($id)
    {
        $data = PurchaseDownPayment::find($id);

        return view('ops.purchaseDownPayment.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Uang Muka Pembelian | Detail",
        ]);
    }

    public function destroy($id)
    {
        $data = PurchaseDownPayment::find($id);
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

            $resApi = $this->callApiJournal($data);
            $convertResApi = (array) json_decode($resApi);
            if ($convertResApi['result'] == false) {
                DB::rollback();
                Log::error($convertResApi['message']);
                Log::error($convertResApi);
                return response()->json([
                    "result" => false,
                    "message" => $convertResApi['message'],
                ]);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dibatalkan",
                "redirect" => route('purchase-down-payment'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void purchase down payment");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpana",
            ]);
        }
    }

    public function autoPo(Request $request)
    {
        $idCabang = $request->id_cabang;
        $duration = DB::table('setting')->where('code', 'Uang Muka Pembelian')->first();
        $startDate = date('Y-m-d', strtotime('-' . intval($duration->value2) . ' months'));
        $endDate = date('Y-m-d');
        $datas = DB::table('permintaan_pembelian as pp')->select('pp.id_permintaan_pembelian as id', 'nama_permintaan_pembelian as text', 'mtotal_permintaan_pembelian')
            ->leftJoin('uang_muka_pembelian as ump', function ($join) {
                $join->on('pp.id_permintaan_pembelian', '=', 'ump.id_permintaan_pembelian')
                    ->where('ump.void', 0);
            })
            ->whereBetween('tanggal_permintaan_pembelian', [$startDate, $endDate])
            ->where('pp.id_cabang', $idCabang)
            ->groupBy('pp.id_permintaan_pembelian')
            ->having(DB::raw('mtotal_permintaan_pembelian - COALESCE(sum(nominal),0)'), '<>', '0')
            ->orderBy('date_permintaan_pembelian', 'desc')->get();
        return response()->json([
            'result' => true,
            'data' => $datas,
        ]);
    }

    public function countPo(Request $request)
    {
        $po_id = $request->po_id;
        $id = $request->id;
        $countDataPo = DB::table('permintaan_pembelian as pp')
            ->select('pp.mtotal_permintaan_pembelian', 'nilai_mata_uang', 'pp.id_mata_uang')
            ->leftJoin('mata_uang as mu', 'pp.id_mata_uang', '=', 'mu.id_mata_uang')
            ->where('pp.id_permintaan_pembelian', $po_id)->first();
        $countData = DB::table('uang_muka_pembelian')
            ->where('id_permintaan_pembelian', $po_id)
            ->where('id_uang_muka_pembelian', '!=', $id)
            ->where('void', 0)
            ->sum('nominal');
        return response()->json([
            'status' => 'success',
            'nominal' => $countDataPo->mtotal_permintaan_pembelian - $countData,
            'total' => $countDataPo->mtotal_permintaan_pembelian,
            'nilai_mata_uang' => $countDataPo->nilai_mata_uang,
            'id_mata_uang' => $countDataPo->id_mata_uang,
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

    public function callApiJournal($data)
    {
        try {
            $date = date('Y-m-d H:i:s');
            $findToken = DB::table('token_pengguna')->where('id_pengguna', session()->get('user')['id_pengguna'])
                ->where('status_token_pengguna', '1')
                ->where('waktu_habis_token_pengguna', '>=', $date)
                ->where('nama2_token_pengguna', '!=', null)
                ->orderBy('date_token_pengguna', 'desc')->first();
            $token = $findToken->nama2_token_pengguna;
            if ($token) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, route('jurnal-otomatis-uangmuka-pembelian'));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Accept: application/json',
                    'Authorization: Bearer ' . $token,
                ));
                curl_setopt($ch, CURLOPT_POSTFIELDS,
                    http_build_query(
                        array(
                            "no_transaksi" => $data->kode_uang_muka_pembelian,
                            "tanggal" => $data->tanggal,
                            "slip" => $data->id_slip,
                            "cabang" => $data->id_cabang,
                            "pemasok" => $data->purchaseOrder->id_pemasok,
                            "void" => $data->void,
                            "user" => session()->get('user')['id_pengguna'],
                            "total" => $data->nominal,
                            "uang_muka" => $data->nominal,
                            "ppn" => 0,
                        )
                    )
                );
                $newData = curl_exec($ch);
                curl_close($ch);
                return $newData;
            } else {
                Log::error("Error when token tidak ditemukan");
                return response()->json([
                    "result" => false,
                    "message" => "Token tidak ditemukan",
                ]);
            }
        } catch (\Throwable $th) {
            Log::error("Error when gagal purchase down payment");
            Log::error($th);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ]);
        }
    }
}
