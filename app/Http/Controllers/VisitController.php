<?php

namespace App\Http\Controllers;

use App\Models\Master\Pelanggan;
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

        $idGrupUser = session()->get('user')['id_grup_pengguna'];
        $sales = Salesman::where('pengguna_id', session()->get('user')['id_pengguna'])->first();

        if ($request->ajax()) {
            $data = Visit::select('visit.*', 'salesman.nama_salesman', 'pelanggan.nama_pelanggan')
                ->leftJoin('salesman', 'visit.id_salesman', 'salesman.id_salesman')
                ->leftJoin('pelanggan', 'visit.id_pelanggan', 'pelanggan.id_pelanggan')
                ->where('visit.status', '!=', 3);
            if ($request->id_cabang) {
                $data = $data->where('visit.id_cabang', $request->id_cabang);
            }

            if ($request->id_salesman) {
                $data = $data->where('visit.id_salesman', $request->id_salesman);
            }

            if ($request->daterangepicker) {
                $explode = explode(' - ', $request->daterangepicker);
                $data = $data->whereBetween('visit_date', $explode);
            }

            if (isset($request->status)) {
                $data = $data->where('visit.status', $request->status);
            }

            if ($request->status_pelanggan) {
                $data = $data->where('visit.status_pelanggan', $request->status_pelanggan);
            }

            $data = $data->orderBy('visit_date', 'desc')->orderBy('visit_code', 'desc');
            return DataTables::of($data)
                ->addColumn('action', function ($data) use ($idGrupUser) {
                    $btn = '';
                    if (checkAccessMenu('marketing-tool/visit', 'show')) {
                        $btn .= '<a href="' . route('visit-view', $data->id) . '" class="btn btn-info btn-sm mr-1"><i class="fa fa-file-text"></i></a>';
                    }

                    if (checkAccessMenu('marketing-tool/visit', 'edit')) {
                        $btn .= '<a href="' . route('visit-entry', $data->id) . '" class="btn btn-warning btn-sm mr-1"><i class="glyphicon glyphicon-pencil"></i></a>';
                    }

                    if (checkAccessMenu('marketing-tool/visit', 'delete')) {
                        if ($idGrupUser == 1) {
                            $btn .= '<a href="' . route('visit-delete', $data->id) . '" class="action-delete btn btn-danger btn-sm"><i class="glyphicon glyphicon-trash"></i></a>';
                        }
                    }

                    return $btn;
                })
                ->editColumn('status', function ($data) {
                    switch ($data->status) {
                        case '0':
                            return "<label class='label label-danger'>BATAL VISIT</label>";
                            break;
                        case '1':
                            return "<label class='label label-warning'>BELUM VISIT</label>";
                            break;
                        case '2':
                            $html = '';
                            if ($data->visit_type == 'LOKASI') {
                                $html .= "<label class='label label-primary'>SUDAH VISIT KE " . $data->visit_type . "</label>";
                            } else {
                                $html .= "<label class='label label-success'>SUDAH VISIT VIA " . $data->visit_type . "</label>";
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
            'groupUser' => $idGrupUser,
            'idUser' => $sales ? $sales->id_salesman : '0',
        ]);
    }

    public function viewData($id)
    {
        if (checkAccessMenu('marketing-tool/visit', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = Visit::find($id);
        if (!$data) {
            $data = '';
            if ($id != 0) {
                return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
            }
        }

        $cabang = DB::table('cabang')
            ->select('id_cabang as id', DB::raw('concat(kode_cabang," - ",nama_cabang) as text'))
            ->where('status_cabang', '1')
            ->get();
        $progress = Visit::$progressIndicator;
        $methods = Visit::$visitMethod;
        $categories = DB::table('kategori_kunjungan')->where('status_kategori_kunjungan', '1')->get();
        $salesman = Salesman::where('pengguna_id', session()->get('user')->id_pengguna)->first();
        $listStatus = Visit::$listStatus;
        return view('ops.visit.view', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Kunjungan | Lihat",
            'salesman' => $salesman,
            'data' => $data,
            'progress' => $progress,
            'categories' => $categories,
            'methods' => $methods,
            'listStatus' => $listStatus,
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('marketing-tool/visit', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = Visit::find($id);
        if (!$data) {
            $data = '';
            if ($id != 0) {
                return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
            }
        }

        $cabang = DB::table('cabang')
            ->select('id_cabang as id', DB::raw('concat(kode_cabang," - ",nama_cabang) as text'))
            ->where('status_cabang', '1')
            ->get();
        $progress = Visit::$progressIndicator;
        $methods = Visit::$visitMethod;
        $categories = DB::table('kategori_kunjungan')->where('status_kategori_kunjungan', '1')->get();
        $salesman = Salesman::where('pengguna_id', session()->get('user')->id_pengguna)->first();
        $listStatus = Visit::$listStatus;
        return view('ops.visit.form', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Kunjungan | " . ($data ? 'Edit' : 'Tambah'),
            'salesman' => $salesman,
            'data' => $data,
            'progress' => $progress,
            'categories' => $categories,
            'methods' => $methods,
            'listStatus' => $listStatus,
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
        if (checkAccessMenu('marketing-tool/visit', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

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

            if ($id == 0) {
                $data->fill($request->all());
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
                $data->id_pelanggan = $request->id_pelanggan;
                $data->pre_visit_desc = $request->pre_visit_desc;
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
            return response()->json(["result" => true, 'redirect' => route('visit-entry', $id), "message" => 'Kunjungan berhasil dibatalkan'], 200);
        } catch (\Exception $th) {
            DB::rollback();
            Log::error($th);
            return response()->json(["result" => false, "message" => 'Kunjungan gagal dibatalkan'], 500);
        }
    }

    public function saveReportEntry(Request $request, $id)
    {
        if (checkAccessMenu('marketing-tool/visit', 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = Visit::find($id);
        if (!$data) {
            return response()->json(['result' => false, 'message' => 'Data kunjungan tidak ditemukan'], 500);
        }

        DB::beginTransaction();
        try {
            $data->fill($request->all());
            $data->status = 2;
            if (isset($request->progress_ind)) {
                $data->progress_ind = implode(', ', $request->progress_ind);
            }

            $data->save();

            if (isset($request->remove_base64)) {
                $decodeRemoveMedia = json_decode($request->remove_base64);
                $removeFile = $data->removefile($decodeRemoveMedia);
                if (!$removeFile['result']) {
                    DB::rollback();
                    return response()->json(['result' => false, 'message' => 'Hapus file bermasalah'], 500);
                }
            }

            if (isset($request->upload_base64)) {
                $decodeMedia = json_decode($request->upload_base64);
                $uploadFile = $data->uploadfile($decodeMedia);
                if (!$uploadFile['result']) {
                    DB::rollback();
                    return response()->json(['result' => false, 'message' => 'Upload file bermasalah'], 500);
                }
            }

            DB::commit();
            return response()->json(["result" => true, 'redirect' => route('visit-entry', $id), "message" => 'Hasil kunjungan berhasil disimpan'], 200);
        } catch (\Exception $th) {
            DB::rollback();
            Log::error($th);
            return response()->json(["result" => false, "message" => 'Hasil kunjungan gagal disimpan'], 500);
        }
    }

    public function findCustomer($customerid)
    {
        $data = Pelanggan::select(
            'nama_pelanggan',
            'alamat_pelanggan',
            'telepon1_pelanggan',
            'kontak_person_pelanggan',
            'kota_pelanggan',
            'bidang_usaha_pelanggan',
            'kapasitas_pelanggan',
            'posisi_kontak_person_pelanggan',
            'aset_pelanggan',
            'status_aktif_pelanggan',
            'keterangan_pelanggan'
        )
            ->where('id_pelanggan', $customerid)->where('status_pelanggan', '1')->first();

        return $data;
    }

    public function saveCustomer(Request $request, $id = 0, $customerid = 0)
    {
        $check = Pelanggan::where('nama_pelanggan', $request->nama_pelanggan)
            ->where('id_pelanggan', '!=', $customerid)
            ->first();
        if ($check) {
            return response()->json(['result' => false, 'message' => 'Nama pelanggan sudah ada'], 500);
        }

        DB::beginTransaction();
        try {
            $data = Pelanggan::find($customerid);
            if (!$data) {
                $data = new Pelanggan;
                $data->id_wilayah_pelanggan = '1';
                $data->id_kategori_pelanggan = '1';
                $data->id_gudang = 1;
                $data->status_pelanggan = '1';
                $data->plafon_hari_pelanggan = '0';
                $data->user_pelanggan = session()->get('user')['id_pengguna'];
                $data->plafon_pelanggan = 10000000;
                $data->plafon_hari_pelanggan = 1;
            }

            $data->nama_pelanggan = $request->nama_pelanggan;
            $data->alamat_pelanggan = $request->alamat_pelanggan;
            $data->kota_pelanggan = $request->kota_pelanggan;
            $data->telepon1_pelanggan = $request->telepon1_pelanggan;
            $data->kontak_person_pelanggan = $request->kontak_person_pelanggan;
            $data->bidang_usaha_pelanggan = $request->bidang_usaha_pelanggan;
            $data->kapasitas_pelanggan = $request->kapasitas_pelanggan;
            $data->posisi_kontak_person_pelanggan = $request->posisi_kontak_person_pelanggan;
            $data->aset_pelanggan = $request->aset_pelanggan;
            $data->status_aktif_pelanggan = $request->status_aktif_pelanggan;
            $data->keterangan_pelanggan = $request->keterangan_pelanggan;
            $data->save();

            DB::commit();
            return response()->json([
                'result' => true,
                'message' => 'Pelanggan berhasil disimpan',
                'redirect' => route('visit-entry', $id),
            ], 200);
        } catch (\Exception $th) {
            DB::rollback();
            Log::error($th);
            return response()->json(["result" => false, "message" => 'Pelanggan gagal disimpan'], 500);
        }
    }

    public function saveDateChange(Request $request, $id)
    {
        $data = Visit::find($id);
        if (!$data) {
            return response()->json(['result' => false, 'message' => 'Data kunjungan tidak ditemukan'], 500);
        }

        DB::beginTransaction();
        try {
            $data->alasan_ubah_tanggal = $request->alasan_ubah_tanggal . ', tanggal sebelumnya ' . $data->visit_date;
            $data->visit_date = $request->new_date;
            $data->save();

            DB::commit();
            return response()->json([
                'result' => true,
                'message' => 'Perubahan tanggal berhasil disimpan',
                'redirect' => route('visit-entry', $id),
            ], 200);
        } catch (\Exception $th) {
            DB::rollback();
            Log::error($th);
            return response()->json(["result" => false, "message" => 'Perubahan tanggal gagal disimpan'], 500);
        }
    }

    public function removeEntry($id)
    {
        if (checkAccessMenu('marketing-tool/visit', 'delete') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = Visit::find($id);
        if (!$data) {
            return response()->json(['result' => false, 'message' => 'Kunjungan tidak ditemukan'], 500);
        }

        DB::beginTransaction();
        try {
            foreach ($data->medias as $media) {
                unlink(public_path($media->image));
                $media->delete();
            }

            $data->delete();
            DB::commit();
            return response()->json(['result' => true, 'message' => 'Kunjungan berhasil dihapus', 'redirect' => route('visit')], 200);
        } catch (\Exception $th) {
            DB::rollback();
            Log::error($th);
            return response()->json(["result" => false, "message" => 'Kunjungan gagal dihapus'], 500);
        }
    }
}
