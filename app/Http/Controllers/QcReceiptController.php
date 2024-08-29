<?php

namespace App\Http\Controllers;

use App\Purchase;
use App\QualityControl;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class QcReceiptController extends Controller
{
    public $arrayStatus = [
        ['text' => 'Belum di qc', 'class' => 'label label-default', 'id' => ''],
        ['text' => 'Passed', 'class' => 'label label-success', 'id' => '1'],
        ['text' => 'Reject', 'class' => 'label label-danger', 'id' => '2'],
        ['text' => 'Hold', 'class' => 'label label-warning', 'id' => '3'],
    ];

    public function index(Request $request)
    {
        if (checkUserSession($request, 'qc_penerimaan_barang', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('pembelian_detail')
                ->select(
                    'id_pembelian_detail',
                    'tanggal_qc',
                    'nama_pembelian',
                    'nama_barang',
                    DB::raw('sum(pembelian_detail.nett) as jumlah_pembelian_detail'),
                    'status_qc',
                    'nama_satuan_barang',
                    'reason',
                    'approval_reason',
                    'qc.sg_pembelian_detail',
                    'qc.be_pembelian_detail',
                    'qc.ph_pembelian_detail',
                    'qc.warna_pembelian_detail',
                    'qc.keterangan_pembelian_detail',
                    'qc.bentuk_pembelian_detail',
                    'qc.id as id_qc',
                    'pengguna.nama_pengguna',
                    'path'
                )
                ->leftJoin('qc', function ($qc) {
                    $qc->on('pembelian_detail.id_pembelian', '=', 'qc.id_pembelian')->on('pembelian_detail.id_barang', '=', 'qc.id_barang');
                })
                ->leftJoin('pembelian', 'pembelian_detail.id_pembelian', '=', 'pembelian.id_pembelian')
                ->leftJoin('barang', 'pembelian_detail.id_barang', '=', 'barang.id_barang')
                ->leftJoin('satuan_barang', 'pembelian_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
                ->leftJoin('pengguna', 'approval_user_id', '=', 'pengguna.id_pengguna')
                ->whereBetween('pembelian.tanggal_pembelian', [$request->start_date, $request->end_date])
                ->where('barang.status_stok_barang', '1')->where('barang.needqc', '1');
            if (isset($request->c)) {
                $data = $data->where('pembelian.id_cabang', $request->c);
            }

            if ($request->id_barang) {
                $data = $data->where('pembelian_detail.id_barang', $request->id_barang);
            }

            $data = $data->groupBy('pembelian_detail.id_pembelian', 'pembelian_detail.id_barang')
                ->orderBy('pembelian.tanggal_pembelian', 'desc');

            $qcApproval = DB::table('setting')->where('code', 'QC Approval')->value('value1');
            $encode = explode(',', $qcApproval);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('status_qc', function ($row) {
                    $index = $row->status_qc;
                    if ($row->status_qc) {
                        return '<label class="' . $this->arrayStatus[$index]['class'] . '">' . $this->arrayStatus[$index]['text'] . '</label>';
                    } else {
                        return '<label class="label label-default">Belum di QC</label>';
                    }
                })
                ->editColumn('path', function ($row) use ($request) {
                    if ($request->show_img == "true") {
                        return '<img src="' . asset('asset/' . $row->path) . '" width="100">';
                    } else {
                        return '<span style="color:#a9a9a9;">Gambar tidak ditampilkan</span>';
                    }
                })
                ->addColumn('action', function ($row) use ($encode) {
                    $btn = '<ul class="horizontal-list" style="min-width:0px;">';
                    if ($row->status_qc == 2 && in_array(session()->get('user')['id_grup_pengguna'], $encode)) {
                        $btn .= '<li><a href="javascript:void(0)" class="btn btn-warning btn-xs mr-1 mb-1 btn-revision" data-id="' . $row->id_qc . '"><i class="glyphicon glyphicon-pencil"></i> Revisi Ke Passed</a></li>';
                    }

                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['status_qc', 'action', 'path'])
                ->make(true);
        }

        $cabang = session()->get('access_cabang');
        $duration = DB::table('setting')->where('code', 'QC Duration')->first();
        $startDate = date('Y-m-d', strtotime('-' . intval($duration->value2) . ' days'));
        $endDate = date('Y-m-d');
        return view('ops.qualityControl.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | QC Permintaan Pembelian | List",
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('qc_penerimaan_barang', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = QualityControl::find($id);
        $cabang = session()->get('access_cabang');
        return view('ops.qualityControl.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | QC Penerimaan Pembelian | " . ($id == 0 ? 'Create' : 'Edit'),
            'arrayStatus' => $this->arrayStatus,
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        DB::beginTransaction();
        try {
            $datas = json_decode($request->details);
            foreach ($datas as $value) {
                if ($value->id == '') {
                    $data = QualityControl::where('id_pembelian', $request->id_pembelian)->where('id_barang', $value->id_barang)->first();
                    if (!$data) {
                        $data = new QualityControl;
                        $data->tanggal_qc = $value->tanggal_qc;
                        $data->id_cabang = $request->id_cabang;
                        $data->id_pembelian = $request->id_pembelian;
                        $data->id_barang = $value->id_barang;
                        $data->id_satuan_barang = $value->id_satuan_barang;
                        $data->jumlah_pembelian_detail = $value->jumlah_pembelian_detail;
                        $data->status_qc = $value->status_qc;
                        $data->reason = $value->reason;
                        $data->sg_pembelian_detail = $value->sg_pembelian_detail;
                        $data->bentuk_pembelian_detail = $value->checkbox_bentuk == 1 ? $value->bentuk_pembelian_detail : '';
                        $data->be_pembelian_detail = $value->be_pembelian_detail;
                        $data->ph_pembelian_detail = $value->ph_pembelian_detail;
                        $data->warna_pembelian_detail = $value->checkbox_warna == 1 ? $value->warna_pembelian_detail : '';
                        $data->keterangan_pembelian_detail = $value->keterangan_pembelian_detail;
                        $data->user_id = session()->get('user')['id_pengguna'];
                        $data->save();

                        $data->uploadfile($value, $data);

                        $data->updatePembelianDetail();
                    }
                }
            }

            DB::commit();
            $this->callApiPembelianNative($request->id_pembelian);
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('qc_receipt'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save qc receipt");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function autoPurchasing(Request $request)
    {
        $cabang = $request->cabang_id;
        $search = $request->search;
        $datas = DB::table('pembelian')->select('nama_pembelian as text', 'id_pembelian as id', 'nama_pemasok', 'tanggal_pembelian', 'nomor_po_pembelian')
            ->leftJoin('pemasok', 'pembelian.id_pemasok', '=', 'pemasok.id_pemasok')
            ->where('id_cabang', $cabang);
        if ($search) {
            $datas = $datas->where('nama_pembelian', 'like', '%' . $search . '%');
        }

        $datas = $datas->orderBy('tanggal_pembelian', 'desc')->limit(20)->get();

        return response()->json([
            'result' => true,
            'data' => $datas,
        ], 200);
    }

    public function autoItem(Request $request)
    {
        $idPembelian = $request->number;
        $parent = Purchase::find($idPembelian);

        $specialGroup = DB::table('setting')->where('code', 'QC Special Group')->value('value1');
        $explodeSpecialGroup = explode(',', $specialGroup);

        $listItem = $parent->detailgroup;
        if (in_array(session()->get('user')['id_grup_pengguna'], $explodeSpecialGroup)) {
            $specialCategory = DB::table('setting')->where('code', 'QC Special Category Item')->value('value1');
            $explodeSpecialCategory = explode(',', $specialCategory);

            $listItem = $parent->detailgroup->whereIn('id_kategori_barang', $explodeSpecialCategory);
        }

        return response()->json([
            'result' => true,
            'list_item' => $listItem,
            'qc' => $parent->qc,
            'route_print' => route('qc_receipt-print-data', $idPembelian),
        ], 200);
    }

    public function callApiPembelianNative($data)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, env('OLD_API_ROOT') . "actions/aa_update_ppn_pembelian.php?id_pembelian=" . $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);

            return $output;
        } catch (\Exception $th) {
            Log::error("Error when access api pembelian");
            Log::error($th);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function printData($id)
    {
        if (checkAccessMenu('qc_penerimaan_barang', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = Purchase::find($id);
        return view('ops.qualityControl.print', [
            'data' => $data,
            'arrayStatus' => $this->arrayStatus,
            "pageTitle" => "SCA OPS | QC Penerimaan Pembelian | Cetak",
        ]);
    }

    public function findDataQc(Request $request)
    {
        $id = isset($request->id) ? $request->id : 0;
        $data = QualityControl::find($id);
        if ($data) {
            return response()->json([
                'status' => 'success',
                'kodePenerimaan' => $data->purchase->nama_pembelian,
                'namaBarang' => $data->barang->nama_barang,
                'urlToChangeStatus' => route('qc_receipt-save-change-status', $id),
                'jumlah' => formatNumber($data->jumlah_pembelian_detail) . ' ' . $data->satuan->nama_satuan_barang,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Data tidak ditemukan',
        ], 500);
    }

    public function saveChangeStatus(Request $request, $id)
    {
        DB::beginTransaction();
        $data = QualityControl::find($id);
        try {
            $data->approval_user_id = session()->get('user')['id_pengguna'];
            $data->approval_date = date('Y-m-d');
            $data->approval_reason = $request->approval_reason;
            $data->status_qc = 1;
            $data->save();
            $data->updatePembelianDetail();
            DB::commit();

            $this->callApiPembelianNative($data->id_pembelian);
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('qc_receipt'),
            ], 200);
        } catch (\Exception $th) {
            DB::rollback();
            Log::error("Error when change save qc receipt");
            Log::error($th);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function getItem(Request $request)
    {
        $datas = [];
        if ($request->search) {
            $datas = DB::table('barang')->select('nama_barang as text', 'id_barang as id')
                ->where('status_barang', '1')
                ->where('nama_barang', 'like', '%' . $request->search . '%')->limit(20)->get();
        }

        return $datas;
    }
}
