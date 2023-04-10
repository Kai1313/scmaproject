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
            $data = DB::table('pindah_barang')
                ->select(
                    'id_pindah_barang',
                    'type',
                    'g.nama_gudang as g_nama_gudang',
                    'tanggal_pindah_barang',
                    'kode_pindah_barang',
                    'g2.nama_gudang as g2_nama_gudang',
                    'status_pindah_barang',
                    'keterangan_pindah_barang',
                )
                ->leftJoin('gudang as g', 'pindah_barang.id_gudang', '=', 'g.id_gudang')
                ->leftJoin('gudang as g2', 'pindah_barang.id_gudang2', '=', 'g2.id_gudang')
                ->where('id_jenis_transaksi', 24)
                ->where('type', 1);
            if (isset($request->c)) {
                $data = $data->where('pindah_barang.id_cabang', $request->c);
            }

            $data = $data->orderBy('pindah_barang.kode_pindah_barang', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="horizontal-list">';
                    $btn .= '<li><a href="' . route('received_from_warehouse-view', $row->id_pindah_barang) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    $btn .= '<li><a href="' . route('received_from_warehouse-entry', $row->id_pindah_barang) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    // $btn .= '<li><a href="' . route('received_from_branch-delete', $row->id_pindah_barang) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                    $btn .= '</ul>';
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

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();
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
        $cabang = DB::table('cabang')->select('nama_cabang as text', 'id_cabang as id')->where('status_cabang', 1)->get();

        return view('ops.receivedFromWarehouse.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Terima Dari Gudang | Lihat",
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        // return $request->all();
        $data = MoveBranch::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new MoveBranch;
            }

            $data->fill($request->all());
            if ($id == 0) {
                $data->kode_pindah_barang = MoveBranch::createcodeGudang($request->id_cabang);
                $data->status_pindah_barang = 1;
                $data->type = 1;
                $data->user_created = session()->get('user')['id_pengguna'];
            } else {
                $data->user_modified = session()->get('user')['id_pengguna'];
            }

            $data->save();
            $data->saveDetails($request->details, 'in');

            $parent = MoveBranch::find($data->id_pindah_barang2);
            $parent->status_pindah_barang = 1;
            $parent->save();

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('received_from_warehouse'),
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
        if (checkAccessMenu('terima_dari_cabang', 'show') == false) {
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

        if ($data && $data->status_pindah_barang == 1) {
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
}
