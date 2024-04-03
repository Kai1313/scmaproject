<?php

namespace App\Http\Controllers;

use App\MoveBranch;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class ReceivedFromWarehouseController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'terima_dari_gudang', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('pindah_barang as pb')
                ->select(
                    'pb.id_pindah_barang',
                    'pb.type',
                    'g.nama_gudang as g_nama_gudang',
                    'pb.tanggal_pindah_barang',
                    'pb.kode_pindah_barang',
                    'g2.nama_gudang as g2_nama_gudang',
                    'pb.status_pindah_barang',
                    'pb.keterangan_pindah_barang',
                    'pb2.kode_pindah_barang as ref_code'
                )
                ->leftJoin('gudang as g', 'pb.id_gudang', '=', 'g.id_gudang')
                ->leftJoin('gudang as g2', 'pb.id_gudang2', '=', 'g2.id_gudang')
                ->leftJoin('pindah_barang as pb2', 'pb.id_pindah_barang2', 'pb2.id_pindah_barang')
                ->where('pb.id_jenis_transaksi', 24)
                ->where('pb.type', 1)
                ->where('pb.status_pindah_barang', '!=', '2');
            if (isset($request->c)) {
                $data = $data->where('pb.id_cabang', $request->c);
            }

            $data = $data->orderBy('pb.tanggal_pindah_barang', 'desc')->orderBy('pb.kode_pindah_barang', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn .= '<a href="' . route('received_from_warehouse-view', $row->id_pindah_barang) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a>';
                    $btn .= '<a href="' . route('received_from_warehouse-entry', $row->id_pindah_barang) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a>';
                    return $btn;
                })
                ->editColumn('status_pindah_barang', function ($row) {
                    if ($row->status_pindah_barang == '0') {
                        return '<label class="label label-warning">Dalam Perjalanan</label>';
                    } else if ($row->status_pindah_barang == '1') {
                        return '<label class="label label-success">Diterima</label>';
                    } else {
                        return '';
                    }
                })
                ->rawColumns(['action', 'status_pindah_barang'])
                ->make(true);
        }

        $cabang = session()->get('access_cabang');
        return view('ops.receivedFromWarehouse.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Terima Dari Gudang | List",
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('terima_dari_gudang', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MoveBranch::find($id);
        $cabang = session()->get('access_cabang');
        return view('ops.receivedFromWarehouse.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Terima Dari Gudang | Lihat",
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = MoveBranch::find($id);
        $detail = json_decode($request->details);
        if (count($detail) <= 0) {
            return response()->json([
                "result" => false,
                "message" => "List barang tidak ditemukan",
            ], 500);
        }

        try {
            DB::beginTransaction();
            if (!$data) {
                $check = MoveBranch::where('id_jenis_transaksi', 24)->where('id_pindah_barang2', $request->id_pindah_barang2)->first();
                if ($check) {
                    return response()->json([
                        "result" => false,
                        "message" => "Pindah barang sudah diterima dengan device lain",
                    ], 500);
                } else {
                    $data = new MoveBranch;
                    $period = $this->checkPeriod($request->tanggal_pindah_barang);
                    if ($period['result'] == false) {
                        return response()->json($period, 500);
                    }
                }
            } else {
                $period = $this->checkPeriod($data->tanggal_pindah_barang);
                if ($period['result'] == false) {
                    return response()->json($period, 500);
                }
            }

            $data->fill($request->all());
            if ($id == 0) {
                $data->kode_pindah_barang = MoveBranch::createcodeGudang($request->id_cabang, $request->tanggal_pindah_barang);
                $data->status_pindah_barang = 0;
                $data->type = 1;
                $data->user_created = session()->get('user')['id_pengguna'];
            } else {
                $data->user_modified = session()->get('user')['id_pengguna'];
            }

            $data->save();
            $data->saveDetails($request->details, 'in');

            $parent = MoveBranch::find($data->id_pindah_barang2);
            $parent->saveChangeStatusFromParent($data->details->pluck('qr_code')->toArray());

            if (count($data->details) == count($parent->details)) {
                $data->status_pindah_barang = 1;
                $data->save();

                $updateParent = MoveBranch::find($data->id_pindah_barang2);
                $updateParent->status_pindah_barang = 1;
                $updateParent->save();
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('received_from_warehouse-entry', $data->id_pindah_barang),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save received from warehouse");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function viewData($id)
    {
        if (checkAccessMenu('terima_dari_gudang', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MoveBranch::where('type', 1)->where('id_pindah_barang', $id)->first();
        return view('ops.receivedFromWarehouse.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Terima Dari Gudang | Detail",
        ]);
    }

    public function autoQRCode(Request $request)
    {
        $data = MoveBranch::where('id_jenis_transaksi', 23)
            ->with(['cabang', 'cabang2', 'gudang', 'gudang2'])
            ->where('type', 0)
            ->where('kode_pindah_barang', $request->qrcode)

            ->first();
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode tidak ditemukan',
            ], 500);
        }

        if ($data->status_pindah_barang == 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pindah barang sudah diterima',
            ], 500);
        }

        $check = MoveBranch::where('id_jenis_transaksi', 24)
            ->where('id_pindah_barang2', $data->id_pindah_barang)
            ->first();
        if ($check) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pindah barang sudah diterima',
            ], 500);
        }

        return response()->json([
            'data' => $data,
            'details' => $data->formatdetail,
            'parent' => $data->parent,
        ]);
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
