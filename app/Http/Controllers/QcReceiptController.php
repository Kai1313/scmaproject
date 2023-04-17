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
        ['text' => 'Pilih Status', 'class' => 'label label-default', 'id' => ''],
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
                    DB::raw('sum(pembelian_detail.jumlah_purchase) as jumlah_pembelian_detail'),
                    'status_qc',
                    'nama_satuan_barang',
                    'reason',
                    'qc.sg_pembelian_detail',
                    'qc.be_pembelian_detail',
                    'qc.ph_pembelian_detail',
                    'qc.warna_pembelian_detail',
                    'qc.keterangan_pembelian_detail',
                    'qc.bentuk_pembelian_detail'
                )
                ->leftJoin('qc', function ($qc) {
                    $qc->on('pembelian_detail.id_pembelian', '=', 'qc.id_pembelian')->on('pembelian_detail.id_barang', '=', 'qc.id_barang');
                })
                ->leftJoin('pembelian', 'pembelian_detail.id_pembelian', '=', 'pembelian.id_pembelian')
                ->leftJoin('barang', 'pembelian_detail.id_barang', '=', 'barang.id_barang')
                ->leftJoin('satuan_barang', 'pembelian_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
                ->whereBetween('pembelian.tanggal_pembelian', [$request->start_date, $request->end_date]);
            if (isset($request->c)) {
                $data = $data->where('pembelian.id_cabang', $request->c);
            }

            $data = $data->groupBy('pembelian_detail.id_pembelian', 'pembelian_detail.id_barang')
                ->orderBy('pembelian.tanggal_pembelian', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('status_qc', function ($row) {
                    if ($row->status_qc) {
                        return '<label class="' . $this->arrayStatus[$row->status_qc]['class'] . '">' . $this->arrayStatus[$row->status_qc]['text'] . '</label>';
                    } else {
                        return '<label class="label label-default">Belum di QC</label>';
                    }
                })
                ->rawColumns(['status_qc'])
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
        try {
            DB::beginTransaction();
            $datas = json_decode($request->details);
            foreach ($datas as $value) {
                $data = QualityControl::find($value->id);
                if (!$data) {
                    $data = new QualityControl;
                    $data->tanggal_qc = date('Y-m-d');
                    $data->id_cabang = $request->id_cabang;
                    $data->id_pembelian = $request->id_pembelian;
                    $data->id_barang = $value->id_barang;
                    $data->id_satuan_barang = $value->id_satuan_barang;
                    $data->jumlah_pembelian_detail = $value->jumlah_purchase;
                }

                $data->status_qc = $value->status_qc;
                $data->reason = $value->reason;
                $data->sg_pembelian_detail = $value->sg_pembelian_detail;
                $data->bentuk_pembelian_detail = $value->bentuk_pembelian_detail;
                $data->be_pembelian_detail = $value->be_pembelian_detail;
                $data->ph_pembelian_detail = $value->ph_pembelian_detail;
                $data->warna_pembelian_detail = $value->warna_pembelian_detail;
                $data->keterangan_pembelian_detail = $value->keterangan_pembelian_detail;
                $data->save();

                $data->updatePembelianDetail();
            }

            $resApi = $this->callApiPembelian($request->id_pembelian);

            DB::commit();
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
        $cabang = $request->cabang;
        $duration = DB::table('setting')->where('code', 'QC Duration')->first();
        $startDate = date('Y-m-d', strtotime('-' . intval($duration->value2) . ' days'));
        $endDate = date('Y-m-d');
        $datas = DB::table('pembelian')->select('nama_pembelian as text', 'id_pembelian as id', 'nama_pemasok', 'tanggal_pembelian', 'nomor_po_pembelian')
            ->leftJoin('pemasok', 'pembelian.id_pemasok', '=', 'pemasok.id_pemasok')
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->where('id_cabang', $cabang)
            ->orderBy('tanggal_pembelian', 'desc')->get();

        return response()->json([
            'result' => true,
            'data' => $datas,
        ], 200);
    }

    public function autoItem(Request $request)
    {
        $idPembelian = $request->number;
        $parent = Purchase::find($idPembelian);
        return response()->json([
            'result' => true,
            'list_item' => $parent->detailgroup,
            'qc' => $parent->qc,
            'route_print' => route('qc_receipt-print-data', $idPembelian),
        ], 200);
    }

    public function callApiPembelian($data)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $request = $client->get(env('OLD_API_ROOT') . "actions/aa_update_ppn_pembelian.php?id_pembelian=" . $data);
            $response = $request->getBody()->getContents();

            return $response;
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
}
