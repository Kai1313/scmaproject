<?php

namespace App\Http\Controllers;

use App\PurchaseRequest;
use DB;
use Illuminate\Http\Request;
use Log;
use PDF;
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
        if (checkUserSession($request, 'purchase_requisitions', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('purchase_request_header as prh')
                ->select(
                    'prh.purchase_request_id',
                    'prh.purchase_request_code',
                    'prh.purchase_request_date',
                    'prh.purchase_request_estimation_date',
                    'nama_gudang',
                    'user.nama_pengguna as user',
                    'catatan',
                    'prh.approval_status',
                    'approval.nama_pengguna as approval_user',
                    'prh.approval_date',
                    'dt_created as created_at',
                    'prh.void',
                    'purchase_request_user_id',
                    DB::raw('concat(sum(prd.closed)," dari ",count(prd.closed)," Barang") as closed')
                )
                ->leftJoin('gudang', 'prh.id_gudang', '=', 'gudang.id_gudang')
                ->leftJoin('pengguna as user', 'prh.purchase_request_user_id', '=', 'user.id_pengguna')
                ->leftJoin('pengguna as approval', 'prh.approval_user_id', '=', 'approval.id_pengguna')
                ->leftJoin('purchase_request_detail as prd', 'prh.purchase_request_id', 'prd.purchase_request_id');

            if (isset($request->c)) {
                $data = $data->where('prh.id_cabang', $request->c);
            }

            if ($request->show_void == 'false') {
                $data = $data->where('prh.void', '0');
            }

            $data = $data->groupBy('prh.purchase_request_id')->orderBy('prh.dt_created', 'desc');
            $access = DB::table('setting')->where('id_cabang', $request->c)->where('code', 'PR Approval')->first();
            $arrayAccess = explode(',', $access->value1);

            $idUser = session()->get('user')['id_pengguna'];
            $filterUser = DB::table('pengguna')
                ->where(function ($w) {
                    $w->where('id_grup_pengguna', session()->get('user')['id_grup_pengguna'])->orWhere('id_grup_pengguna', 1);
                })
                ->where('status_pengguna', '1')->pluck('id_pengguna')->toArray();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($arrayAccess, $filterUser, $idUser) {
                    $btn = '<ul class="horizontal-list">';
                    if ($row->void == '0') {
                        $btn .= '<li><a href="' . route('purchase-request-view', $row->purchase_request_id) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        if ($row->approval_status == 0) {
                            // if (in_array(session()->get('user')['id_grup_pengguna'], $arrayAccess)) {
                            //     $btn .= '<li><a href="' . route('purchase-request-change-status', [$row->purchase_request_id, 'approval']) . '" class="btn btn-success btn-xs mr-1 mb-1 btn-change-status" data-param="menyetujui"><i class="glyphicon glyphicon-check"></i> Approval</a></li>';
                            //     $btn .= '<li><a href="' . route('purchase-request-change-status', [$row->purchase_request_id, 'reject']) . '" class="btn btn-default btn-xs mr-1 mb-1 btn-change-status" data-param="menolak"><i class="fa fa-times"></i> Reject</a></li>';
                            // }

                            if (in_array($idUser, $filterUser) || $idUser == $row->purchase_request_user_id) {
                                $btn .= '<li><a href="' . route('purchase-request-entry', $row->purchase_request_id) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                                $btn .= '<li><a href="' . route('purchase-request-delete', $row->purchase_request_id) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                            }
                        }
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

        $cabang = session()->get('access_cabang');
        return view('ops.purchaseRequest.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Permintaan Pembelian | List",
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('purchase_requisitions', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = PurchaseRequest::find($id);
        $cabang = session()->get('access_cabang');
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
                $data->purchase_request_user_id = session()->get('user')['id_pengguna'];
            } else {
                $data->user_modified = session()->get('user')['id_pengguna'];
            }

            $data->save();
            $data->savedetails($request->details);

            if ($id == 0) {
                $access = DB::table('setting')->where('id_cabang', $request->id_cabang)->where('code', 'PR Send WA')->first();
                if ($access && $access->value1 == '1') {
                    $userSendWa = DB::table('pengguna')
                        ->select('nama_pengguna', 'telepon1_pengguna')
                        ->whereIn('id_grup_pengguna', [7, 13])
                        ->where('status_pengguna', 1)->get();
                    $settingMessage = DB::table('setting')->where('code', 'Pesan Permintaan Beli')->first();
                    $strParam = [
                        '[[pembuat]]' => $data->pengguna->nama_pengguna,
                        '[[code]]' => $data->purchase_request_code,
                        '[[date]]' => date('d/m/Y'),
                    ];
                    foreach ($userSendWa as $user) {
                        $messageText = replaceMessage($strParam, $settingMessage->value1);
                        $this->sendToWa($user->telepon1_pengguna, $messageText);
                    }
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('purchase-request'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save purchase request");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function viewData($id)
    {
        if (checkAccessMenu('purchase_requisitions', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = PurchaseRequest::find($id);
        $access = DB::table('setting')->where('id_cabang', $data->id_cabang)->where('code', 'PR Approval')->value('value1');
        $arrayAccess = explode(',', $access);
        $idUser = session()->get('user')['id_grup_pengguna'];

        return view('ops.purchaseRequest.detail', [
            'data' => $data,
            'status' => $this->arrayStatus,
            'arrayAccess' => $arrayAccess,
            'idUser' => $idUser,
            "pageTitle" => "SCA OPS | Permintaan Pembelian | Detail",
        ]);
    }

    public function destroy($id)
    {
        if (checkAccessMenu('purchase_requisitions', 'delete') == false) {
            return response()->json(['message' => 'Tidak mempunyai akses'], 500);
        }

        $data = PurchaseRequest::find($id);
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

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dibatalkan",
                "redirect" => route('purchase-request'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void purchase request");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal dibatalkan",
            ], 500);
        }
    }

    public function autoWerehouse(Request $request)
    {
        $idCabang = $request->cabang;
        $datas = DB::table('gudang')->select('id_gudang as id', 'nama_gudang as text')
            ->where('id_cabang', $idCabang)
            ->where('status_gudang', 1)
            ->get();
        return response()->json([
            'result' => true,
            'data' => $datas,
        ], 200);
    }

    public function autoItem(Request $request)
    {
        $search = $request->search;
        $datas = DB::table('barang')->select('id_barang as id', 'nama_barang as text', 'kode_barang')
            ->where('status_barang', 1)
            ->where('nama_barang', 'like', '%' . $search . '%')->limit(10)->get();

        return response()->json([
            'result' => true,
            'data' => $datas,
        ], 200);
    }

    public function autoSatuan(Request $request)
    {
        $item = $request->item;
        $cabang = $request->cabang;
        $gudang = $request->gudang;
        $satuan = DB::table('isi_satuan_barang')->select('satuan_barang.id_satuan_barang as id', 'nama_satuan_barang as text')
            ->leftJoin('satuan_barang', 'isi_satuan_barang.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
            ->where('id_barang', $item)
            ->where('status_satuan_barang', 1)
            ->where('satuan_jual_isi_satuan_barang', 1)
            ->where('satuan_wadah_isi_satuan_barang', 0)->get();

        $messageStock = '0';
        $messageSatuanStok = '';
        if ($cabang) {
            $arrayCabang = [
                '1' => [1],
                '2' => [5],
            ];

            $stok = DB::table('master_qr_code')->select(DB::raw('sum(sisa_master_qr_code) as stok'), 'nama_satuan_barang')
                ->join('satuan_barang', 'master_qr_code.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
                ->where('id_barang', $item)->whereIn('id_gudang', $arrayCabang[$cabang])
                ->groupBy('id_barang')->first();
            if ($stok) {
                $messageStock = $stok->stok;
                $messageSatuanStok = $stok->nama_satuan_barang;
            }
        }

        return response()->json([
            'result' => true,
            'satuan' => $satuan,
            'stok' => $messageStock,
            'satuan_stok' => $messageSatuanStok,
        ], 200);
    }

    public function changeStatus($id, $type = 'approval')
    {
        $data = PurchaseRequest::find($id);
        if (!$data) {
            return response()->json([
                "result" => false,
                "message" => "Data tidak ditemukan",
            ], 500);
        }

        if (!in_array($type, ['approval', 'reject']) || $data->approval_status != 0) {
            return response()->json([
                "result" => false,
                "message" => "Data gagal diperbarui",
            ], 500);
        }
        try {
            DB::beginTransaction();
            $data->approval_status = $type == 'approval' ? '1' : '2';
            $data->approval_user_id = session()->get('user')['id_pengguna'];
            $data->approval_date = date('Y-m-d H:i:s');
            $data->save();
            $data->saveStatusDetail();

            $access = DB::table('setting')->where('id_cabang', $data->id_cabang)->where('code', 'PR Send WA')->first();
            if ($access && $access->value1 == '1') {
                $userSendWa = DB::table('pengguna')
                    ->select('nama_pengguna', 'telepon1_pengguna')
                    ->whereIn('id_grup_pengguna', [7])
                    ->where('status_pengguna', 1)->get();
                $settingMessage = DB::table('setting')->where('code', 'Pesan Persetujuan Beli')->first();
                $strParam = [
                    '[[pembuat]]' => $data->pengguna->nama_pengguna,
                    '[[code]]' => $data->purchase_request_code,
                    '[[date]]' => date('d/m/Y'),
                    '[[status]]' => $type == 'approval' ? 'disetujui' : 'ditolak',
                ];
                foreach ($userSendWa as $user) {
                    $messageText = replaceMessage($strParam, $settingMessage->value1);
                    $this->sendToWa($user->telepon1_pengguna, $messageText);
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil diperbarui",
                "redirect" => route('purchase-request'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when change status " . $type . " purchase request");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal diperbarui",
            ], 500);
        }
    }

    public function printData($id)
    {
        if (checkAccessMenu('purchase_requisitions', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = PurchaseRequest::find($id);

        $pdf = PDF::loadView('ops.purchaseRequest.print', ['data' => $data, 'arrayStatus' => $this->arrayStatus]);
        $pdf->setPaper('a5', 'landscape');
        return $pdf->stream('Bukti permintaan pembelian ' . $data->purchase_request_code . '.pdf');
    }

    public function sendToWa($targetNumber, $message)
    {
        $token_pengguna = "fb176fda94ad70ec8cc65456d1d5906a";
        $url = "https://wa.scasda.my.id/actions/aaa_api_kirim_webhook.php";
        $data = array(
            "id_jenis_kirim" => 4,
            "nomor_pengirim_kirim" => '*',
            "nomor_tujuan_kirim" => $targetNumber,
            "token_pengguna" => $token_pengguna,
            "pesan_kirim" => $message,
            "gambar_kirim" => '',
            "file_kirim" => '',
            "base64_string" => '',
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_exec($ch);
        curl_close($ch);

        return ['status' => 'true'];
    }

    public function changeStatusDetail(Request $request)
    {
        $index = $request->index;
        $purchaseRequestId = $request->purchase_request_id;
        $approvalNotes = $request->approval_notes;
        $approvalStatus = $request->approval_status;
        $qty = $request->qty;

        $check = DB::table('purchase_request_detail')->where('purchase_request_id', $purchaseRequestId)
            ->where('index', $index)->first();

        if (!$check) {
            return response()->json([
                'result' => 'error',
                'message' => 'Data tidak ditemukan',
            ], 500);
        }

        if ($check->approval_status != 0) {
            return response()->json([
                'result' => 'error',
                'message' => 'Status data sudah diubah menjadi ' . ($check->approval_status == '1' ? 'disetujui' : 'ditolak'),
            ], 500);
        }

        try {
            DB::beginTransaction();
            DB::table('purchase_request_detail')->where('purchase_request_id', $purchaseRequestId)
                ->where('index', $index)->update([
                'approval_status' => $approvalStatus,
                'approval_user_id' => session()->get('user')['id_pengguna'],
                'approval_date' => date('Y-m-d H:i:s'),
                'qty' => $qty,
                'approval_notes' => $approvalNotes,
            ]);

            $checkParent = $this->checkStatusParent($purchaseRequestId);
            if ($checkParent['result'] == false) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Data gagal diperbarui",
                ], 500);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil diperbarui",
                "redirect" => route('purchase-request-view', $purchaseRequestId),
            ], 200);
        } catch (\Exception $th) {
            DB::rollback();
            Log::error($th);
            return response()->json([
                "result" => false,
                "message" => "Data gagal diperbarui",
            ], 500);
        }
    }

    public function checkStatusParent($id)
    {
        $parent = PurchaseRequest::where('purchase_request_id', $id)->first();
        if (!$parent) {
            return [
                "result" => false,
                "message" => "Data parent tidak ditemukan",
            ];
        }

        $countApproval = 0;
        $countReject = 0;
        $totalRow = count($parent->details);
        foreach ($parent->details as $detail) {
            if ($detail->approval_status == '1') {
                $countApproval++;
            }

            if ($detail->approval_status == '2') {
                $countReject++;
            }
        }

        if (($countApproval + $countReject) == $totalRow) {
            $parent->approval_status = ($countApproval > 0) ? 1 : 2;
            $parent->approval_user_id = session()->get('user')['id_pengguna'];
            $parent->approval_date = date('Y-m-d H:i:s');
            $parent->save();

            $access = DB::table('setting')->where('id_cabang', $parent->id_cabang)->where('code', 'PR Send WA')->first();
            if ($access && $access->value1 == '1') {
                $userSendWa = DB::table('pengguna')
                    ->select('nama_pengguna', 'telepon1_pengguna')
                    ->whereIn('id_grup_pengguna', [7])
                    ->where('status_pengguna', 1)->get();
                $settingMessage = DB::table('setting')->where('code', 'Pesan Persetujuan Beli')->first();
                $strParam = [
                    '[[pembuat]]' => $parent->pengguna->nama_pengguna,
                    '[[code]]' => $parent->purchase_request_code,
                    '[[date]]' => date('d/m/Y'),
                    '[[status]]' => $parent->approval_status == '1' ? 'disetujui' : 'ditolak',
                ];
                foreach ($userSendWa as $user) {
                    $messageText = replaceMessage($strParam, $settingMessage->value1);
                    $this->sendToWa($user->telepon1_pengguna, $messageText);
                }
            }
        }

        return ['result' => true];
    }
}
