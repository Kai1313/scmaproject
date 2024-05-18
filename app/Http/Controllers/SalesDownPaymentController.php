<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\SalesDownPayment;
use App\TransactionBalance;
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
                ->select('ump.*',
                    'pp.nama_permintaan_penjualan',
                    DB::raw("concat(mu.kode_mata_uang,' - ',mu.nama_mata_uang) as nama_mata_uang"),
                    'nama_pelanggan'
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

            $idUser = session()->get('user')['id_pengguna'];
            $filterUser = DB::table('pengguna')
                ->where(function ($w) {
                    $w->where('id_grup_pengguna', session()->get('user')['id_grup_pengguna'])->orWhere('id_grup_pengguna', 1);
                })
                ->where('status_pengguna', '1')->pluck('id_pengguna')->toArray();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($filterUser, $idUser) {
                    $btn = '<ul class="horizontal-list">';
                    $btn .= '<li><a href="' . route('sales-down-payment-view', $row->id_uang_muka_penjualan) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    if ($row->void == '0' && (in_array($idUser, $filterUser) || $idUser == $row->user_created)) {
                        $btn .= '<li><a href="' . route('sales-down-payment-entry', $row->id_uang_muka_penjualan) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                        $btn .= '<li><a href="' . route('sales-down-payment-delete', $row->id_uang_muka_penjualan) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                    }

                    $btn .= '</ul>';
                    return $btn;
                })
                ->editColumn('nama_permintaan_penjualan', function ($row) {
                    return '<a target="_blank" href="' . env('OLD_URL_ROOT') . '#permintaan_penjualan&data_master2=' . $row->id_permintaan_penjualan . '">' . $row->nama_permintaan_penjualan . '</a>';
                })
                ->rawColumns(['action', 'nama_permintaan_penjualan'])
                ->make(true);
        }

        $cabang = session()->get('access_cabang');
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

        $cabang = session()->get('access_cabang');
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
                $period = $this->checkPeriod($request->tanggal);
                if ($period['result'] == false) {
                    return response()->json($period, 500);
                }
            } else {
                $period = $this->checkPeriod($data->tanggal);
                if ($period['result'] == false) {
                    return response()->json($period, 500);
                }
            }

            $data->fill($request->all());
            if ($id == 0) {
                $data->kode_uang_muka_penjualan = SalesDownPayment::createcode($request->id_cabang);
                $data->user_created = session()->get('user')['id_pengguna'];
                $data->void = 0;
            } else {
                $data->user_modified = session()->get('user')['id_pengguna'];
            }

            $data->rate = $request->rate;
            $data->nominal = $request->nominal;
            $data->total = $request->total;
            $data->konversi_nominal = $request->konversi_nominal;
            $data->dpp = $request->dpp;
            $data->ppn = $request->ppn;
            $data->save();

            //save saldo transaksi
            $resultSaldoTransaksi = (new ApiController)->transactionBalance(new Request([
                'tipe_transaksi' => 'Uang Muka Penjualan',
                'id_transaksi' => $data->kode_uang_muka_penjualan,
                'tanggal' => $data->tanggal,
                'ref_id' => $data->salesOrder->nama_permintaan_penjualan,
                'catatan' => $data->catatan,
                'id_pelanggan' => $data->salesOrder->id_pelanggan,
                'id_pemasok' => null,
                'dpp' => $data->dpp,
                'ppn' => $data->ppn,
                'uang_muka' => 0,
                'biaya' => 0,
                'tipe_pembayaran' => null,
            ]));

            if ($resultSaldoTransaksi->getData()->result == false) {
                DB::rollback();
                Log::error($resultSaldoTransaksi->getData()->message);
                Log::error($resultSaldoTransaksi);
                return response()->json([
                    "result" => false,
                    "message" => $resultSaldoTransaksi->getData()->message,
                ], 500);
            }

            $resultJurnalUangMukaPenjualan = (new ApiController)->journalUangMukaPenjualan(new Request([
                "no_transaksi" => $data->kode_uang_muka_penjualan,
                "tanggal" => $data->tanggal,
                "slip" => null,
                "cabang" => $data->id_cabang,
                "pelanggan" => $data->salesOrder->id_pelanggan,
                "void" => $data->void,
                "user" => session()->get('user')['id_pengguna'],
                "total" => $data->konversi_nominal,
                "uang_muka" => $data->dpp,
                "ppn" => $data->ppn,
            ]));

            if ($resultJurnalUangMukaPenjualan->getData()->result == false) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => $resultJurnalUangMukaPenjualan->getData()->message,
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

        $period = $this->checkPeriod($data->tanggal);
        if ($period['result'] == false) {
            return response()->json($period, 500);
        }

        try {
            DB::beginTransaction();
            $data->void = 1;
            $data->void_user_id = session()->get('user')['id_pengguna'];
            $data->save();

            $payment = TransactionBalance::where('id_transaksi', $data->kode_uang_muka_penjualan)->where('tipe_transaksi', 'Uang Muka Penjualan')->first();
            if ($payment) {
                if ($payment && $payment->bayar > 0) {
                    return response()->json([
                        "result" => false,
                        "message" => "Uang muka sudah terbayar",
                    ], 500);
                }

                $payment->delete();
            }

            $resultJurnalUangMukaPenjualan = (new ApiController)->journalUangMukaPenjualan(new Request([
                "no_transaksi" => $data->kode_uang_muka_penjualan,
                "tanggal" => $data->tanggal,
                "slip" => null,
                "cabang" => $data->id_cabang,
                "pelanggan" => $data->salesOrder->id_pelanggan,
                "void" => 1,
                "user" => session()->get('user')['id_pengguna'],
                "total" => $data->ppn_uang_muka_penjualan == '2' ? $data->konversi_nominal + $data->ppn : $data->konversi_nominal,
                "uang_muka" => $data->konversi_nominal,
                "ppn" => $data->ppn,
            ]));

            if ($resultJurnalUangMukaPenjualan->getData()->result == false) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => $resultJurnalUangMukaPenjualan->getData()->message,
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

        $datas = DB::table('permintaan_penjualan')
            ->select(
                'id_permintaan_penjualan as id',
                DB::raw('concat(nama_permintaan_penjualan," ( ",nama_pelanggan," )") as text'),
                DB::raw('case
                    when sisa is null then sum(mtotal_permintaan_penjualan)
                    else sum(sisa)
                end as mtotal_permintaan_penjualan')
            )
            ->leftJoin('saldo_transaksi', 'permintaan_penjualan.nama_permintaan_penjualan', 'saldo_transaksi.ref_id')
            ->leftJoin('pelanggan', 'permintaan_penjualan.id_pelanggan', 'pelanggan.id_pelanggan')
            ->whereBetween('tanggal_permintaan_penjualan', [$startDate, $endDate])
            ->where('permintaan_penjualan.id_cabang', $idCabang)
            ->where('status_approval_permintaan_penjualan', '1')
            ->where(function ($a) {
                $a->where('sisa', null)->orWhere('sisa', '>', '0');
            })
            ->groupBy('id_permintaan_penjualan')
            ->orderBy('tanggal_permintaan_penjualan', 'desc')
            ->orderBy('nama_permintaan_penjualan', 'desc')
            ->get();

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
            ->select('pp.mtotal_permintaan_penjualan', 'nilai_mata_uang', 'pp.id_mata_uang', 'mu.nama_mata_uang', 'ppn_permintaan_penjualan', 'kurs_permintaan_penjualan')
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
            'nilai_mata_uang' => $countDataSo->kurs_permintaan_penjualan,
            'id_mata_uang' => $countDataSo->id_mata_uang,
            'nama_mata_uang' => $countDataSo->nama_mata_uang,
            'ppn' => $countDataSo->ppn_permintaan_penjualan,
        ], 200);
    }

    public function checkPeriod($date)
    {
        if (!$date) {
            return ['result' => false, 'message' => 'Tanggal tidak ditemukan'];
        }

        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));

        $data = DB::table('periode')->where('tahun_periode', $year)->where('bulan_periode', $month)->first();
        if (!$data) {
            return ['result' => false, 'message' => 'Periode tidak ditemukan'];
        }

        if ($data->status_periode == '0') {
            return ['result' => false, 'message' => 'Periode sudah ditutup'];
        }

        return ['result' => true];
    }
}
