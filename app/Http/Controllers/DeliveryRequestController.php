<?php
namespace App\Http\Controllers;

use App\Barang;
use App\Models\Transaction\DeliveryRequest;
use App\Models\Transaction\DeliveryRequestDetail;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\Facades\DataTables;

class DeliveryRequestController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'delivery_request', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DeliveryRequest::select(
                'delivery_requests.*',
                'branch.nama_cabang as branch_nama_cabang',
                'dest_cabang.nama_cabang as dest_nama_cabang'
            )
                ->join('cabang as branch', 'branch_id', 'branch.id_cabang')
                ->join('cabang as dest_cabang', 'destination_branch_id', 'dest_cabang.id_cabang')
                ->where('delivery_request_type', '1')->where('status', '!=', '0');

            if (isset($request->branch_id)) {
                $data = $data->where('branch_id', $request->branch_id);
            }

            $data = $data->orderBy('date', 'desc')->orderBy('delivery_request_code', 'desc');

            $statusOptions         = DeliveryRequest::statusOption();
            $approvalStatusOptions = DeliveryRequest::approvalStatusOption();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn .= '<a href="' . route('delivery_request-view', $row->id) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a>';
                    if ($row->approval_status == '1' && $row->created_by == session()->get('user')['id_pengguna']) {
                        $btn .= '<a href="' . route('delivery_request-entry', $row->id) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a>';
                        $btn .= '<a href="' . route('delivery_request-delete', $row->id) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a>';
                    }

                    return $btn;
                })
                ->editColumn('status', function ($row) use ($statusOptions) {
                    return $statusOptions[$row->status]['label'] ?? '';
                })
                ->editColumn('approval_status', function ($row) use ($approvalStatusOptions) {
                    return $approvalStatusOptions[$row->approval_status]['label'] ?? '';
                })
                ->editColumn('date', function ($row) {
                    return date('d/m/Y', strtotime($row->date));
                })
                ->rawColumns(['action', 'status', 'approval_status'])
                ->make(true);
        }

        return view('ops.deliveryRequest.index', [
            "pageTitle" => "SCA OPS | Permintaan Pengiriman | List",
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('delivery_request', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data          = DeliveryRequest::find($id);
        $branches      = DB::table('cabang')->select('id_cabang as id', 'nama_cabang as text')->where('status_cabang', '1')->orderBy('nama_cabang', 'asc')->get();
        $statusOptions = DeliveryRequest::statusOption();

        return view('ops.deliveryRequest.form', [
            'data'          => $data,
            'branches'      => $branches,
            'statusOptions' => $statusOptions,
            "pageTitle"     => "SCA OPS | Permintaan Pengiriman | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = DeliveryRequest::find($id);
        try {
            DB::beginTransaction();
            if (! $data) {
                $data = new DeliveryRequest;
            }

            $data->fill($request->all());
            if ($id == 0) {
                $data->delivery_request_code = DeliveryRequest::createcode();
                $data->created_by            = session()->get('user')['id_pengguna'];
                $data->approval_status       = '1'; // set ke pending approval
            }

            $data->save();

            $resRmDetails = $data->removedetails($request->rm_details);
            if (! $resRmDetails['result']) {
                DB::rollback();
                return response()->json(["result" => false, "message" => $resRmDetails['message']], 500);
            }

            $resSaveDetails = $data->savedetails($request->details);
            if (! $resSaveDetails['result']) {
                DB::rollback();
                return response()->json(["result" => false, "message" => $resSaveDetails['message']], 500);
            }

            DB::commit();
            return response()->json([
                "result"   => true,
                "message"  => "Data berhasil disimpan",
                "redirect" => route('delivery_request-entry', $data->id),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);
            return response()->json(["result" => false, "message" => $e->getMessage()], 500);
        }
    }

    public function autoItem(Request $request)
    {
        try {
            $search = $request->get('search');
            $data   = Barang::select('id_barang as id', 'nama_barang as text')
                ->where('status_barang', '1')
                ->where(function ($query) use ($search) {
                    $query->where('nama_barang', 'like', '%' . $search . '%')
                        ->orWhere('kode_barang', 'like', '%' . $search . '%');
                })
                ->limit(20)
                ->get();

            return response()->json($data, 200);
        } catch (\Exception $e) {
            Log::error("Error when auto item delivery request");
            Log::error($e);
            return response()->json([
                "result"  => false,
                "message" => $e->getMessage(),
            ], 500);
        }
    }

    public function getItemUnits(Request $request)
    {
        try {
            $id_barang = $request->get('id_barang');
            $barang    = Barang::find($id_barang);
            $isiSatuan = [];
            if ($barang) {
                $isiSatuan = $barang->units();
            }

            return response()->json($isiSatuan, 200);
        } catch (\Exception $e) {
            Log::error("Error when get item units delivery request");
            Log::error($e);
            return response()->json([
                "result"  => false,
                "message" => $e->getMessage(),
            ], 500);
        }
    }

    public function viewData($id)
    {
        if (checkAccessMenu('delivery_request', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data                  = DeliveryRequest::find($id);
        $statusOptions         = DeliveryRequest::statusOption();
        $approvalStatusOptions = DeliveryRequest::approvalStatusOption();

        return view('ops.deliveryRequest.detail', [
            'data'                  => $data,
            "pageTitle"             => "SCA OPS | Permintaan Pengiriman | Detail",
            'statusOptions'         => $statusOptions,
            'approvalStatusOptions' => $approvalStatusOptions,
        ]);
    }

    public function approvalDelivery(Request $request, $id)
    {
        try {
            $approvalStatus = $request->get('approval_status');
            $detailId       = $request->get('detail_id');
            // cek data permintaan pengiriman berdasarkan id
            $deliveryRequest = DeliveryRequest::find($id);
            if (! $deliveryRequest) {
                return response()->json(["result" => false, "message" => "Permintaan pengiriman tidak ditemukan"], 404);
            }

            DB::beginTransaction();
            if ($detailId) {
                // ketika detail id ada, maka hanya update detail tersebut
                $detail = DeliveryRequestDetail::find($detailId);
                if (! $detail) {
                    DB::rollback();
                    return response()->json(["result" => false, "message" => "Detail tidak ditemukan"], 404);
                }

                $detail->approval_status = $approvalStatus;
                $detail->save();
            } else {
                // jika tidak ada, update semua detail yang berstatus pending
                $detail = DeliveryRequestDetail::where('delivery_request_id', $id)->where('approval_status', '1')->update([
                    'approval_status' => $approvalStatus,
                ]);
            }
            // ambil total detail dan total detail yang sudah di approve atau reject
            $totalDetails         = DeliveryRequestDetail::where('delivery_request_id', $id)->count();
            $approvedDetails      = DeliveryRequestDetail::where('delivery_request_id', $id)->where('approval_status', '!=', '1')->get();
            $approvedDetailsCount = 0;
            $rejectedDetailsCount = 0;
            foreach ($approvedDetails as $approvedDetail) {
                if ($approvedDetail->approval_status == '2') {
                    $approvedDetailsCount += 1;
                } else {
                    $rejectedDetailsCount += 1;
                }
            }
            // jika semua detail sudah di approve atau reject, maka update status permintaan pengiriman
            if ($totalDetails == ($approvedDetailsCount + $rejectedDetailsCount)) {
                // jika total approval 1 atau lebih dari 1. maka status disetujui, jika tidak maka ditolak
                $deliveryRequest->approval_status = $approvedDetailsCount > 0 ? '2' : '0';
                $deliveryRequest->approved_by     = session()->get('user')['id_pengguna'];
                $deliveryRequest->approved_at     = date('Y-m-d H:i:s');
                $deliveryRequest->save();
            }

            DB::commit();
            return response()->json([
                "result"   => true,
                "message"  => "Data berhasil disetujui",
                "redirect" => route('delivery_request-view', $deliveryRequest->id),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);
            return response()->json(["result" => false, "message" => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $data = DeliveryRequest::find($id);
            if (! $data) {
                return response()->json(["result" => false, "message" => "Data tidak ditemukan"], 404);
            }

            if ($data->approval_status != '1') {
                return response()->json(["result" => false, "message" => "Hanya permintaan pengiriman dengan status pending approval yang dapat di void"], 400);
            }

            $data->status = '0'; // set ke void
            $data->save();

            return response()->json([
                "result"   => true,
                "message"  => "Data berhasil divoid",
                "redirect" => route('delivery_request'),
            ], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(["result" => false, "message" => $e->getMessage()], 500);
        }
    }
}
