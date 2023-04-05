<?php

namespace App\Http\Controllers;

use App\SalesDownPayment;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class SalesDownPaymentController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'uang_muka_penjualan', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('uang_muka_penjualan as ump')
                ->select(
                    'id_uang_muka_penjualan',
                    'kode_uang_muka_penjualan',
                    'tanggal',
                    'pp.nama_permintaan_penjualan',
                    DB::raw("concat(mu.kode_mata_uang,' - ',mu.nama_mata_uang) as nama_mata_uang"),
                    'nama_pelanggan',
                    'rate',
                    'nominal',
                    'total',
                    'catatan',
                    'void'
                )
                ->leftJoin('permintaan_penjualan as pp', 'ump.id_permintaan_penjualan', '=', 'pp.id_permintaan_penjualan')
                ->leftJoin('pelanggan as p', 'pp.id_pelanggan', '=', 'p.id_pelanggan')
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
                        $btn .= '<li><a href="' . route('sales-down-payment-view', $row->id_uang_muka_penjualan) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        $btn .= '<li><a href="' . route('sales-down-payment-entry', $row->id_uang_muka_penjualan) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                        $btn .= '<li><a href="' . route('sales-down-payment-delete', $row->id_uang_muka_penjualan) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                        $btn .= '</ul>';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();
        return view('ops.salesDownPayment.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Uang Muka Penjualan | List",
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('uang_muka_penjualan', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = SalesDownPayment::find($id);
        $remainingPayment = 0;
        if ($data) {
            $totalPO = DB::table('permintaan_penjualan')
                ->where('id_permintaan_penjualan', $data->id_permintaan_penjualan)
                ->value('mtotal_permintaan_penjualan');
            $totalPayment = DB::table('uang_muka_penjualan')
                ->where('id_permintaan_penjualan', $data->id_permintaan_penjualan)
                ->where('id_uang_muka_penjualan', '!=', $data->id_uang_muka_pennjualan)
                ->where('void', 0)->sum('nominal');
            $remainingPayment = $totalPO - $totalPayment;
        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();
        $slip = DB::table('master_slip')->select('id_slip as id', DB::raw("CONCAT(kode_slip,' - ',nama_slip) as text"))
            ->get();
        return view('ops.salesDownPayment.form', [
            'data' => $data,
            'cabang' => $cabang,
            'maxPayment' => $remainingPayment,
            "pageTitle" => "SCA OPS | Uang Muka Penjualan | " . ($id == 0 ? 'Create' : 'Edit'),
            "slip" => $slip,
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = SalesDownPayment::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new SalesDownPayment;
            }

            $data->fill($request->all());
            if ($id == 0) {
                $data->kode_uang_muka_penjualan = SalesDownPayment::createcode($request->id_cabang);
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
                ], 500);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('sales-down-payment'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save sales down payment");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function viewData($id)
    {
        if (checkAccessMenu('uang_muka_penjualan', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = SalesDownPayment::find($id);
        return view('ops.salesDownPayment.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Uang Muka Penjualan | Detail",
        ]);
    }

    public function destroy($id)
    {
        if (checkAccessMenu('uang_muka_penjualan', 'delete') == false) {
            return response()->json(['message' => 'Tidak mempunyai akses'], 500);
        }

        $data = SalesDownPayment::find($id);
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

            $resApi = $this->callApiJournal($data);
            $convertResApi = (array) json_decode($resApi);
            if ($convertResApi['result'] == false) {
                DB::rollback();
                Log::error($convertResApi['message']);
                Log::error($convertResApi);
                return response()->json([
                    "result" => false,
                    "message" => $convertResApi['message'],
                ], 500);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dibatalkan",
                "redirect" => route('sales-down-payment'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void sales down payment");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpana",
            ], 500);
        }
    }

    public function autoSo(Request $request)
    {
        $idCabang = $request->id_cabang;
        $duration = DB::table('setting')->where('code', 'UMJ Duration')->first();
        $startDate = date('Y-m-d', strtotime('-' . intval($duration->value2) . ' days'));
        $endDate = date('Y-m-d');
        $datas = DB::table('permintaan_penjualan as pp')->select('pp.id_permintaan_penjualan as id', 'nama_permintaan_penjualan as text', 'mtotal_permintaan_penjualan', 'tanggal_permintaan_penjualan')
            ->leftJoin('uang_muka_penjualan as ump', function ($join) {
                $join->on('pp.id_permintaan_penjualan', '=', 'ump.id_permintaan_penjualan')
                    ->where('ump.void', 0);
            })
            ->whereBetween('tanggal_permintaan_penjualan', [$startDate, $endDate])
            ->where('pp.id_cabang', $idCabang)
            ->groupBy('pp.id_permintaan_penjualan')
            ->having(DB::raw('mtotal_permintaan_penjualan - COALESCE(sum(nominal),0)'), '<>', '0')
            ->orderBy('tanggal_permintaan_penjualan', 'desc')->get();
        return response()->json([
            'result' => true,
            'data' => $datas,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ], 200);
    }

    public function countSo(Request $request)
    {
        $so_id = $request->so_id;
        $id = $request->id;
        $countDataSo = DB::table('permintaan_penjualan as pp')
            ->select('pp.mtotal_permintaan_penjualan', 'nilai_mata_uang', 'pp.id_mata_uang', 'mu.nama_mata_uang')
            ->leftJoin('mata_uang as mu', 'pp.id_mata_uang', '=', 'mu.id_mata_uang')
            ->where('pp.id_permintaan_penjualan', $so_id)->first();
        $countData = DB::table('uang_muka_penjualan')
            ->where('id_permintaan_penjualan', $so_id)
            ->where('id_uang_muka_penjualan', '!=', $id)
            ->where('void', 0)
            ->sum('nominal');
        return response()->json([
            'status' => 'success',
            'nominal' => $countDataSo->mtotal_permintaan_penjualan - $countData,
            'total' => $countDataSo->mtotal_permintaan_penjualan,
            'nilai_mata_uang' => $countDataSo->nilai_mata_uang,
            'id_mata_uang' => $countDataSo->id_mata_uang,
            'nama_mata_uang' => $countDataSo->nama_mata_uang,
        ], 200);
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
                curl_setopt($ch, CURLOPT_URL, route('jurnal-otomatis-uangmuka-penjualan'));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Accept: application/json',
                    'Authorization: Bearer ' . $token,
                ));
                curl_setopt($ch, CURLOPT_POSTFIELDS,
                    http_build_query(
                        array(
                            "no_transaksi" => $data->kode_uang_muka_penjualan,
                            "tanggal" => $data->tanggal,
                            "slip" => $data->id_slip,
                            "cabang" => $data->id_cabang,
                            "pelanggan" => $data->salesOrder->id_pemasok,
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
                ], 500);
            }
        } catch (\Exception $th) {
            Log::error("Error when gagal sales down payment");
            Log::error($th);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }
}
