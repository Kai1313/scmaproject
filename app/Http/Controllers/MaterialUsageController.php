<?php

namespace App\Http\Controllers;

use App\MaterialUsage;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class MaterialUsageController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'pemakaian_header', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('pemakaian_header')
                ->select(
                    'id_pemakaian',
                    'kode_pemakaian',
                    'tanggal',
                    'g.nama_gudang',
                    'c.nama_cabang',
                    'catatan'
                )
                ->leftJoin('gudang as g', 'pemakaian_header.id_gudang', '=', 'g.id_gudang')
                ->leftJoin('cabang as c', 'pemakaian_header.id_cabang', '=', 'c.id_cabang');
            if (isset($request->c)) {
                $data = $data->where('pemakaian_header.id_cabang', $request->c);
            }

            $data = $data->orderBy('pemakaian_header.dt_created', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="horizontal-list">';
                    $btn .= '<li><a href="' . route('material_usage-view', $row->id_pemakaian) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    $btn .= '<li><a href="' . route('material_usage-entry', $row->id_pemakaian) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    // $btn .= '<li><a href="' . route('received_from_branch-delete', $row->id_pindah_barang) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();
        return view('ops.materialUsage.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Pemakaian | List",
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('pemakaian_header', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MaterialUsage::find($id);
        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();
        $timbangan = DB::table('konfigurasi')->select('id_konfigurasi as id', 'nama_konfigurasi as text', 'keterangan_konfigurasi as value')
            ->where('id_kategori_konfigurasi', 5)->get();

        return view('ops.materialUsage.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Pemakaian | " . ($id == 0 ? 'Create' : 'Edit'),
            "timbangan" => $timbangan,
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = MaterialUsage::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new MaterialUsage;
            }

            $data->fill($request->all());
            if ($id == 0) {
                $data->kode_pemakaian = MaterialUsage::createcode($request->id_cabang);
                $data->user_created = session()->get('user')['id_pengguna'];
            } else {
                $data->user_modified = session()->get('user')['id_pengguna'];
            }

            $data->save();
            $data->savedetails($request->details);

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('material_usage'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save material usage");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function viewData($id)
    {
        if (checkAccessMenu('pemakaian_header', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MaterialUsage::find($id);
        return view('ops.materialUsage.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Pemakaian | Detail",
        ]);
    }

    public function autoQRCode(Request $request)
    {
        $idCabang = $request->id_cabang;
        $idGudang = $request->id_gudang;
        $qrcode = $request->qrcode;

        $data = DB::table('master_qr_code as mqc')
            ->select(
                'kode_batang_master_qr_code as kode_batang',
                'nama_barang',
                'mqc.id_barang',
                'nama_satuan_barang',
                'mqc.id_satuan_barang',
                'sisa_master_qr_code',
                'isweighed',
                'master_wrapper.weight as wrapper_weight',
                'id_wrapper_zak'
            )
            ->leftJoin('barang', 'mqc.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang as sb', 'mqc.id_satuan_barang', '=', 'sb.id_satuan_barang')
            ->leftJoin('master_wrapper', 'mqc.id_wrapper_zak', '=', 'master_wrapper.id_wrapper')
            ->where('mqc.id_cabang', $idCabang)
            ->where('mqc.id_gudang', $idGudang)
            ->where('kode_batang_master_qr_code', $qrcode)->first();
        return response()->json([
            'data' => $data,
        ]);
    }

    public function reloadWeight(Request $request)
    {
        $id = $request->id;
        $value = 0;
        $data = DB::table('konfigurasi')
            ->where('id_kategori_konfigurasi', 5)
            ->where('id_konfigurasi', $id)
            ->value('keterangan_konfigurasi');
        if ($data) {
            $value = $data;
        }

        return response()->json([
            'data' => $value,
        ], 200);
    }
}
