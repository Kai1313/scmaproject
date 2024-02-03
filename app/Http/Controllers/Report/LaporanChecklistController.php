<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LaporanChecklistController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_checklist', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getData($request, 'datatable');
        }

        return view('report_ops.laporanChecklist.index', [
            "pageTitle" => "SCA OPS | Laporan Checklist | List",
            // 'typeReport' => ['Rekap', 'Detail'],
        ]);
    }

    public function getLocation(Request $request)
    {
        $search = $request->search;
        $datas = DB::table('objek_kerja')
            ->select('id_objek_kerja as id', DB::raw('concat(kode_objek_kerja," - ",nama_objek_kerja) as text'))
            ->where(DB::raw('concat(kode_objek_kerja," - ",nama_objek_kerja)'), 'like', '%' . $search . '%')
            ->where('status_objek_kerja', '1')->get();
        return response()->json(['status' => 'success', 'datas' => $datas], 200);
    }

    public function getUserGroup(Request $request)
    {
        $date = $request->date;
        $rangeDate = explode(' - ', $date);
        $location = $request->location;

        $datas = DB::table('jawaban_checklist_pekerjaan as jcp')
            ->select('nama_grup_pengguna as text', 'jcp.id_grup_pengguna as id')
            ->join('grup_pengguna as gp', 'jcp.id_grup_pengguna', 'gp.id_grup_pengguna')
            ->whereBetween('tanggal_jawaban_checklist_pekerjaan', $rangeDate)
            ->where('id_objek_kerja', $location)
            ->groupBy('jcp.id_grup_pengguna')
            ->get();

        return response()->json(['status' => 'success', 'datas' => $datas], 200);
    }

    public function getData($request, $type)
    {
        $date = explode(' - ', $request->date);

        $data = DB::table('jawaban_checklist_pekerjaan as jcp')->select(
            'jcp.tanggal_jawaban_checklist_pekerjaan',
            'jcp.kode_jawaban_checklist_pekerjaan',
            'ok.nama_objek_kerja',
            'nama_pengguna',
            'gp.nama_grup_pengguna',
            'id_jawaban_checklist_pekerjaan'
        )
            ->join('objek_kerja as ok', 'jcp.id_objek_kerja', 'ok.id_objek_kerja')
            ->join('grup_pengguna as gp', 'jcp.id_grup_pengguna', 'gp.id_grup_pengguna')
            ->join('pengguna as p', 'jcp.user_jawaban_checklist_pekerjaan', 'p.id_pengguna')
            ->whereBetween('jcp.tanggal_jawaban_checklist_pekerjaan', $date)
            ->where('jcp.id_objek_kerja', $request->location)
            ->where('jcp.id_grup_pengguna', $request->user_group);

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }

    public function print(Request $request)
    {
        if (checkAccessMenu('laporan_checklist', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request, 'print');
        $array = [
            "datas" => $data,
            'date' => $request->date,
        ];

        $pdf = PDF::loadView('report_ops.laporanChecklist.print', $array);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('laporan checklist pekerjaan.pdf');
    }

    public function getExcel(Request $request)
    {
        if (checkAccessMenu('laporan_checklist', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request, 'print');
        $arrayCabang = [];

        $array = [
            "datas" => $data,
            'date' => $request->date,
        ];
        return Excel::download(new ReportPurchaseDownPaymentExport('report_ops.laporanChecklist.excel', $array), 'laporan checklist pekerjaan.xlsx');
    }

    public function viewData($id)
    {
        $data = DB::table('jawaban_checklist_pekerjaan as jcp')
            ->join('grup_pengguna as gp', 'jcp.id_grup_pengguna', 'gp.id_grup_pengguna')
            ->join('pengguna as p', 'jcp.user_jawaban_checklist_pekerjaan', 'p.id_pengguna')
            ->join('objek_kerja as ok', 'jcp.id_objek_kerja', 'ok.id_objek_kerja')
            ->where('id_jawaban_checklist_pekerjaan', $id)
            ->first();

        if (!$data) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $medias = DB::table('media_jawaban')->where('id_jawaban_checklist_pekerjaan', $id)->get();
        $groupMedia = [];
        foreach ($medias as $media) {
            $groupMedia[$media->id_pekerjaan][] = [
                'id' => $media->id_media_jawaban,
                'image' => env('OLD_URL_ROOT') . 'uploads/checklist_pekerjaan/' . $media->lokasi_media_jawaban,
            ];
        }
        $jobs = DB::table('pekerjaan')->where('status_pekerjaan', '1')->pluck('nama_pekerjaan', 'id_pekerjaan');

        return view('report_ops.laporanChecklist.view', [
            "pageTitle" => "SCA OPS | Laporan Checklist | View",
            'data' => $data,
            'medias' => $groupMedia,
            'jobs' => $jobs,
        ]);
    }
}
