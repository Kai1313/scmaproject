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
            $data = DB::table('uang_muka_pembelian as ump');

            if (isset($request->c)) {
                $data = $data->where('ump.id_cabang', $request->c);
            }

            $data = $data->orderBy('ump.dt_created', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('purchase-down-payment-entry', $row->uang_muka_pembelian_id) . '" class="btn btn-warning btn-sm">Edit</a>';
                    $btn .= '<a href="' . route('purchase-down-payment-delete', $row->uang_muka_pembelian_id) . '" class="btn btn-danger btn-sm btn-destroy">Delete</a>';
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
            'rate' => 'required',
            'nominal' => 'required',
            'total' => 'required',
        ];

        $messages = [
            'id_cabang.required' => 'Cabang harus diisi',
            'tanggal.required' => 'Tanggal harus diisi',
            'id_permintaan_pembelian.required' => 'PO harus diisi',
            'id_mata_uang.required' => 'Mata uang harus diisi',
            'rate.required' => 'Rate harus diisi',
            'nominal.required' => 'Nomial harus diisi',
            'total.required' => 'Total harus diisi',
        ];

        $valid = Validator::make($request->all(), $paramValidate, $messages);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid)->withInput($request->all());
        }

        $data = PurchaseDownPayment::find($id);
        if (!$data) {
            $data = new PurchaseDownPayment;
        }

        // $data->fill($request->all());
        // if ($id == 0) {
        //     $data->purchase_request_code = PurchaseRequest::createcode($request->id_cabang);
        //     $data->approval_status = 1;
        //     $data->user_created = session()->get('user')['id_pengguna'];
        // } else {
        //     $data->user_modified = session()->get('user')['id_pengguna'];
        // }

        // $data->save();
        // $data->savedetails($request->details);

        return redirect()
            ->route('purchase-down-payment-entry', $data->id_uang_muka_pembelian)
            ->with('success', 'Data berhasil tersimpan');
    }

    public function autoPo(Request $request)
    {
        $search = $request->serach;
        $idCabang = $request->id_cabang;
        $datas = DB::table('permintaan_pembelian')->select('id_permintaan_pembelian as id', 'nama_permintaan_pembelian as text')
            ->where('id_cabang', $idCabang)
            ->orderBy('date_permintaan_pembelian', 'desc')->limit(10)->get();
        return $datas;
    }

    public function autoCurrency(Request $request)
    {
        $search = $request->serach;
        $datas = DB::table('mata_uang')->select('id_mata_uang as id', 'kode_mata_uang as text', 'nilai_mata_uang')
            ->get();
        return $datas;
    }

    public function countPo(Request $request)
    {
        $po_id = $request->po_id;
        $id = $request->id;
        $countDataPo = DB::table('permintaan_pembelian')->where('id_permintaan_pembelian', $po_id)->value('mtotal_permintaan_pembelian');
        $countData = DB::table('uang_muka_pembelian')
            ->where('id_permintaan_pembelian', $po_id)
            ->where('id_uang_muka_pembelian', '!=', $id)
            ->sum('nominal');
        return response()->json([
            'status' => 'success',
            'nominal' => $countDataPo - $countData,
            'total' => $countDataPo,
        ]);
    }
}
