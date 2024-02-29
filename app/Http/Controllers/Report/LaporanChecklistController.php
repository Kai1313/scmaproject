<?php

namespace App\Http\Controllers\Report;

use App\Exports\ChecklistExport;
use App\Http\Controllers\Controller;
use App\JawabanChecklistPekerjaan;
use DB;
use Excel;
use Illuminate\Http\Request;
use Log;
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

        $locations = DB::table('objek_kerja')
            ->select('alamat_objek_kerja')
            ->where('status_objek_kerja', '1')->distinct()->orderBy('kota_objek_kerja', 'asc')->orderBy('alamat_objek_kerja', 'asc')->get();

        $groups = DB::table('grup_pengguna')->where('status_grup_pengguna', '1')->pluck('nama_grup_pengguna', 'id_grup_pengguna');
        $userGroups = DB::table('checklist_pekerjaan')->select('id_grup_pengguna')->where('status_checklist_pekerjaan', '1')->distinct()->get();
        $array = [];
        foreach ($userGroups as $ug) {
            $array[] = [
                'id' => $ug->id_grup_pengguna,
                'text' => isset($groups[$ug->id_grup_pengguna]) ? $groups[$ug->id_grup_pengguna] : '',
            ];
        }

        return view('report_ops.laporanChecklist.index', [
            "pageTitle" => "SCA OPS | Laporan Checklist | List",
            'locations' => $locations,
            'users' => collect($array),
        ]);
    }

    public function getData($request, $type)
    {
        $date = $request->date;
        $location = $request->location;
        $userGroup = $request->user_group;

        $datas = DB::table('checklist_pekerjaan as cp')
            ->select('nama_objek_kerja', 'cp.id_objek_kerja', 'id_jawaban_checklist_pekerjaan')
            ->join('objek_kerja as ok', 'cp.id_objek_kerja', 'ok.id_objek_kerja')
            ->leftJoin('jawaban_checklist_pekerjaan as jcp', function ($q) use ($date, $userGroup) {
                $q->on('ok.id_objek_kerja', 'jcp.id_objek_kerja')
                    ->where('jcp.tanggal_jawaban_checklist_pekerjaan', $date);
            })
            ->where('cp.id_grup_pengguna', $userGroup)
            ->where('ok.alamat_objek_kerja', $location)
            ->where('ok.status_objek_kerja', '1')
            ->groupBy('ok.id_objek_kerja');

        if ($type == 'datatable') {
            return Datatables::of($datas)
                ->toJson();
        }

        $datas = $datas->get();
        return $datas;
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

    public function viewData(Request $request, $id)
    {
        $idObjekKerja = $id;
        $date = $request->date;
        $group = $request->grup;

        $data = DB::table('jawaban_checklist_pekerjaan as jcp')
            ->select('jcp.*', 'nama_grup_pengguna', 'p.nama_pengguna', 'nama_objek_kerja', 'pc.nama_pengguna as nama_pengguna_checker')
            ->join('grup_pengguna as gp', 'jcp.id_grup_pengguna', 'gp.id_grup_pengguna')
            ->join('pengguna as p', 'jcp.user_jawaban_checklist_pekerjaan', 'p.id_pengguna')
            ->leftJoin('pengguna as pc', 'jcp.checker_jawaban_checklist_pekerjaan', 'pc.id_pengguna')
            ->join('objek_kerja as ok', 'jcp.id_objek_kerja', 'ok.id_objek_kerja')
            ->where('jcp.id_objek_kerja', $idObjekKerja)
            ->where('tanggal_jawaban_checklist_pekerjaan', $date)
            ->where('jcp.id_grup_pengguna', $group)
            ->first();

        if (!$data) {
            return view('exceptions.forbidden', ["pageTitle" => "Belum Dikerjakan"]);
        }

        $medias = DB::table('media_jawaban')->where('id_jawaban_checklist_pekerjaan', $data->id_jawaban_checklist_pekerjaan)->get();
        $groupMedia = [];
        foreach ($medias as $media) {
            $groupMedia[$media->id_pekerjaan][] = [
                'id' => $media->id_media_jawaban,
                'image' => env('OLD_ASSET_ROOT') . 'uploads/checklist_pekerjaan/' . $media->lokasi_media_jawaban,
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

    public function sendChecker(Request $request)
    {
        $id = $request->id;
        $seq = $request->seq;
        $val = $request->val == '1' ? '1' : '0';
        $input = 'checker' . $seq . '_jawaban_checklist_pekerjaan';
        try {
            $data = JawabanChecklistPekerjaan::where('id_jawaban_checklist_pekerjaan', $id)->first();
            $array = [];
            if (!$data->checker_jawaban_checklist_pekerjaan) {
                $array['checker_jawaban_checklist_pekerjaan'] = session()->get('user')['id_pengguna'];
            }

            $array[$input] = $val;
            DB::table('jawaban_checklist_pekerjaan')->where('id_jawaban_checklist_pekerjaan', $id)
                ->update($array);

            return response()->json(['status' => 'success', 'message' => 'Data berhasil di update'], 200);
        } catch (\Exception $th) {
            Log::error($th);
            return response()->json(['status' => 'error', 'message' => 'Terdapat masalah ketika checklist'], 500);
        }
    }

    public function sendCommentChecker(Request $request)
    {
        $note = $request->note;
        $id = $request->id;
        try {
            $data = JawabanChecklistPekerjaan::where('id_jawaban_checklist_pekerjaan', $id)->first();
            $array = [];
            if (!$data->checker_jawaban_checklist_pekerjaan) {
                $array['checker_jawaban_checklist_pekerjaan'] = session()->get('user')['id_pengguna'];
            }

            $array['keterangan_checker_jawaban_checklist_pekerjaan'] = $note;
            DB::table('jawaban_checklist_pekerjaan')->where('id_jawaban_checklist_pekerjaan', $id)
                ->update($array);

            return response()->json(['status' => 'success', 'message' => 'Berhasil di perbarui'], 200);
        } catch (\Exception $th) {
            Log::error($th);
            return response()->json(['status' => 'error', 'message' => 'Terdapat masalah ketika update catatan'], 500);
        }
    }

    public function getDataExport(Request $request)
    {
        if (checkAccessMenu('laporan_checklist', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $location = $request->location;
        $date = $request->date;
        $userGroup = $request->user_group;

        $group = DB::table('grup_pengguna')->where('id_grup_pengguna', $userGroup)->first();

        $locations = DB::table('objek_kerja')->select('id_objek_kerja', 'nama_objek_kerja')
            ->where('status_objek_kerja', '1')->where('alamat_objek_kerja', $location)->orderBy('nama_objek_kerja', 'asc')->get();
        $pluckId = $locations->pluck('id_objek_kerja');

        $jobs = DB::table('checklist_pekerjaan')
            ->join('pekerjaan', 'checklist_pekerjaan.id_pekerjaan', 'pekerjaan.id_pekerjaan')
            ->whereIn('id_objek_kerja', $pluckId)
            ->where('id_grup_pengguna', $userGroup)
            ->get();

        $answers = DB::table('jawaban_checklist_pekerjaan')
            ->whereIn('id_objek_kerja', $pluckId)
            ->where('id_grup_pengguna', $userGroup)
            ->where('tanggal_jawaban_checklist_pekerjaan', $date)->get();
        $arrayAns = [];
        foreach ($answers as $a => $ans) {
            for ($i = 1; $i < 26; $i++) {
                if ($ans->{'pekerjaan' . ($i) . '_jawaban_checklist_pekerjaan'}) {
                    $arrayAns[$ans->id_objek_kerja . '-' . $ans->{'pekerjaan' . ($i) . '_jawaban_checklist_pekerjaan'}] = [
                        'keterangan' => $ans->{'keterangan' . ($i) . '_jawaban_checklist_pekerjaan'},
                        'jawaban' => $ans->{'jawaban' . ($i) . '_jawaban_checklist_pekerjaan'},
                    ];
                } else {
                    break;
                }
            }
        }

        $array = [
            'locations' => $locations,
            'req' => $request,
            'jobs' => $jobs,
            'answers' => $arrayAns,
            'group' => $group,
        ];

        return Excel::download(new ChecklistExport('report_ops.laporanChecklist.excel', $array), 'laporan checklist pekerjaan.xlsx');
    }
}
