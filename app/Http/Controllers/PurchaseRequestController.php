<?php

namespace App\Http\Controllers;

use App\PurchaseRequest;
use DB;
use Illuminate\Http\Request;
use Log;
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
                    'dt_created as created_at',
                    'prh.void'
                )
                ->leftJoin('gudang', 'prh.id_gudang', '=', 'gudang.id_gudang')
                ->leftJoin('pengguna as user', 'prh.purchase_request_user_id', '=', 'user.id_pengguna')
                ->leftJoin('pengguna as approval', 'prh.approval_user_id', '=', 'approval.id_pengguna');

            if (isset($request->c)) {
                $data = $data->where('prh.id_cabang', $request->c);
            }

            if ($request->show_void == 'false') {
                $data = $data->where('prh.void', '0');
            }

            $data = $data->orderBy('prh.dt_created', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    if ($row->void == '1') {
                        $btn = '<label class="label label-default">Batal</label>';
                    } else {
                        $btn = '<ul class="horizontal-list">';
                        $btn .= '<li><a href="' . route('purchase-request-view', $row->purchase_request_id) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        if ($row->approval_status == 0) {
                            $btn .= '<li><a href="' . route('purchase-request-change-status', [$row->purchase_request_id, 'approval']) . '" class="btn btn-success btn-xs mr-1 mb-1 btn-change-status" data-param="menyetujui"><i class="glyphicon glyphicon-check"></i> Approval</a></li>';
                            $btn .= '<li><a href="' . route('purchase-request-change-status', [$row->purchase_request_id, 'reject']) . '" class="btn btn-default btn-xs mr-1 mb-1 btn-change-status" data-param="menolak"><i class="fa fa-times"></i> Reject</a></li>';
                            $btn .= '<li><a href="' . route('purchase-request-entry', $row->purchase_request_id) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                            $btn .= '<li><a href="' . route('purchase-request-delete', $row->purchase_request_id) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                        }

                        $btn .= '</ul>';
                    }

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
        $data = PurchaseRequest::find($id);
        try {
            DB::beginTransaction();
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

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('purchase-request'),
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
        $data = PurchaseRequest::find($id);

        return view('ops.purchaseRequest.detail', [
            'data' => $data,
            'status' => $this->arrayStatus,
            "pageTitle" => "SCA OPS | Permintaan Pembelian | Detail",
        ]);
    }

    public function destroy($id)
    {
        $data = PurchaseRequest::find($id);
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
                "redirect" => route('purchase-request'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void purchase request");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal dibatalkan",
            ]);
        }
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
        $search = $request->search;
        $datas = DB::table('barang')->select('id_barang as id', 'nama_barang as text', 'kode_barang')
            ->where('status_barang', 1)
            ->where('nama_barang', 'like', '%' . $search . '%')->limit(10)->get();

        return $datas;
    }

    public function autoSatuan(Request $request)
    {
        $item = $request->item;
        $ids = DB::table('isi_satuan_barang')->where('id_barang', $item)->pluck('id_satuan_barang');
        $datas = DB::table('satuan_barang')->select('id_satuan_barang as id', 'nama_satuan_barang as text')
            ->where('status_satuan_barang', 1)->whereIn('id_satuan_barang', $ids)->get();
        return $datas;
    }

    public function changeStatus($id, $type = 'approval')
    {
        $data = PurchaseRequest::find($id);
        if (!$data) {
            return response()->json([
                "result" => false,
                "message" => "Data tidak ditemukan",
            ]);
        }

        if (!in_array($type, ['approval', 'reject']) || $data->approval_status != 0) {
            return response()->json([
                "result" => false,
                "message" => "Data gagal diperbarui",
            ]);
        }
        try {
            DB::beginTransaction();
            $data->approval_status = $type == 'approval' ? '1' : '2';
            $data->approval_user_id = session()->get('user')['id_pengguna'];
            $data->approval_date = date('Y-m-d H:i:s');
            $data->save();

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil diperbarui",
                "redirect" => route('purchase-request'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when change status " . $type . " purchase request");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal diperbarui",
            ]);
        }
    }
}
