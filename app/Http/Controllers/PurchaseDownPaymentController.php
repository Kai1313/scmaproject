<?php

namespace App\Http\Controllers;

use App\PurchaseDownPayment;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PurchaseDownPaymentController extends Controller
{
    public function index(Request $request)
    {
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
                        $btn .= '<li><a href="' . route('purchase-down-payment-delete', $row->id_uang_muka_pembelian) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Hapus</a></li>';
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
        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();
        return view('ops.purchaseDownPayment.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Uang Muka Pembelian | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $paramValidate = [
            'id_cabang' => 'required',
            'tanggal' => 'required',
            'id_permintaan_pembelian' => 'required',
            'id_mata_uang' => 'required',
            'id_slip' => 'required',
            'rate' => 'required',
            'nominal' => 'required',
            'total' => 'required',
        ];

        $messages = [
            'id_cabang.required' => 'Cabang harus diisi',
            'tanggal.required' => 'Tanggal harus diisi',
            'id_permintaan_pembelian.required' => 'PO harus diisi',
            'id_mata_uang.required' => 'Mata uang harus diisi',
            'id_slip.required' => 'Slip harus diisi',
            'rate.required' => 'Rate harus diisi',
            'nominal.required' => 'Nomial harus diisi',
            'total.required' => 'Total harus diisi',
        ];

        $valid = Validator::make($request->all(), $paramValidate, $messages);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid)->withInput($request->all());
        }

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

            $data->save();

            DB::commit();
            return redirect()
                ->route('purchase-down-payment-entry', $data->id_uang_muka_pembelian)
                ->with('success', 'Data berhasil tersimpan');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save purchase down payment");
            Log::error($e);
            return redirect()
                ->route('purchase-down-payment-entry', $data ? $data->id_uang_muka_pembelian : 0)
                ->with('error', 'Data gagal tersimpan');
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
            return 'Data tidak ditemukan';
        }

        try {
            DB::beginTransaction();
            $data->void = 1;
            $data->void_user_id = session()->get('user')['id_pengguna'];
            $data->save();

            DB::commit();
            return redirect()
                ->route('purchase-down-payment')
                ->with('success', 'Data berhasil dibatalkan');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void purchase down payment");
            Log::error($e);
            return redirect()
                ->route('purchase-down-payment')
                ->with('error', 'Data gagal tersimpan');
        }
    }

    public function autoPo(Request $request)
    {
        $search = $request->search;
        $idCabang = $request->id_cabang;
        $datas = DB::table('permintaan_pembelian')->select('id_permintaan_pembelian as id', 'nama_permintaan_pembelian as text')
            ->where('id_cabang', $idCabang)
            ->where('nama_permintaan_pembelian', 'like', '%' . $search . '%')
            ->orderBy('date_permintaan_pembelian', 'desc')->limit(10)->get();
        return $datas;
    }

    public function autoSlip(Request $request)
    {
        $search = $request->search;
        $datas = DB::table('master_slip')->select('id_slip as id', DB::raw("CONCAT(kode_slip,' - ',nama_slip) as text"))
            ->where(DB::raw("CONCAT(kode_slip,' - ',nama_slip)"), 'like', '%' . $search . '%')
            ->get();
        return $datas;
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
}
