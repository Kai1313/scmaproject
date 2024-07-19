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
            ->select(
                'nama_objek_kerja',
                'cp.id_objek_kerja',
                'id_jawaban_checklist_pekerjaan',
                'pekerjaan1_jawaban_checklist_pekerjaan',
                'pekerjaan2_jawaban_checklist_pekerjaan',
                'pekerjaan3_jawaban_checklist_pekerjaan',
                'pekerjaan4_jawaban_checklist_pekerjaan',
                'pekerjaan5_jawaban_checklist_pekerjaan',
                'pekerjaan6_jawaban_checklist_pekerjaan',
                'pekerjaan7_jawaban_checklist_pekerjaan',
                'pekerjaan8_jawaban_checklist_pekerjaan',
                'pekerjaan9_jawaban_checklist_pekerjaan',
                'pekerjaan10_jawaban_checklist_pekerjaan',
                'pekerjaan11_jawaban_checklist_pekerjaan',
                'pekerjaan12_jawaban_checklist_pekerjaan',
                'pekerjaan13_jawaban_checklist_pekerjaan',
                'pekerjaan14_jawaban_checklist_pekerjaan',
                'pekerjaan15_jawaban_checklist_pekerjaan',
                'pekerjaan16_jawaban_checklist_pekerjaan',
                'pekerjaan17_jawaban_checklist_pekerjaan',
                'pekerjaan18_jawaban_checklist_pekerjaan',
                'pekerjaan19_jawaban_checklist_pekerjaan',
                'pekerjaan20_jawaban_checklist_pekerjaan',
                'pekerjaan21_jawaban_checklist_pekerjaan',
                'pekerjaan22_jawaban_checklist_pekerjaan',
                'pekerjaan23_jawaban_checklist_pekerjaan',
                'pekerjaan24_jawaban_checklist_pekerjaan',
                'pekerjaan25_jawaban_checklist_pekerjaan',
                'jawaban1_jawaban_checklist_pekerjaan',
                'jawaban2_jawaban_checklist_pekerjaan',
                'jawaban3_jawaban_checklist_pekerjaan',
                'jawaban4_jawaban_checklist_pekerjaan',
                'jawaban5_jawaban_checklist_pekerjaan',
                'jawaban6_jawaban_checklist_pekerjaan',
                'jawaban7_jawaban_checklist_pekerjaan',
                'jawaban8_jawaban_checklist_pekerjaan',
                'jawaban9_jawaban_checklist_pekerjaan',
                'jawaban10_jawaban_checklist_pekerjaan',
                'jawaban11_jawaban_checklist_pekerjaan',
                'jawaban12_jawaban_checklist_pekerjaan',
                'jawaban13_jawaban_checklist_pekerjaan',
                'jawaban14_jawaban_checklist_pekerjaan',
                'jawaban15_jawaban_checklist_pekerjaan',
                'jawaban16_jawaban_checklist_pekerjaan',
                'jawaban17_jawaban_checklist_pekerjaan',
                'jawaban18_jawaban_checklist_pekerjaan',
                'jawaban19_jawaban_checklist_pekerjaan',
                'jawaban20_jawaban_checklist_pekerjaan',
                'jawaban21_jawaban_checklist_pekerjaan',
                'jawaban22_jawaban_checklist_pekerjaan',
                'jawaban23_jawaban_checklist_pekerjaan',
                'jawaban24_jawaban_checklist_pekerjaan',
                'jawaban25_jawaban_checklist_pekerjaan'
            )
            ->join('objek_kerja as ok', 'cp.id_objek_kerja', 'ok.id_objek_kerja')
            ->leftJoin('jawaban_checklist_pekerjaan as jcp', function ($q) use ($date, $userGroup) {
                $q->on('ok.id_objek_kerja', 'jcp.id_objek_kerja')
                    ->where('jcp.tanggal_jawaban_checklist_pekerjaan', $date)
                    ->where('jcp.id_grup_pengguna', $userGroup);
            })
            ->where('cp.id_grup_pengguna', $userGroup)
            ->where('ok.alamat_objek_kerja', $location)
            ->where(function ($a) use ($date) {
                $a->where('tahun_checklist_pekerjaan', '*')
                    ->orWhere('tahun_checklist_pekerjaan', 'like', '%' . date('w', strtotime($date)) . '%');
            })
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

        $groupMedia = [];
        $jobs = [];
        $status = '0';
        $datas = [];
        $obj = '';
        if ($data) {
            $status = '1';
            $medias = DB::table('media_jawaban')
                ->join('pengguna', 'media_jawaban.user_media_jawaban', 'pengguna.id_pengguna')
                ->where('id_jawaban_checklist_pekerjaan', $data->id_jawaban_checklist_pekerjaan)
                ->get();
            $groupMedia = [];
            foreach ($medias as $media) {
                $groupMedia[$media->id_pekerjaan][] = [
                    'id' => $media->id_media_jawaban,
                    'image' => env('OLD_ASSET_ROOT') . 'uploads/checklist_pekerjaan/' . $media->lokasi_media_jawaban,
                    'user_name' => $media->nama_pengguna,
                ];
            }

            $jobs = DB::table('pekerjaan')->where('status_pekerjaan', '1')->pluck('nama_pekerjaan', 'id_pekerjaan');
        } else {
            $datas = DB::table('checklist_pekerjaan as cp')
                ->join('pekerjaan as p', 'cp.id_pekerjaan', 'p.id_pekerjaan')
                ->where('cp.id_objek_kerja', $idObjekKerja)
                ->where('cp.id_grup_pengguna', $group)
                ->where(function ($q) use ($date) {
                    $q->where('tahun_checklist_pekerjaan', '*')
                        ->orWhere('tahun_checklist_pekerjaan', 'like', '%' . date('w', strtotime($date)) . '%');
                })
                ->where('status_checklist_pekerjaan', '1')
                ->orderBy('cp.urut_checklist_pekerjaan', 'asc')
                ->get();

            $data = DB::table('grup_pengguna')->where('id_grup_pengguna', $group)->first();
            $obj = DB::table('objek_kerja')->where('id_objek_kerja', $id)->first();
        }

        return view('report_ops.laporanChecklist.view', [
            "pageTitle" => "SCA OPS | Laporan Checklist | View",
            'data' => $data,
            'medias' => $groupMedia,
            'jobs' => $jobs,
            'status' => $status,
            'datas' => $datas,
            'obj' => $obj,
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
            ->where(function ($a) use ($date) {
                $a->where('tahun_checklist_pekerjaan', '*')
                    ->orWhere('tahun_checklist_pekerjaan', 'like', '%' . date('w', strtotime($date)) . '%');
            })
            ->get();
        $answers = DB::table('jawaban_checklist_pekerjaan')
            ->whereIn('id_objek_kerja', $pluckId)
            ->where('id_grup_pengguna', $userGroup)
            ->where('tanggal_jawaban_checklist_pekerjaan', $date)->get();

        $pluckIdAnswer = $answers->pluck('id_jawaban_checklist_pekerjaan');
        $media = DB::table('media_jawaban')->select('id_jawaban_checklist_pekerjaan', 'lokasi_media_jawaban', 'id_pekerjaan')
            ->whereIn('id_jawaban_checklist_pekerjaan', $pluckIdAnswer)->get();

        $arrayAns = [];
        foreach ($answers as $a => $ans) {
            for ($i = 1; $i < 26; $i++) {
                if ($ans->{'pekerjaan' . ($i) . '_jawaban_checklist_pekerjaan'}) {
                    $arrayMedia = [];
                    foreach ($media as $me) {
                        if ($ans->{'pekerjaan' . ($i) . '_jawaban_checklist_pekerjaan'} == $me->id_pekerjaan) {
                            $arrayMedia[] = '/var/www/html/uploads/checklist_pekerjaan/' . $me->lokasi_media_jawaban;
                        }
                    }

                    $arrayAns[$ans->id_objek_kerja . '-' . $ans->{'pekerjaan' . ($i) . '_jawaban_checklist_pekerjaan'}] = [
                        'keterangan' => $ans->{'keterangan' . ($i) . '_jawaban_checklist_pekerjaan'},
                        'jawaban' => $ans->{'jawaban' . ($i) . '_jawaban_checklist_pekerjaan'},
                        'media' => $arrayMedia,
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

    public function getViewDataExport(Request $request)
    {
        $date = $request->date;
        $grup = $request->grup;
        $objek = $request->objek;

        $data = DB::table('jawaban_checklist_pekerjaan as jcp')
            ->select('jcp.*', 'nama_grup_pengguna', 'p.nama_pengguna', 'nama_objek_kerja', 'pc.nama_pengguna as nama_pengguna_checker')
            ->join('grup_pengguna as gp', 'jcp.id_grup_pengguna', 'gp.id_grup_pengguna')
            ->join('pengguna as p', 'jcp.user_jawaban_checklist_pekerjaan', 'p.id_pengguna')
            ->leftJoin('pengguna as pc', 'jcp.checker_jawaban_checklist_pekerjaan', 'pc.id_pengguna')
            ->join('objek_kerja as ok', 'jcp.id_objek_kerja', 'ok.id_objek_kerja')
            ->where('jcp.id_objek_kerja', $objek)
            ->where('tanggal_jawaban_checklist_pekerjaan', $date)
            ->where('jcp.id_grup_pengguna', $grup)
            ->first();

        $groupMedia = [];
        $jobs = [];
        $status = '0';
        $datas = [];
        $obj = '';
        if ($data) {
            $medias = DB::table('media_jawaban')
                ->join('pengguna', 'media_jawaban.user_media_jawaban', 'pengguna.id_pengguna')
                ->where('id_jawaban_checklist_pekerjaan', $data->id_jawaban_checklist_pekerjaan)
                ->get();
            $groupMedia = [];
            foreach ($medias as $media) {
                $groupMedia[$media->id_pekerjaan][] = [
                    'id' => $media->id_media_jawaban,
                    'image' => env('OLD_ASSET_ROOT') . 'uploads/checklist_pekerjaan/' . $media->lokasi_media_jawaban,
                    'user_name' => $media->nama_pengguna,
                ];
            }

            $checklist = DB::table('checklist_pekerjaan')->where('id_objek_kerja', $objek)->pluck('id_pekerjaan');
            $jobs = DB::table('pekerjaan')->where('status_pekerjaan', '1')->whereIn('id_pekerjaan', $checklist)
                ->pluck('nama_pekerjaan', 'id_pekerjaan');
        }

        $array = [
            'data' => $data,
            'medias' => $groupMedia,
            'jobs' => $jobs,
        ];

        return Excel::download(new ChecklistExport('report_ops.laporanChecklist.detail-excel', $array), 'laporan checklist pekerjaan.xlsx');
    }

    public function getViewDataPrint(Request $request)
    {
        $date = $request->date;
        $grup = $request->grup;
        $objek = $request->objek;

        $data = DB::table('jawaban_checklist_pekerjaan as jcp')
            ->select('jcp.*', 'nama_grup_pengguna', 'p.nama_pengguna', 'nama_objek_kerja', 'pc.nama_pengguna as nama_pengguna_checker')
            ->join('grup_pengguna as gp', 'jcp.id_grup_pengguna', 'gp.id_grup_pengguna')
            ->join('pengguna as p', 'jcp.user_jawaban_checklist_pekerjaan', 'p.id_pengguna')
            ->leftJoin('pengguna as pc', 'jcp.checker_jawaban_checklist_pekerjaan', 'pc.id_pengguna')
            ->join('objek_kerja as ok', 'jcp.id_objek_kerja', 'ok.id_objek_kerja')
            ->where('jcp.id_objek_kerja', $objek)
            ->where('tanggal_jawaban_checklist_pekerjaan', $date)
            ->where('jcp.id_grup_pengguna', $grup)
            ->first();

        $groupMedia = [];
        $jobs = [];
        $status = '0';
        $datas = [];
        $obj = '';
        if ($data) {
            $medias = DB::table('media_jawaban')
                ->join('pengguna', 'media_jawaban.user_media_jawaban', 'pengguna.id_pengguna')
                ->where('id_jawaban_checklist_pekerjaan', $data->id_jawaban_checklist_pekerjaan)
                ->get();
            $groupMedia = [];
            foreach ($medias as $media) {
                $groupMedia[$media->id_pekerjaan][] = [
                    'id' => $media->id_media_jawaban,
                    'image' => env('OLD_ASSET_ROOT') . 'uploads/checklist_pekerjaan/' . $media->lokasi_media_jawaban,
                    'user_name' => $media->nama_pengguna,
                ];
            }

            $checklist = DB::table('checklist_pekerjaan')->where('id_objek_kerja', $objek)->pluck('id_pekerjaan');
            $jobs = DB::table('pekerjaan')->where('status_pekerjaan', '1')->whereIn('id_pekerjaan', $checklist)
                ->pluck('nama_pekerjaan', 'id_pekerjaan');
        }

        $array = [
            'data' => $data,
            'medias' => $groupMedia,
            'jobs' => $jobs,
        ];

        return view('report_ops.laporanChecklist.detail-excel', $array);
    }

    public function printMonth(Request $request)
    {
        $date = $request->date;
        $grup = $request->grup;
        $objek = $request->objek;

        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $monthName = ['januari', 'Februari', 'Meret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $group = DB::table('grup_pengguna')->where('id_grup_pengguna', $grup)->first();
        $count_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $object = DB::table('objek_kerja')->where('id_objek_kerja', $objek)->first();

        $datas = DB::table('jawaban_checklist_pekerjaan as jcp')
            ->select('jcp.*', 'nama_grup_pengguna', 'p.nama_pengguna', 'nama_objek_kerja', 'pc.nama_pengguna as nama_pengguna_checker')
            ->join('grup_pengguna as gp', 'jcp.id_grup_pengguna', 'gp.id_grup_pengguna')
            ->join('pengguna as p', 'jcp.user_jawaban_checklist_pekerjaan', 'p.id_pengguna')
            ->leftJoin('pengguna as pc', 'jcp.checker_jawaban_checklist_pekerjaan', 'pc.id_pengguna')
            ->join('objek_kerja as ok', 'jcp.id_objek_kerja', 'ok.id_objek_kerja')
            ->where('jcp.id_objek_kerja', $objek)
            ->whereBetween('tanggal_jawaban_checklist_pekerjaan', [
                date('Y-m', strtotime($date)) . '-01',
                date('Y-m', strtotime($date)) . '-' . $count_date,
            ])
            ->where('jcp.id_grup_pengguna', $grup)
            ->get();

        $ar = [];
        foreach ($datas as $data) {
            for ($i = 1; $i <= 25; $i++) {
                if ($data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'}) {
                    $ar[$data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'} . '-' . (int) date('d', strtotime($data->tanggal_jawaban_checklist_pekerjaan))] = $data->{'jawaban' . $i . '_jawaban_checklist_pekerjaan'};
                }
            }
        }

        $checklist = DB::table('checklist_pekerjaan')->where('id_objek_kerja', $objek)->where('id_grup_pengguna', $grup)->pluck('id_pekerjaan');
        $jobs = DB::table('pekerjaan')->where('status_pekerjaan', '1')->whereIn('id_pekerjaan', $checklist)
            ->pluck('nama_pekerjaan', 'id_pekerjaan');
        $array = [
            'month' => $monthName[(int) $month - 1],
            'year' => $year,
            'count_date' => $count_date,
            'group' => $group,
            'jobs' => $jobs,
            'object' => $object,
            'answers' => $ar,
        ];

        return view('report_ops.laporanChecklist.print_month', $array);
    }

    public function tes(Request $request)
    {
        $objek = $request->objek;
        $grup = $request->grup;
        $arrayIdObjectKerja = [8, 71, 72, 7, 69, 70, 80, 77, 78, 79, 82, 75, 76, 83, 74, 73, 85, 86, 5, 84, 81, 150, 151,
            152, 154, 155, 156, 92, 165, 93, 97, 98, 94, 95, 96, 6, 146, 147, 4, 19, 18, 3, 12, 13, 9, 11, 10,
        ];
        $datas = JawabanChecklistPekerjaan::select('jawaban_checklist_pekerjaan.*', 'nama_objek_kerja', 'nama_grup_pengguna')
            ->join('objek_kerja', 'jawaban_checklist_pekerjaan.id_objek_kerja', 'objek_kerja.id_objek_kerja')
            ->join('grup_pengguna', 'jawaban_checklist_pekerjaan.id_grup_pengguna', 'grup_pengguna.id_grup_pengguna')
            ->whereIn('jawaban_checklist_pekerjaan.id_objek_kerja', $arrayIdObjectKerja)
            ->whereBetween('tanggal_jawaban_checklist_pekerjaan', ['2024-06-08', '2024-07-18'])
            ->where('checker_jawaban_checklist_pekerjaan', null)
            ->get();

        $array = [];
        foreach ($datas as $data) {
            $detail['id_jawaban_checklist_pekerjaan'] = $data->id_jawaban_checklist_pekerjaan;
            $detail['id_objek_kerja'] = $data->id_objek_kerja;
            $detail['id_grup_pengguna'] = $data->id_grup_pengguna;
            $detail['nama_objek_kerja'] = $data->nama_objek_kerja;
            $detail['nama_grup_pengguna'] = $data->nama_grup_pengguna;
            $detail['tanggal_jawaban_checklist_pekerjaan'] = $data->tanggal_jawaban_checklist_pekerjaan;
            $detail['checker_jawaban_checklist_pekerjaan'] = $data->checker_jawaban_checklist_pekerjaan;
            for ($i = 1; $i <= 25; $i++) {
                if ($data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'} && $data->{'jawaban' . $i . '_jawaban_checklist_pekerjaan'} == '1') {
                    $detail['pekerjaan' . $i . '_jawaban_checklist_pekerjaan'] = $data->{'pekerjaan' . $i . '_jawaban_checklist_pekerjaan'};
                } else {
                    break;
                }

            }
            $array[] = $detail;
        }

        return $array;
    }
}
