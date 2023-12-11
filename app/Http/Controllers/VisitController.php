<?php

namespace App\Http\Controllers;

use App\Models\Master\Setting;
use App\Penjualan;
use App\Salesman;
use App\Visit;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\Facades\DataTables;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'marketing-tool/visit', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = Visit::select('visit.*', 'salesman.nama_salesman', 'pelanggan.nama_pelanggan')
                ->leftJoin('salesman', 'visit.id_salesman', 'salesman.id_salesman')
                ->leftJoin('pelanggan', 'visit.id_pelanggan', 'pelanggan.id_pelanggan')
                ->where('visit.status', '!=', 3);
            if ($request->id_cabang) {
                $data = $data->where('id_cabang', $request->id_cabang);
            }

            if ($request->id_salesman) {
                $data = $data->where('id_salesman', $request->id_salesman);
            }

            if ($request->daterangepicker) {
                $explode = explode(' - ', $request->daterangepicker);
                $data = $data->whereBetween('visit_date', $explode);
            }

            if ($request->status) {
                $data = $data->where('status', $request->status);
            }

            if ($request->status_pelanggan) {
                $data = $data->where('status_pelanggan', $request->status_pelanggan);
            }

            $data = $data->orderBy('created_at', 'desc');

            $idUser = session()->get('user')['id_pengguna'];
            $idGrupUser = session()->get('user')['id_grup_pengguna'];

            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $btn = '<div class="dropdown">
                    <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-bars"></i>
                    <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<li><a href="' . route('visit-view', $data->id) . '"><i class="fa fa-eye"></i> Lihat</a></li>';
                    if ($data->status != 0) {
                        $btn .= '<li><a href="' . route('visit-report-entry', $data->id) . '"><i class="fa fa-truck"></i> Kunjungan</a></li>';
                        $btn .= '<li><a href="' . route('visit-entry', $data->id) . '"><i class="fa fa-pencil"></i> Edit</a></li>';
                    }

                    // $btn .= '<li><a href="#"><i class="fa fa-trash"></i> Hapus</a></li>';

                    $btn .= '</ul></div>';
                    return $btn;
                })
                ->editColumn('status', function ($data) {
                    switch ($data->status) {
                        case '0':
                            return "<label class='label label-danger'>Batal Visit</label>";
                            break;
                        case '1':
                            return "<label class='label label-warning'>Belum Visit</label>";
                            break;
                        case '2':
                            $html = '';
                            if ($data->visit_type == 'LOKASI') {
                                $html .= "<label class='label label-primary'>Sudah Visit " . $data->visit_type . "</label>";
                            } else {
                                $html .= "<label class='label label-success'>Sudah Visit " . $data->visit_type . "</label>";
                            }
                            return $html;
                            break;
                        default:
                            return '';
                            break;
                    }
                })

                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        $salesman = Salesman::select('id_salesman as id', 'nama_salesman as text')->where('status_salesman', '1')->get();
        $customerCategory = Visit::$kategoriPelanggan;
        $cabang = session()->get('access_cabang');
        return view('ops.visit.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Kunjungan | List",
            'salesmans' => $salesman,
            'customerCategory' => $customerCategory,
        ]);
    }

    public function entry($id = 0)
    {
        $data = Visit::find($id);
        if (!$data) {
            $data = '';
            if ($id != 0) {
                return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
            }
        }

        $cabang = DB::table('cabang')->select('id_cabang as id', DB::raw('concat(kode_cabang," - ",nama_cabang) as text'))->where('status_cabang', '1')->get();
        $salesman = Salesman::where('pengguna_id', session()->get('user')->id_pengguna)->first();
        return view('ops.visit.form', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Kunjungan | " . ($data ? 'Edit' : 'Tambah'),
            'salesman' => $salesman,
            'data' => $data,
        ]);
    }

    public function getCustomer(Request $request)
    {
        $datas = DB::table('pelanggan')
            ->select('id_pelanggan as id', 'nama_pelanggan as text', 'alamat_pelanggan')
            ->where(function ($w) use ($request) {
                $w->where('nama_pelanggan', 'like', '%' . $request->search . '%')
                    ->orWhere('alamat_pelanggan', 'like', '%' . $request->search . '%');
            })
            ->where('status_pelanggan', '1')
            ->limit(20)->get();
        return $datas;
    }

    public function saveEntry(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Visit::find($id);
            if (!$data) {
                if ($id != 0) {
                    DB::rollback();
                    return response()->json([
                        'result' => false,
                        'message' => 'Kunjungan tidak ditemukan',
                    ], 500);
                }

                $data = new Visit;
            }

            $data->fill($request->all());
            if ($id == 0) {
                $data->status = '1';
                $data->visit_code = Visit::createcode($request->id_cabang);
                $data->user_created = session()->get('user')['id_pengguna'];

                $checkCustomer = Penjualan::where('id_pelanggan', $request->id_pelanggan)->orderBy('tanggal_penjualan', 'DESC')->first();
                if ($checkCustomer) {
                    $maxTanggalPenjualan = Setting::where('code', 'Treshold Customer Old')->where('id_cabang', $request->id_cabang)->first();

                    $this_month = Carbon::now();
                    $start_month = Carbon::parse($checkCustomer->tanggal_penjualan);
                    $diff = $start_month->diffInMonths($this_month);
                    if ($diff >= $maxTanggalPenjualan->value2) {
                        $data->status_pelanggan = 'OLD CUSTOMER';
                    } else {
                        $data->status_pelanggan = 'EXISTING CUSTOMER';
                    }
                } else {
                    $data->status_pelanggan = 'NEW CUSTOMER';
                }
            } else {
                $data->user_modified = session()->get('user')['id_pengguna'];
            }

            $data->save();
            DB::commit();
            return response()->json([
                'result' => true,
                'message' => 'Kunjungan berhasil disimpan',
                'redirect' => route('visit-entry', $data->id),
            ], 200);
        } catch (\Exception $th) {
            DB::rollback();
            Log::error($th);
            return response()->json(['result' => false, 'message' => 'Kunjungan gagal disimpan'], 500);
        }
    }

    public function viewData($id)
    {
        $data = Visit::find($id);
        if (!$data) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        return view('ops.visit.detail', [
            "pageTitle" => "SCA OPS | Kunjungan | Lihat",
            'data' => $data,
        ]);
    }

    public function reportEntry($id)
    {
        $data = Visit::find($id);
        if (!$data) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $progress = Visit::$progressIndicator;
        $methods = Visit::$visitMethod;

        return view('ops.visit.form-report', [
            "pageTitle" => "SCA OPS | Kunjungan | Input Laporan",
            'data' => $data,
            'progress' => $progress,
            'methods' => $methods,
        ]);
    }

    public function cancelVisit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Visit::find($id);
            if (!$data) {
                DB::rollback();
                return response()->json(['result' => false, 'message' => 'Data kunjungan tidak ditemukan'], 500);
            }

            if ($data->status == 2) {
                DB::rollback();
                return response()->json(['result' => false, 'message' => 'Laporan hasil kunjungan sudah terisi'], 500);
            }

            $data->alasan_pembatalan = $request->alasan_pembatalan;
            $data->status = 0;
            $data->save();
            DB::commit();
            return response()->json(["result" => true, 'redirect' => route('visit-view', $id), "message" => 'Kunjungan berhasil dibatalkan'], 200);
        } catch (\Exception $th) {
            DB::rollback();
            Log::error($th);
            return response()->json(["result" => false, "message" => 'Kunjungan gagal dibatalkan'], 500);
        }
    }

    public function saveReportEntry(Request $request, $id)
    {
        return $request->all();
    }

    public function saveCustomer(Request $request, $id = 0)
    {
        $data = Pelanggan::find($id);
        if (!$data) {
            $data = new Pelanggan;
        }

        $data->nama_pelanggan = $request->nama_pelanggan;
        $data->alamat_pelanggan = $request->alamat_pelanggan;
        $data->kota_pelanggan = $request->kota_pelanggan;
        $data->telepon1_pelanggan = $request->telepon1_pelanggan;
        $data->kotak_person_pelanggan = $request->kotan_person_pelanggan;
        $data->save();

        return $request->all();
    }
}
