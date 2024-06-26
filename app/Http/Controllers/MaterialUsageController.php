<?php

namespace App\Http\Controllers;

use App\MasterQrCode;
use App\MaterialUsage;
use DB;
use Illuminate\Http\Request;
use Log;
use PDF;
use Yajra\DataTables\DataTables;

class MaterialUsageController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'pemakaian', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('pemakaian_header')
                ->select(
                    'pemakaian_header.*',
                    'g.nama_gudang',
                    'c.nama_cabang'
                )
                ->leftJoin('gudang as g', 'pemakaian_header.id_gudang', '=', 'g.id_gudang')
                ->leftJoin('cabang as c', 'pemakaian_header.id_cabang', '=', 'c.id_cabang');
            if (isset($request->c)) {
                $data = $data->where('pemakaian_header.id_cabang', $request->c);
            }

            if ($request->show_void == 'false') {
                $data = $data->where('pemakaian_header.void', '0');
            }

            $data = $data->orderBy('pemakaian_header.dt_created', 'desc');

            $idUser = session()->get('user')['id_pengguna'];
            $idGrupUser = session()->get('user')['id_grup_pengguna'];
            $filterUser = DB::table('pengguna')
                ->where(function ($w) {
                    $w->where('id_grup_pengguna', session()->get('user')['id_grup_pengguna'])->orWhere('id_grup_pengguna', 1);
                })
                ->where('status_pengguna', '1')->pluck('id_pengguna')->toArray();
            $accessVoid = getSetting('Pemakaian Void');
            $arrayAccessVoid = explode(',', $accessVoid);

            return Datatables::of($data)
                ->addColumn('action', function ($row) use ($filterUser, $idUser, $idGrupUser, $arrayAccessVoid) {
                    if ($row->void == '1') {
                        $btn = '<label class="label label-default">Batal</label>';
                        $btn .= '<ul class="horizontal-list">';
                        $btn .= '<li><a href="' . route('material_usage-view', $row->id_pemakaian) . '" class="btn btn-info btn-xs mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        $btn .= '</ul>';
                        return $btn;
                    } else {
                        $btn = '<ul class="horizontal-list">';
                        $btn .= '<li><a href="' . route('material_usage-view', $row->id_pemakaian) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                        $btn .= '<li><a href="' . route('material_usage-print-data', $row->id_pemakaian) . '" class="btn btn-default btn-xs mr-1 mb-1" target="_blank"><i class="glyphicon glyphicon-print"></i> Cetak</a></li>';
                        if (in_array($idUser, $filterUser) || $idUser == $row->user_created) {
                            $btn .= '<li><a href="' . route('material_usage-entry', $row->id_pemakaian) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                        }

                        if (in_array($idGrupUser, $arrayAccessVoid) || $idUser == $row->user_created) {
                            $btn .= '<li><a href="' . route('material_usage-delete', $row->id_pemakaian) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                        }

                        $btn .= '</ul>';
                        return $btn;
                    }

                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $cabang = session()->get('access_cabang');
        return view('ops.materialUsage.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Pemakaian | List",
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('pemakaian', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MaterialUsage::find($id);
        $accessQC = getSetting('Pemakaian QC');
        $cabang = session()->get('access_cabang');
        $timbangan = DB::table('konfigurasi')->select('id_konfigurasi as id', 'nama_konfigurasi as text', 'keterangan_konfigurasi as value')
            ->where('id_kategori_konfigurasi', 5)->get();
        $types = DB::table('setting')->where('id_cabang', 1)->where('code', 'like', 'HPP Pemakaian %')->get();
        return view('ops.materialUsage.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Pemakaian | " . ($id == 0 ? 'Create' : 'Edit'),
            "timbangan" => $timbangan,
            'accessQc' => in_array(session()->get('user')['id_grup_pengguna'], explode(',', $accessQC)) ? '1' : '0',
            'types' => $types,
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = MaterialUsage::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new MaterialUsage;
                $period = $this->checkPeriod($request->tanggal);
                if ($period['result'] == false) {
                    return response()->json($period, 500);
                }
            } else {
                $period = $this->checkPeriod($data->tanggal);
                if ($period['result'] == false) {
                    return response()->json($period, 500);
                }

                // $rev = $data->revertMasterQrcode();
                // if ($rev['result'] == false) {
                //     DB::rollback();
                //     return response()->json($rev, 500);
                // }
            }

            $data->fill($request->except('is_qc'));
            if ($id == 0) {
                $data->kode_pemakaian = MaterialUsage::createcode($request->id_cabang);
                $data->user_created = session()->get('user')['id_pengguna'];
                $data->is_qc = isset($request->is_qc) ? $request->is_qc : 0;
            } else {
                $data->user_modified = session()->get('user')['id_pengguna'];
            }

            $data->save();

            // $checkStock = $data->checkStockDetails($request->details);
            // if ($checkStock['result'] == false) {
            //     DB::rollback();
            //     return response()->json($checkStock, 500);
            // }

            // $resSave = $data->savedetails($request->details);
            // if ($resSave['result'] == false) {
            //     DB::rollback();
            //     return response()->json($resSave, 500);
            // }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('material_usage-entry', $data->id_pemakaian),
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
        if (checkAccessMenu('pemakaian', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MaterialUsage::find($id);
        $accessQC = getSetting('Pemakaian QC');
        return view('ops.materialUsage.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Pemakaian | Detail",
            'accessQc' => in_array(session()->get('user')['id_grup_pengguna'], explode(',', $accessQC)) ? '1' : '0',
        ]);
    }

    public function destroy($id)
    {
        if (checkAccessMenu('pemakaian', 'delete') == false) {
            return response()->json(['message' => 'Tidak mempunyai akses'], 500);
        }

        $data = MaterialUsage::find($id);
        if (!$data) {
            return response()->json([
                "result" => false,
                "message" => "Data tidak ditemukan",
            ], 500);
        }

        $period = $this->checkPeriod($data->tanggal);
        if ($period['result'] == false) {
            return response()->json($period, 500);
        }

        try {
            DB::beginTransaction();
            $data->void = 1;
            $data->void_user_id = session()->get('user')['id_pengguna'];
            $data->save();

            $data->voidDetails();

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dibatalkan",
                "redirect" => route('material_usage'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void pemakaian");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal dibatalkan",
            ], 500);
        }
    }

    public function autoQRCode(Request $request)
    {
        $idCabang = $request->id_cabang;
        $idGudang = $request->id_gudang;
        $qrcode = $request->qrcode;
        $isQc = $request->is_qc;

        $data = DB::table('master_qr_code as mqc')
            ->select(
                'kode_batang_master_qr_code as kode_batang',
                'nama_barang',
                'mqc.id_barang',
                'nama_satuan_barang',
                'mqc.id_satuan_barang',
                'sisa_master_qr_code',
                'isweighed',
                'id_wrapper_zak',
                DB::raw('IFNULL(weight_zak,0) as weight_zak'),
                DB::raw('IFNULL(zak,0) as jumlah_zak'),
                'mqc.status_qc_qr_code',
                'id_rak'
            )
            ->leftJoin('barang', 'mqc.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang as sb', 'mqc.id_satuan_barang', '=', 'sb.id_satuan_barang')
            ->leftJoin('master_wrapper', 'mqc.id_wrapper_zak', '=', 'master_wrapper.id_wrapper')
            ->where('mqc.id_cabang', $idCabang)
            ->where('mqc.id_gudang', $idGudang);

        $data = $data->where('kode_batang_master_qr_code', $qrcode)->first();
        if (!$data) {
            return response()->json([
                'message' => 'Barang tidak ditemukan',
                'status' => 'error',
            ], 500);
        }

        if ($data->sisa_master_qr_code == 0) {
            return response()->json([
                'message' => 'Barang sudah habis',
                'status' => 'error',
            ], 500);
        }

        if ($data->id_rak) {
            return response()->json([
                'message' => 'Barang masih dalam rak',
                'status' => 'error',
            ], 500);
        }

        if ($isQc == 0 && $data->status_qc_qr_code == 0) {
            return response()->json([
                'message' => 'Barang belum di QC',
                'status' => 'error',
            ], 500);
        }

        return response()->json([
            'data' => $data,
        ], 200);
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
            $value = $id == 38 ? (number_format($data / 1000, 4)) : $data;
        }

        return response()->json([
            'data' => $value,
        ], 200);
    }

    public function printData($id)
    {
        if (checkAccessMenu('pemakaian', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MaterialUsage::where('id_pemakaian', $id)->first();
        if (!$data) {
            return 'data tidak ditemukan';
        }

        $pdf = PDF::loadView('ops.materialUsage.print', ['data' => $data]);
        $pdf->setPaper('a5', 'landscape');
        return $pdf->stream('Pemakaian ' . $data->kode_pemakaian . '.pdf');
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

    public function saveDetailEntry(Request $request, $id)
    {
        $data = MaterialUsage::find($id);
        if (!$data) {
            return response()->json(['result' => false, 'message' => 'Pemakaian tidak ditemukan'], 500);
        }

        $stock = MasterQrCode::where('kode_batang_master_qr_code', $request->kode_batang)->first();
        if (!$stock) {
            return response()->json(['result' => false, 'message' => 'Stok tidak ditemukan'], 500);
        }

        if ($stock->sisa_master_qr_code < $request->jumlah) {
            return response()->json(['result' => false, 'message' => 'Stok tidak mencukupi'], 500);
        }

        DB::beginTransaction();
        $s = $data->savedetail($request);
        if ($s['result'] == false) {
            DB::rollback();
            return response()->json($s, 500);
        }

        DB::commit();
        return response()->json([
            "result" => true,
            "message" => "Data berhasil disimpan",
            "redirect" => route('material_usage-entry', $id),
        ], 200);
    }

    public function deleteDetail($parent, $id)
    {
        $data = MaterialUsage::where('id_pemakaian', $parent)->first();
        if (!$data) {
            return response()->json(['result' => false, 'message' => 'Data tidak ditemukan'], 500);
        }

        DB::beginTransaction();
        $r = $data->deleteDetail($id);
        if ($r['result'] == false) {
            DB::rollback();
            return response()->json($r, 500);
        }

        DB::commit();
        return response()->json([
            "result" => true,
            "message" => "Data berhasil diproses",
            "redirect" => route('material_usage-entry', $parent),
        ], 200);
    }
}
