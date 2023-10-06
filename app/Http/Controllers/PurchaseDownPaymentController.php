<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\PurchaseDownPayment;
use App\TransactionBalance;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class PurchaseDownPaymentController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'uang_muka_pembelian', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('uang_muka_pembelian as ump')
                ->select(
                    'id_uang_muka_pembelian',
                    'kode_uang_muka_pembelian',
                    'tanggal',
                    'pp.nama_permintaan_pembelian',
                    DB::raw("concat(mu.kode_mata_uang,' - ',mu.nama_mata_uang) as nama_mata_uang"),
                    'nama_pemasok',
                    'rate',
                    'nominal',
                    'total',
                    'catatan',
                    'void',
                    'konversi_nominal'
                )
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
                    $btn .= '<li><a href="' . route('purchase-down-payment-view', $row->id_uang_muka_pembelian) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    $btn .= '<li><a href="' . route('purchase-down-payment-entry', $row->id_uang_muka_pembelian) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    if ($row->void == '0' && (in_array($idUser, $filterUser) || $idUser == $row->user_created)) {
                        $btn .= '<li><a href="' . route('purchase-down-payment-delete', $row->id_uang_muka_pembelian) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                    }

                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $cabang = session()->get('access_cabang');
        return view('ops.purchaseDownPayment.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Uang Muka Pembelian | List",
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('uang_muka_pembelian', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

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

        $cabang = session()->get('access_cabang');
        // $slip = DB::table('master_slip')->select('id_slip as id', DB::raw("CONCAT(kode_slip,' - ',nama_slip) as text"))
        //     ->get();
        return view('ops.purchaseDownPayment.form', [
            'data' => $data,
            'cabang' => $cabang,
            'maxPayment' => $remainingPayment,
            "pageTitle" => "SCA OPS | Uang Muka Pembelian | " . ($id == 0 ? 'Create' : 'Edit'),
            // "slip" => $slip,
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = PurchaseDownPayment::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new PurchaseDownPayment;
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

            //save saldo transaksi
            $resultSaldoTransaksi = (new ApiController)->transactionBalance(new Request([
                'tipe_transaksi' => 'Uang Muka Pembelian',
                'id_transaksi' => $data->kode_uang_muka_pembelian,
                'tanggal' => $data->tanggal,
                'ref_id' => $data->purchaseOrder->nama_permintaan_pembelian,
                'catatan' => $data->catatan,
                'id_pelanggan' => null,
                'id_pemasok' => $data->purchaseOrder->id_pemasok,
                'dpp' => $data->konversi_nominal,
                'ppn' => 0,
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

            // $resultJurnalUangMukaPembelian = (new ApiController)->journalUangMukaPembelian(new Request([
            //     "no_transaksi" => $data->kode_uang_muka_pembelian,
            //     "tanggal" => $data->tanggal,
            //     "slip" => null,
            //     "cabang" => $data->id_cabang,
            //     "pemasok" => $data->purchaseOrder->id_pemasok,
            //     "void" => $data->void,
            //     "user" => session()->get('user')['id_pengguna'],
            //     "total" => $data->konversi_nominal,
            //     "uang_muka" => $data->konversi_nominal,
            //     "ppn" => 0,
            // ]));

            // if ($resultJurnalUangMukaPembelian->getData()->result == false) {
            //     DB::rollback();
            //     Log::error($resultJurnalUangMukaPembelian->getData()->message);
            //     Log::error($resultJurnalUangMukaPembelian);
            //     return response()->json([
            //         "result" => false,
            //         "message" => $resultJurnalUangMukaPembelian->getData()->message,
            //     ], 500);
            // }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('purchase-down-payment'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save purchase down payment");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function viewData($id)
    {
        if (checkAccessMenu('uang_muka_pembelian', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = PurchaseDownPayment::find($id);
        return view('ops.purchaseDownPayment.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Uang Muka Pembelian | Detail",
        ]);
    }

    public function destroy($id)
    {
        if (checkAccessMenu('uang_muka_pembelian', 'delete') == false) {
            return response()->json(['message' => 'Tidak mempunyai akses'], 500);
        }

        $data = PurchaseDownPayment::find($id);
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

            $payment = TransactionBalance::where('id_transaksi', $data->kode_uang_muka_pembelian)->where('tipe_transaksi', 'Uang Muka Pembelian')->first();
            if ($payment && $payment->bayar > 0) {
                return response()->json([
                    "result" => false,
                    "message" => "Uang muka sudah terbayar",
                ], 500);
            }

            $payment->delete();

            $resultJurnalUangMukaPembelian = (new ApiController)->journalUangMukaPembelian(new Request([
                "no_transaksi" => $data->kode_uang_muka_pembelian,
                "tanggal" => $data->tanggal,
                "slip" => null,
                "cabang" => $data->id_cabang,
                "pemasok" => $data->purchaseOrder->id_pemasok,
                "void" => $data->void,
                "user" => session()->get('user')['id_pengguna'],
                "total" => $data->konversi_nominal,
                "uang_muka" => $data->konversi_nominal,
                "ppn" => 0,
            ]));

            if ($resultJurnalUangMukaPembelian->getData()->result == false) {
                DB::rollback();
                Log::error($resultJurnalUangMukaPembelian->getData()->message);
                Log::error($resultJurnalUangMukaPembelian);
                return response()->json([
                    "result" => false,
                    "message" => $resultJurnalUangMukaPembelian->getData()->message,
                ], 500);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dibatalkan",
                "redirect" => route('purchase-down-payment'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void purchase down payment");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal diproses",
            ], 500);
        }
    }

    public function autoPo(Request $request)
    {
        $idCabang = $request->id_cabang;
        $duration = DB::table('setting')->where('code', 'UMB Duration')->first();
        $valDuration = '1';
        if ($duration) {
            $valDuration = $duration->value2;
        }

        $startDate = date('Y-m-d', strtotime('-' . intval($valDuration) . ' days'));
        $endDate = date('Y-m-d');
        $datas = DB::table('permintaan_pembelian as pp')
            ->select(
                'pp.id_permintaan_pembelian as id',
                DB::raw('concat(nama_permintaan_pembelian," ( ",nama_pemasok," )") as text'),
                'mtotal_permintaan_pembelian'
            )
            ->leftJoin('uang_muka_pembelian as ump', function ($join) {
                $join->on('pp.id_permintaan_pembelian', '=', 'ump.id_permintaan_pembelian')
                    ->where('ump.void', 0);
            })
            ->leftJoin('pemasok as p', 'pp.id_pemasok', 'p.id_pemasok')
            ->whereBetween('tanggal_permintaan_pembelian', [$startDate, $endDate])
            ->where('pp.id_cabang', $idCabang)
            ->groupBy('pp.id_permintaan_pembelian')
            ->having(DB::raw('mtotal_permintaan_pembelian - COALESCE(sum(nominal),0)'), '<>', '0')
            ->orderBy('tanggal_permintaan_pembelian', 'desc')->get();
        return response()->json([
            'result' => true,
            'data' => $datas,
        ], 200);
    }

    public function countPo(Request $request)
    {
        $po_id = $request->po_id;
        $id = $request->id;
        $countDataPo = DB::table('permintaan_pembelian as pp')
            ->select('pp.mtotal_permintaan_pembelian', 'nilai_mata_uang', 'pp.id_mata_uang', 'mu.nama_mata_uang')
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
            'nama_mata_uang' => $countDataPo->nama_mata_uang,
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
