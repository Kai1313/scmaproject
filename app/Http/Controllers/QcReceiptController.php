<?php
namespace App\Http\Controllers;

use App\Purchase;
use App\QualityControl;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
                    'pembelian.id_pembelian',
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
                    'qc.trial_pembelian_detail',
                    'qc.id as id_qc',
                    'pengguna.nama_pengguna',
                    'path',
                    'barang.id_barang'
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
            $encode     = explode(',', $qcApproval);

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
                ->editColumn('trial_pembelian_detail', function ($row) {
                    if ($row->trial_pembelian_detail) {
                        return $row->trial_pembelian_detail == '1' ? 'OK' : 'Tidak OK';
                    }

                    return '';
                })
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
                    if ($row->status_qc) {
                        // $btn .= '<li><a href="javascript:void(0)" data-id="' . $row->id_qc . '" class="btn btn-default btn-xs mr-1 mb-1 check-history"><i class="glyphicon glyphicon-time"></i></a></li>';
                    }

                    if ($row->status_qc == 2 && in_array(session()->get('user')['id_grup_pengguna'], $encode)) {
                        $btn .= '<li><a href="' . route('qc_receipt-entry', $row->id_qc) . '" class="btn btn-warning btn-xs mr-1 mb-1">Revisi QC</a></li>';
                    }

                    if (! $row->status_qc && in_array(session()->get('user')['id_grup_pengguna'], $encode)) {
                        $btn .= '<li><a href="' . route('qc_receipt-entry') . '?id_barang=' . $row->id_barang . '&id_pembelian=' . $row->id_pembelian . '" class="btn btn-primary btn-xs mr-1 mb-1">Mulai QC</a></li>';
                    }

                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['status_qc', 'action', 'path'])
                ->make(true);
        }

        $cabang    = session()->get('access_cabang');
        $duration  = DB::table('setting')->where('code', 'QC Duration')->first();
        $startDate = date('Y-m-d', strtotime('-' . intval($duration->value2) . ' days'));
        $endDate   = date('Y-m-d');
        return view('ops.qualityControl.index', [
            'cabang'    => $cabang,
            "pageTitle" => "SCA OPS | QC Permintaan Pembelian | List",
            "startDate" => $startDate,
            "endDate"   => $endDate,
        ]);
    }

    public function entry(Request $request, $id = 0)
    {
        if (checkAccessMenu('qc_penerimaan_barang', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($id == 0) {
            $data = DB::table('pembelian_detail')
                ->select(
                    DB::raw('"" as id'),
                    'pembelian.id_cabang',
                    'nama_pembelian',
                    'pembelian_detail.id_pembelian',
                    'nama_pemasok',
                    'nomor_po_pembelian',
                    'tanggal_pembelian',
                    'pembelian_detail.id_barang',
                    'pembelian_detail.id_satuan_barang',
                    'nama_barang',
                    'nama_satuan_barang',
                    'bentuk_qc_barang',
                    'final_range_be',
                    'final_range_ph',
                    'final_range_sg',
                    'start_range_be',
                    'start_range_ph',
                    'start_range_sg',
                    'warna_qc_barang',
                    'nama_cabang',
                    DB::raw('sum(pembelian_detail.nett) as jumlah_pembelian_detail')
                )
                ->join('pembelian', 'pembelian_detail.id_pembelian', 'pembelian.id_pembelian')
                ->join('barang', 'pembelian_detail.id_barang', 'barang.id_barang')
                ->join('pemasok', 'pembelian.id_pemasok', 'pemasok.id_pemasok')
                ->join('satuan_barang', 'pembelian_detail.id_satuan_barang', 'satuan_barang.id_satuan_barang')
                ->join('cabang', 'pembelian.id_cabang', 'cabang.id_cabang')
                ->where('pembelian_detail.id_pembelian', $request->id_pembelian)
                ->where('pembelian_detail.id_barang', $request->id_barang)
                ->groupBy('id_barang')->first();
        } else {
            $data = DB::table('qc')
                ->select(
                    'qc.id',
                    'qc.id_cabang',
                    'nama_pembelian',
                    'qc.id_pembelian',
                    'nama_pemasok',
                    'nomor_po_pembelian',
                    'tanggal_pembelian',
                    'qc.id_barang',
                    'qc.id_satuan_barang',
                    'nama_barang',
                    'nama_satuan_barang',
                    'bentuk_qc_barang',
                    'final_range_be',
                    'final_range_ph',
                    'final_range_sg',
                    'start_range_be',
                    'start_range_ph',
                    'start_range_sg',
                    'warna_qc_barang',
                    'nama_cabang',
                    'jumlah_pembelian_detail',
                    'status_qc',
                    'tanggal_qc',
                    'reason',
                    'sg_pembelian_detail',
                    'be_pembelian_detail',
                    'ph_pembelian_detail',
                    'warna_pembelian_detail',
                    'bentuk_pembelian_detail',
                    'keterangan_pembelian_detail',
                    'trial_pembelian_detail'
                )
                ->join('pembelian', 'qc.id_pembelian', 'pembelian.id_pembelian')
                ->join('barang', 'qc.id_barang', 'barang.id_barang')
                ->join('pemasok', 'pembelian.id_pemasok', 'pemasok.id_pemasok')
                ->join('satuan_barang', 'qc.id_satuan_barang', 'satuan_barang.id_satuan_barang')
                ->join('cabang', 'qc.id_cabang', 'cabang.id_cabang')
                ->where('id', $id)->first();

        }

        return view('ops.qualityControl.form', [
            'data'        => $data,
            "pageTitle"   => "SCA OPS | QC Penerimaan Pembelian | " . ($id == 0 ? 'Tambah' : 'Ubah'),
            'arrayStatus' => $this->arrayStatus,
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $arrayValid = [
            'id_cabang'               => 'required',
            'id_pembelian'            => 'required',
            'id_barang'               => 'required',
            'id_satuan_barang'        => 'required',
            'jumlah_pembelian_detail' => 'required',
            'status_qc'               => 'required',
            'sg_pembelian_detail'     => 'required',
            'be_pembelian_detail'     => 'required',
            'ph_pembelian_detail'     => 'required',
            'tanggal_qc'              => 'required',
            'trial_pembelian_detail'  => 'required',
        ];

        if ($request->status_qc == '2') {
            $arrayValid['reason'] = 'required';
        }

        $valid = Validator::make($request->all(), $arrayValid);
        if ($valid->fails()) {
            return response()->json(['errors' => $valid->errors()], 500);
        }

        DB::beginTransaction();
        try {
            $data = QualityControl::where('id_pembelian', $request->id_pembelian)->where('id_barang', $request->id_barang)->first();
            if (! $data) {
                $data                              = new QualityControl;
                $data->tanggal_qc                  = $request->tanggal_qc;
                $data->id_cabang                   = $request->id_cabang;
                $data->id_pembelian                = $request->id_pembelian;
                $data->id_barang                   = $request->id_barang;
                $data->id_satuan_barang            = $request->id_satuan_barang;
                $data->jumlah_pembelian_detail     = $request->jumlah_pembelian_detail;
                $data->status_qc                   = $request->status_qc;
                $data->reason                      = $request->reason;
                $data->sg_pembelian_detail         = $request->sg_pembelian_detail;
                $data->bentuk_pembelian_detail     = $request->checkbox_bentuk == 1 ? $request->bentuk_pembelian_detail : '';
                $data->be_pembelian_detail         = $request->be_pembelian_detail;
                $data->ph_pembelian_detail         = $request->ph_pembelian_detail;
                $data->warna_pembelian_detail      = $request->checkbox_warna == 1 ? $request->warna_pembelian_detail : '';
                $data->keterangan_pembelian_detail = $request->keterangan_pembelian_detail;
                $data->trial_pembelian_detail      = $request->trial_pembelian_detail;
                $data->user_id                     = session()->get('user')['id_pengguna'];
                $data->save();

                $data->uploadfile($request, $data);
                $data->updatePembelianDetail();
            } else {
                DB::table('qc_log')->insert([
                    'sg_pembelian_detail'         => $data->sg_pembelian_detail,
                    'bentuk_pembelian_detail'     => $data->bentuk_pembelian_detail,
                    'be_pembelian_detail'         => $data->be_pembelian_detail,
                    'ph_pembelian_detail'         => $data->ph_pembelian_detail,
                    'warna_pembelian_detail'      => $data->warna_pembelian_detail,
                    'keterangan_pembelian_detail' => $data->keterangan_pembelian_detail,
                    'user_id'                     => $data->user_id,
                    'status_qc'                   => $data->status_qc,
                    'reason'                      => $data->reason,
                    'created_at'                  => date('Y-m-d H:i:s'),
                    'qc_id'                       => $data->id,
                    'tanggal_qc'                  => $data->tanggal_qc,
                    'trial_pembelian_detail'      => $data->trial_pembelian_detail,
                    'path'                        => $data->path,
                ]);

                $data->sg_pembelian_detail         = $request->sg_pembelian_detail;
                $data->bentuk_pembelian_detail     = $request->checkbox_bentuk == 1 ? $request->bentuk_pembelian_detail : '';
                $data->be_pembelian_detail         = $request->be_pembelian_detail;
                $data->ph_pembelian_detail         = $request->ph_pembelian_detail;
                $data->warna_pembelian_detail      = $request->checkbox_warna == 1 ? $request->warna_pembelian_detail : '';
                $data->keterangan_pembelian_detail = $request->keterangan_pembelian_detail;
                $data->user_id                     = session()->get('user')['id_pengguna'];
                $data->status_qc                   = $request->status_qc;
                $data->reason                      = $request->reason;
                $data->tanggal_qc                  = $request->tanggal_qc;
                $data->trial_pembelian_detail      = $request->trial_pembelian_detail;
                $data->save();

                $data->uploadfile($request, $data);
                $data->updatePembelianDetail();
            }

            DB::commit();
            $this->callApiPembelianNative($request->id_pembelian);
            return response()->json([
                "result"   => true,
                "message"  => "Data berhasil disimpan",
                "redirect" => route('qc_receipt'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save qc receipt");
            Log::error($e);
            return response()->json([
                "result"  => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function autoPurchasing(Request $request)
    {
        $cabang = $request->cabang_id;
        $search = $request->search;
        $datas  = DB::table('pembelian')->select('nama_pembelian as text', 'id_pembelian as id', 'nama_pemasok', 'tanggal_pembelian', 'nomor_po_pembelian')
            ->leftJoin('pemasok', 'pembelian.id_pemasok', '=', 'pemasok.id_pemasok')
            ->where('id_cabang', $cabang);
        if ($search) {
            $datas = $datas->where('nama_pembelian', 'like', '%' . $search . '%');
        }

        $datas = $datas->orderBy('tanggal_pembelian', 'desc')->limit(20)->get();

        return response()->json([
            'result' => true,
            'data'   => $datas,
        ], 200);
    }

    public function autoItem(Request $request)
    {
        $idPembelian = $request->number;
        $parent      = Purchase::find($idPembelian);

        $specialGroup        = DB::table('setting')->where('code', 'QC Special Group')->value('value1');
        $explodeSpecialGroup = explode(',', $specialGroup);

        $listItem = $parent->detailgroup;
        if (in_array(session()->get('user')['id_grup_pengguna'], $explodeSpecialGroup)) {
            $specialCategory        = DB::table('setting')->where('code', 'QC Special Category Item')->value('value1');
            $explodeSpecialCategory = explode(',', $specialCategory);

            $listItem = $parent->detailgroup->whereIn('id_kategori_barang', $explodeSpecialCategory);
        }

        return response()->json([
            'result'      => true,
            'list_item'   => $listItem,
            'qc'          => $parent->qc,
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
                "result"  => false,
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
            'data'        => $data,
            'arrayStatus' => $this->arrayStatus,
            "pageTitle"   => "SCA OPS | QC Penerimaan Pembelian | Cetak",
        ]);
    }

    public function findDataQc(Request $request)
    {
        $id   = isset($request->id) ? $request->id : 0;
        $data = QualityControl::find($id);
        if ($data) {
            return response()->json([
                'status'            => 'success',
                'kodePenerimaan'    => $data->purchase->nama_pembelian,
                'namaBarang'        => $data->barang->nama_barang,
                'urlToChangeStatus' => route('qc_receipt-save-change-status', $id),
                'jumlah'            => formatNumber($data->jumlah_pembelian_detail) . ' ' . $data->satuan->nama_satuan_barang,
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Data tidak ditemukan',
        ], 500);
    }

    public function saveChangeStatus(Request $request, $id)
    {
        DB::beginTransaction();
        $data = QualityControl::find($id);
        try {
            $data->approval_user_id = session()->get('user')['id_pengguna'];
            $data->approval_date    = date('Y-m-d');
            $data->approval_reason  = $request->approval_reason;
            $data->status_qc        = 1;
            $data->save();
            $data->updatePembelianDetail();
            DB::commit();

            $this->callApiPembelianNative($data->id_pembelian);
            return response()->json([
                "result"   => true,
                "message"  => "Data berhasil disimpan",
                "redirect" => route('qc_receipt'),
            ], 200);
        } catch (\Exception $th) {
            DB::rollback();
            Log::error("Error when change save qc receipt");
            Log::error($th);
            return response()->json([
                "result"  => false,
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
