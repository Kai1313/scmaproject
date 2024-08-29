<?php

namespace App\Http\Controllers;

use App\Models\SuratJalan;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class SuratJalanController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'surat_jalan_umum', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('surat_jalan')->where('jenis', 'surat_jalan_umum');
            $data = $data->orderBy('tanggal', 'desc')->orderBy('no_surat_jalan', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn .= '<a href="' . route('surat_jalan_umum-view', $row->id) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a>';
                    $btn .= '<a href="' . route('surat_jalan_umum-entry', $row->id) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a>';
                    $btn .= '<a href="' . route('surat_jalan_umum-delete', $row->id) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a>';
                    return $btn;
                })
                ->editColumn('status', function ($row) {
                    $array = [
                        '0' => '<label class="label label-default">Batal</label>',
                        '1' => '<label class="label label-primary">Aktif</label>',
                    ];

                    return isset($array[$row->status]) ? $array[$row->status] : '';
                })
                ->rawColumns(['action', 'status'])
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
        return view('ops.SuratJalan.form', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Surat Jalan Umum | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = SuratJalan::find($id);
        if (!$data) {
            $data = new SuratJalan;
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
        if (checkAccessMenu('kirim_ke_cabang', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MoveBranch::where('type', 0)->where('id_pindah_barang', $id)->first();
        $groupPengguna = DB::table('pengguna')->select(DB::raw('distinct(id_grup_pengguna)'))->where('id_pengguna', $data->user_created)->orWhere('id_grup_pengguna', 1)->get()->toArray();
        $groups = [];
        foreach ($groupPengguna as $grup) {
            $groups[] = $grup->id_grup_pengguna;
        }

        return view('ops.sendToBranch.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Kirim Ke Cabang | Lihat",
            'isEdit' => in_array(session()->get('user')['id_grup_pengguna'], $groups),
        ]);
    }

    public function destroy($id)
    {
        if (checkAccessMenu('kirim_ke_cabang', 'delete') == false) {
            return response()->json([
                "result" => false,
                "message" => "Tidak mendapatkan akses halaman",
            ], 500);
        }

        $data = MoveBranch::find($id);
        if (!$data) {
            return response()->json([
                "result" => false,
                "message" => "Data tidak ditemukan",
            ], 500);
        }

        $period = $this->checkPeriod($data->tanggal_pindah_barang);
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
                "redirect" => route('send_to_branch'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void pindah barang");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal dibatalkan",
            ], 500);
        }
    }

    public function printData($id)
    {
        if (checkAccessMenu('kirim_ke_cabang', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $dataSatuan = DB::table('isi_satuan_barang')->select(DB::raw('distinct(isi_satuan_barang.id_satuan_barang)'), 'id_barang', 'nama_satuan_barang')
            ->leftJoin('satuan_barang', 'isi_satuan_barang.id_satuan_barang', 'satuan_barang.id_satuan_barang')
            ->where('satuan_wadah_isi_satuan_barang', '1')->get();
        $arraySatuan = [];
        foreach ($dataSatuan as $satuan) {
            $arraySatuan[$satuan->id_barang] = $satuan->nama_satuan_barang;
        }

        $data = MoveBranch::where('id_jenis_transaksi', 21)->where('id_pindah_barang', $id)->first();
        if (!$data) {
            return 'data tidak ditemukan';
        }

        $pdf = PDF::loadView('ops.sendToBranch.print', ['data' => $data, 'arraySatuan' => $arraySatuan]);
        $pdf->setPaper('a5', 'landscape');
        return $pdf->stream('Surat jalan pindah cabang ' . $data->kode_pindah_barang . '.pdf');
    }
}
