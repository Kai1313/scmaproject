<?php

namespace App\Http\Controllers;

use App\PurchaseRequest;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PurchaseRequestController extends Controller
{
    public $arrayStatus = [
        ['text' => 'Pending', 'class' => 'label label-default'],
        ['text' => 'Approve', 'class' => 'label label-success'],
        ['text' => 'Reject', 'class' => 'label label-danger'],
    ];

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('purchase_request_header as prh')
                ->select(
                    'purchase_request_id',
                    'purchase_request_code',
                    'purchase_request_date',
                    'purchase_request_estimation_date',
                    'nama_gudang',
                    'user.nama_pengguna as user',
                    'catatan',
                    'approval_status',
                    'approval.nama_pengguna as approval_user',
                    'approval_date',
                    'dt_created as created_at'
                )
                ->leftJoin('gudang', 'prh.id_gudang', '=', 'gudang.id_gudang')
                ->leftJoin('pengguna as user', 'prh.purchase_request_user_id', '=', 'user.id_pengguna')
                ->leftJoin('pengguna as approval', 'prh.approval_user_id', '=', 'approval.id_pengguna')
                ->where('prh.void', '=', 0)->where('void', 0);

            if (isset($request->c)) {
                $data = $data->where('prh.id_cabang', $request->c);
            }

            $data = $data->orderBy('prh.dt_created', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="horizontal-list">';
                    if ($row->approval_status == 0) {
                        $btn .= '<li><a href="' . route('purchase-request-change-status', [$row->purchase_request_id, 'approval']) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-check"></i> Approval</a></li>';
                        $btn .= '<li><a href="' . route('purchase-request-change-status', [$row->purchase_request_id, 'reject']) . '" class="btn btn-default btn-xs mr-1 mb-1"><i class="fa fa-times"></i> Reject</a></li>';
                    }

                    $btn .= '<li><a href="' . route('purchase-request-entry', $row->purchase_request_id) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    if ($row->approval_status == 0) {
                        $btn .= '<li><a href="' . route('purchase-request-delete', $row->purchase_request_id) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Hapus</a></li>';
                    }

                    $btn .= '</ul>';
                    return $btn;
                })
                ->editColumn('approval_status', function ($row) {
                    return '<label class="' . $this->arrayStatus[$row->approval_status]['class'] . '">' . $this->arrayStatus[$row->approval_status]['text'] . '</label>';
                })
                ->rawColumns(['action', 'approval_status'])
                ->make(true);

        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.purchaseRequest.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Permintaan Pembelian | List",
        ]);
    }

    public function entry($id = 0)
    {
        $data = PurchaseRequest::find($id);
        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.purchaseRequest.form', [
            'data' => $data,
            'cabang' => $cabang,
            'arrayStatus' => $this->arrayStatus,
            "pageTitle" => "SCA OPS | Permintaan Pembelian | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $paramValidate = [
            'id_cabang' => 'required',
            'purchase_request_date' => 'required',
            'purchase_request_estimation_date' => 'required',
            'purchase_request_user_id' => 'required',
            'id_gudang' => 'required',
        ];

        $messages = [
            'id_cabang.required' => 'Cabang harus diisi',
            'purchase_request_date.required' => 'Tanggal harus diisi',
            'purchase_request_estimation_date.required' => 'Tanggal estimasi harus diisi',
            'id_gudang.required' => 'Gudang harus diisi',
            'purchase_request_user_id' => 'Pemohon harus diisi',
        ];

        $valid = Validator::make($request->all(), $paramValidate, $messages);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid)->withInput($request->all());
        }

        $data = PurchaseRequest::find($id);
        if (!$data) {
            $data = new PurchaseRequest;
        }

        $data->fill($request->all());
        if ($id == 0) {
            $data->purchase_request_code = PurchaseRequest::createcode($request->id_cabang);
            $data->approval_status = 0;
            $data->user_created = session()->get('user')['id_pengguna'];
            $data->void = 0;
        } else {
            $data->user_modified = session()->get('user')['id_pengguna'];
        }

        $data->save();
        $data->savedetails($request->details);

        return redirect()
            ->route('purchase-request-entry', $data->purchase_request_id)
            ->with('success', 'Data berhasil tersimpan');
    }

    public function destroy(Request $request, $id)
    {
        $data = PurchaseRequest::find($id);
        if (!$data) {
            return 'Data tidak ditemukan';
        }

        $data->void = 1;
        $data->void_user_id = session()->get('user')['id_pengguna'];
        $data->save();

        return redirect()
            ->route('purchase-reques')
            ->with('success', 'Data berhasil dibatalkan');

    }

    public function autoWerehouse(Request $request)
    {
        $search = $request->search;
        $idCabang = $request->id_cabang;
        $datas = DB::table('gudang')->select('id_gudang as id', 'nama_gudang as text')
            ->where('id_cabang', $idCabang)
            ->where('status_gudang', 1)
            ->where('nama_gudang', 'like', '%' . $search . '%')->get();
        return $datas;
    }

    public function autoUser(Request $request)
    {
        $search = $request->search;
        $datas = DB::table('pengguna')->select('id_pengguna as id', 'nama_pengguna as text')
            ->where('status_pengguna', 1)
            ->where('nama_pengguna', 'like', '%' . $search . '%')->get();
        return $datas;
    }

    public function autoItem(Request $request)
    {
        $serach = $request->serach;
        $datas = DB::table('barang')->select('id_barang as id', 'nama_barang as text', 'kode_barang')
            ->where('status_barang', 1)
            ->where('nama_barang', 'like', '%' . $serach . '%')->limit(10)->get();
        return $datas;
    }

    public function autoSatuan(Request $request)
    {
        $serach = $request->serach;
        $datas = DB::table('satuan_barang')->select('id_satuan_barang as id', 'nama_satuan_barang as text')
            ->where('status_satuan_barang', 1)
            ->where('nama_satuan_barang', 'like', '%' . $serach . '%')->get();
        return $datas;
    }

    public function changeStatus($id, $type = 'approval')
    {
        $data = PurchaseRequest::find($id);
        if (!$data) {
            return 'data tidak ditemukan';
        }

        if (!in_array($type, ['approval', 'reject']) || $data->approval_status != 0) {
            return 'Akses ditolak';
        }

        $data->approval_status = $type == 'approval' ? '1' : '2';
        $data->approval_user_id = session()->get('user')['id_pengguna'];
        $data->approval_date = date('Y-m-d H:i:s');

        $data->save();
        return redirect()
            ->route('purchase-request', $data->purchase_request_id)
            ->with('success', 'Data berhasil diperbarui');
    }
}
