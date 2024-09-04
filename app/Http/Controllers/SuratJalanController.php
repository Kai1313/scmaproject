<?php

namespace App\Http\Controllers;

use App\Models\SuratJalan;
use DB;
use Illuminate\Http\Request;
use Log;
use PDF;
use Yajra\DataTables\DataTables;

class SuratJalanController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'surat_jalan_umum', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('surat_jalan')->select('surat_jalan.*', 'pengguna.nama_pengguna')
                ->join('pengguna', 'surat_jalan.id_pengguna', '=', 'pengguna.id_pengguna')
                ->where('jenis', 'surat_jalan_umum')
                ->orderBy('tanggal', 'desc')
                ->orderBy('no_surat_jalan', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn .= '<a href="' . route('surat_jalan_umum-print-data', $row->id) . '" class="btn btn-default btn-xs mr-1 mb-1" target="_blank"><i class="fa fa-print"></i> Cetak</a>';
                    $btn .= '<a href="' . route('surat_jalan_umum-view', $row->id) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a>';
                    if ($row->id_pengguna == session()->get('user')['id_pengguna']) {
                        $btn .= '<a href="' . route('surat_jalan_umum-entry', $row->id) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a>';
                    }

                    if ($row->id_pengguna == session()->get('user')['id_pengguna'] && date('Y-m-d', strtotime($row->created_at)) == date('Y-m-d')) {
                        $btn .= '<a href="' . route('surat_jalan_umum-delete', $row->id) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Hapus</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('ops.suratJalan.index', [
            "pageTitle" => "SCA OPS | Surat Jalan Umum | List",
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('surat_jalan_umum', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = SuratJalan::find($id);
        return view('ops.suratJalan.form', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Surat Jalan Umum | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = SuratJalan::find($id);
        if (!$data) {
            $data = new SuratJalan;
        } else {
            if ($data->id_pengguna != session()->get('user')['id_pengguna']) {
                return response()->json([
                    "result" => false,
                    "message" => "Tidak mendapat akses",
                ], 500);
            }
        }

        DB::beginTransaction();
        try {
            $data->fill($request->all());
            if ($id == 0) {
                $data->no_surat_jalan = SuratJalan::createcode();
                $data->jenis = 'surat_jalan_umum';
                $data->id_pengguna = session()->get('user')['id_pengguna'];
            }

            $data->save();

            $resSave = $data->savedetails($request->details);
            if ($resSave['result'] == false) {
                DB::rollback();
                return response()->json($resSave, 500);
            }

            $resDelete = $data->deletedetails($request->detele_details);
            if ($resDelete['result'] == false) {
                DB::rollback();
                return response()->json($resDelete, 500);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('surat_jalan_umum-entry', $data->id),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save surat jalan umum");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function viewData($id)
    {
        if (checkAccessMenu('surat_jalan_umum', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = SuratJalan::find($id);
        return view('ops.suratJalan.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Surat Jalan Umum | Lihat",
        ]);
    }

    public function destroy($id)
    {
        if (checkAccessMenu('surat_jalan_umum', 'delete') == false) {
            return response()->json([
                "result" => false,
                "message" => "Tidak mendapatkan akses halaman",
            ], 500);
        }

        $data = SuratJalan::find($id);
        if (!$data) {
            return response()->json([
                "result" => false,
                "message" => "Data tidak ditemukan",
            ], 500);
        }

        if (date('Y-m-d', strtotime($data->created_at)) != date('Y-m-d')) {
            return response()->json([
                "result" => false,
                "message" => "Hapus surat jalan bisa dihapus di hari yang sama dengan pembuatan",
            ], 500);
        }

        if ($data->id_pengguna != session()->get('user')['id_pengguna']) {
            return response()->json([
                "result" => false,
                "message" => "Tidak mendapat akses",
            ], 500);
        }

        try {
            DB::beginTransaction();
            SuratJalanDetail::where('id_surat_jalan', $id)->delete();

            $data->delete();
            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dihapus",
                "redirect" => route('surat_jalan_umum'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error ketika hapus surat jalan");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal dihapus",
            ], 500);
        }
    }

    public function printData($id)
    {
        if (checkAccessMenu('surat_jalan_umum', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = SuratJalan::find($id);
        if (!$data) {
            return 'data tidak ditemukan';
        }
        $month = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $pdf = PDF::loadView('ops.suratJalan.print', ['data' => $data, 'month' => $month]);
        $pdf->setPaper('a5', 'landscape');
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $font = $dom_pdf->getFontMetrics()->get_font("sans-serif", "normal");
        $canvas = $dom_pdf->get_canvas();
        $canvas->page_text(518, 74, "{PAGE_NUM} dari {PAGE_COUNT}", $font, 9, array(0, 0, 0));

        return $pdf->stream('Surat Jalan Umum ' . $data->no_surat_jalan . '.pdf');
    }

    public function saveImage(Request $request, $id)
    {
        $data = SuratJalan::find($id);
        if (!$data) {
            return response()->json(['result' => false, 'message' => 'Data tidak ditemukan'], 500);
        }

        $res = $data->uploadfile($request->data_url);
        if ($res['result'] == false) {
            return response()->json($res, 500);
        }

        return response()->json(['result' => false, 'message' => 'Berhasil upload foto', 'id' => $res['data']->id_media], 200);
    }

    public function rmImage(Request $request, $id)
    {
        $data = SuratJalan::find($id);
        if (!$data) {
            return response()->json(['result' => false, 'message' => 'Data tidak ditemukan'], 500);
        }

        $res = $data->removefile($request->id_media);
        if ($res['result'] == false) {
            return response()->json($res, 500);
        }

        return response()->json(['result' => false, 'message' => 'Berhasil Berhasil dihapus'], 200);
    }
}
