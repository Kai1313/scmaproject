<?php

namespace App\Http\Controllers;

use App\Exports\SlipsExport;
use App\Models\Accounting\JurnalHeader;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use App\Models\Master\Slip;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterSlipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (checkUserSession($request, 'master_slip', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_slip = Slip::join('master_akun', 'master_slip.id_akun', '=', 'master_akun.id_akun')->select('master_slip.*', 'master_akun.nama_akun')->get();
        $cabang = Cabang::find(1);
        $data_cabang = Cabang::get();
        $user_id = $request->user_id;

        // if (($user_id != '' && $request->session()->has('token') == false) || $request->session()->has('token') == true) {
        //     if ($request->session()->has('token') == true) {
        //         $user_id = $request->session()->get('user')->id_pengguna;
        //     }
        //     $user = User::where('id_pengguna', $user_id)->first();
        //     $token = UserToken::where('id_pengguna', $user_id)->where('status_token_pengguna', 1)->whereRaw("waktu_habis_token_pengguna > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", Carbon::now()->format('Y-m-d H:i:s'))->first();

        //     $sql = "SELECT
        //         a.id_pengguna,
        //         a.id_grup_pengguna,
        //         d.id_menu,
        //         d.nama_menu,
        //         c.lihat_akses_menu,
        //         c.tambah_akses_menu,
        //         c.ubah_akses_menu,
        //         c.hapus_akses_menu,
        //         c.cetak_akses_menu
        //     FROM
        //         pengguna a,
        //         grup_pengguna b,
        //         akses_menu c,
        //         menu d
        //     WHERE
        //         a.id_grup_pengguna = b.id_grup_pengguna
        //         AND b.id_grup_pengguna = c.id_grup_pengguna
        //         AND c.id_menu = d.id_menu
        //         AND a.id_pengguna = $user_id
        //         AND d.keterangan_menu = 'Accounting'
        //         AND d.status_menu = 1";
        //     $access = DB::connection('mysql')->select($sql);

        //     $user_access = array();
        //     foreach ($access as $value) {
        //         $user_access[$value->nama_menu] = ['show' => $value->lihat_akses_menu, 'create' => $value->tambah_akses_menu, 'edit' => $value->ubah_akses_menu, 'delete' => $value->hapus_akses_menu, 'print' => $value->cetak_akses_menu];
        //     }

        //     if ($token && $request->session()->has('token') == false) {
        //         $request->session()->put('token', $token->nama_token_pengguna);
        //         $request->session()->put('user', $user);
        //         $request->session()->put('access', $user_access);
        //     } else if ($request->session()->has('token')) {
        //     } else {
        //         $request->session()->flush();
        //     }

        //     $session = $request->session()->get('access');

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | List",
            "cabang" => $cabang,
            "data_cabang" => $data_cabang,
            "data_slip" => $data_slip,
        ];

        // if (($request->session()->has('token') && array_key_exists('Master Slip', $session)) && $session['Master Slip']['show'] == 1) {
        return view('accounting.master.slip.index', $data);
        // } else {
        // return view('exceptions.forbidden');
        // }
        // } else {
        //     $request->session()->flush();
        //     return view('exceptions.forbidden');
        // }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (checkAccessMenu('master_slip', 'create') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $cabang = Cabang::find(1);
        $data_akun = Akun::where("isshown", 1)->where("id_cabang", $cabang->id_cabang)->get(); //DB::select('select * from master_akun');
        $data_cabang = DB::select('select * from cabang where status_cabang = 1');

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | Create",
            "data_akun" => $data_akun,
            "data_cabang" => $data_cabang,
        ];

        // $session = $request->session()->get('access');

        // if (($request->session()->has('token') && array_key_exists('Master Slip', $session)) && $session['Master Slip']['create'] == 1) {
        return view('accounting.master.slip.form', $data);
        // } else {
        //     return view('exceptions.forbidden');
        // }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            //code...
            DB::beginTransaction();
            $request->validate([
                'kode_slip' => 'required',
                'nama_slip' => 'required',
                'jenis_slip' => 'required',
                'id_akun' => 'required',
                'cabang_input' => 'required',
            ]);

            $new_slip = new Slip();
            $new_slip->id_cabang = $request->cabang_input;
            $new_slip->kode_slip = $request->kode_slip;
            $new_slip->nama_slip = $request->nama_slip;
            $new_slip->jenis_slip = $request->jenis_slip;
            $new_slip->id_akun = $request->id_akun;

            if (!$new_slip->save()) {
                DB::rollback();
                Log::error("Failed when saving data slip");
                return response()->json([
                    "result" => false,
                    "message" => "Error when saving data slip",
                ]);
            }

            DB::commit();

            $data = [
                'result' => true,
                'message' => 'Success save ' . $request->nama_slip,
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed when saving data slip " . $e);
            $data = [
                'result' => false,
                'message' => 'Failed when saving data slip, contact developer',
            ];

            return response()->json($data);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id = null)
    {
        if (checkAccessMenu('master_slip', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_slip = Slip::join('master_akun', 'master_slip.id_akun', 'master_akun.id_akun')
            ->join('cabang', 'cabang.id_cabang', 'master_slip.id_cabang')
            ->where('id_slip', $id)
            ->select('master_slip.*', 'master_akun.nama_akun', 'cabang.kode_cabang', 'cabang.nama_cabang')->first();
        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | List",
            "data_slip" => $data_slip,
        ];
        // $session = $request->session()->get('access');

        // if (($request->session()->has('token') && array_key_exists('Master Slip', $session)) && $session['Master Slip']['show'] == 1) {
        return view('accounting.master.slip.detail', $data);
        // } else {
        //     return view('exceptions.forbidden');
        // }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id = null)
    {
        if (checkAccessMenu('terima_dari_cabang', 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_akun = DB::select('select * from master_akun');
        $data_slip = Slip::join('master_akun', 'master_slip.id_akun', 'master_akun.id_akun')->where('id_slip', $id)->select('master_slip.*', 'master_akun.nama_akun')->first();
        $data_cabang = DB::select('select * from cabang where status_cabang = 1');
        // $session = $request->session()->get('access');

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | Create",
            "data_akun" => $data_akun,
            "data_slip" => $data_slip,
            "data_cabang" => $data_cabang,
        ];

        // if (($request->session()->has('token') && array_key_exists('Master Slip', $session)) && $session['Master Slip']['edit'] == 1) {
        return view('accounting.master.slip.form', $data);
        // } else {
        //     return view('exceptions.forbidden');
        // }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            //code...
            DB::beginTransaction();
            $request->validate([
                'kode_slip' => 'required',
                'nama_slip' => 'required',
                'jenis_slip' => 'required',
                'id_akun' => 'required',
                'cabang_input' => 'required',
            ]);

            $new_slip = Slip::find($request->id_slip);
            if ($new_slip) {
                $new_slip->id_cabang = $request->cabang_input;
                $new_slip->kode_slip = $request->kode_slip;
                $new_slip->nama_slip = $request->nama_slip;
                $new_slip->jenis_slip = $request->jenis_slip;
                $new_slip->id_akun = $request->id_akun;

                if (!$new_slip->save()) {
                    DB::rollback();
                    Log::error("Failed when saving data slip");
                    return response()->json([
                        "result" => false,
                        "message" => "Error when saving data slip",
                    ]);
                }

                DB::commit();

                $data = [
                    'result' => true,
                    'message' => 'Success save ' . $request->nama_slip,
                ];
            } else {
                DB::rollback();
                $data = [
                    'result' => false,
                    'message' => "Can't find slip " . $request->id_slip,
                ];
            }

            return response()->json($data);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed when saving data slip " . $e);
            $data = [
                'result' => false,
                'message' => 'Failed when saving data slip, contact developer',
            ];

            return response()->json($data);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $data_journal_header = JurnalHeader::where('id_slip', $id)->get();
        $data_slip = Slip::find($id);
        $kode_slip = $data_slip->kode_slip;
        if (checkAccessMenu('master_slip', 'delete') == false) {
            return response()->json([
                "result" => false,
                "message" => "Maaf, tidak bisa menghapus slip dengan kode slip " . $kode_slip . ", anda tidak punya akses!",
            ]);
        }

        // $session = $request->session()->get('access');
        // if (($request->session()->has('token') && array_key_exists('Master Slip', $session)) && $session['Master Slip']['delete'] == 1) {
        if ($data_journal_header->isNotEmpty()) {
            // return back()->with("failed", "Maaf, tidak bisa menghapus slip" . $data_slip->kode_slip . "karena sudah digunakan pada jurnal");
            return response()->json([
                "result" => false,
                "message" => "Maaf, tidak bisa menghapus slip dengan kode slip " . $kode_slip . ", karena sudah digunakan pada jurnal",
            ]);
        }

        Slip::find($id)->delete();
        // return back()->with("success", "Berhasil menghapus slip " .  $data_slip->kode_slip);
        return response()->json([
            "result" => true,
            "message" => "Berhasil menghapus slip dengan kode slip " . $kode_slip,
        ]);
        // } else {
        // return response()->json([
        //     "result" => false,
        //     "message" => "Maaf, tidak bisa menghapus slip dengan kode slip " . $kode_slip . ", anda tidak punya akses!",
        // ]);
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function populate(Request $request)
    {
        $cabang = $request->cabang;
        // dd($cabang);
        $offset = $request->start;
        $limit = $request->length;
        $keyword = $request->search['value'];
        $sort = [];

        foreach ($request->order as $key => $order) {
            $columnIdx = $order['column'];
            $sortDir = $order['dir'];
            $sort[] = [
                'column' => $request->columns[$columnIdx]['name'],
                'dir' => $sortDir,
            ];
        }

        $draw = $request->draw;
        $current_page = $offset / $limit + 1;

        $data_slip = Slip::join('master_akun', 'master_akun.id_akun', 'master_slip.id_akun')
            ->select('master_slip.*', 'master_akun.nama_akun', DB::raw('
                    (CASE
                        WHEN jenis_slip = 0 THEN "Kas"
                        WHEN jenis_slip = 1 THEN "Bank"
                        WHEN jenis_slip = 2 THEN "Piutang Giro"
                        WHEN jenis_slip = 3 THEN "Hutang Giro"
                    END) as jenis_name
                '));

        $data_slip_table = DB::table(DB::raw('(' . $data_slip->toSql() . ') as master_slip'));
        $data_slip_table = $data_slip_table->where('id_cabang', $cabang);

        if (!empty($keyword)) {
            $data_slip_table->where(function ($query) use ($keyword) {
                $query->orWhere('kode_slip', 'LIKE', "%$keyword%")
                    ->orWhere('nama_slip', 'LIKE', "%$keyword%")
                    ->orWhere('jenis_name', 'LIKE', "%$keyword%")
                    ->orWhere('nama_akun', 'LIKE', "%$keyword%");
            });
        }

        $filtered_data = $data_slip_table->get();

        if ($sort) {
            if (!is_array($sort)) {
                $message = "Invalid array for parameter sort";
                $data = [
                    'result' => false,
                    'message' => $message,
                ];
                return response()->json($data);
            }

            foreach ($sort as $key => $s) {
                $column = $s['column'];
                $directon = $s['dir'];
                $data_slip_table->orderBy($column, $directon);
            }
        } else {
            $data_slip_table->orderBy('kode_slip', 'ASC');
        }

        // pagination
        if ($current_page) {
            $page = $current_page;
            $limit_data = $data_slip_table->count();

            if ($limit) {
                $limit_data = $limit;
            }

            $offset = ($page - 1) * $limit_data;
            if ($offset < 0) {
                $offset = 0;
            }

            Log::debug($offset);
            Log::debug($limit_data);

            $data_slip_table->skip($offset)->take($limit_data);
        }

        $table['draw'] = $draw;
        $table['recordsTotal'] = $data_slip_table->count();
        $table['recordsFiltered'] = $filtered_data->count();
        $table['data'] = $data_slip_table->get();

        return json_encode($table);
    }

    public function export_excel(Request $request)
    {
        try {
            if (checkAccessMenu('master_slip', 'print') == false) {
                return response()->json([
                    "result" => false,
                    "message" => "Error, anda tidak punya akses!",
                ]);
            }

            // $session = $request->session()->get('access');
            // if (($request->session()->has('token') && array_key_exists('Master Slip', $session)) && $session['Master Slip']['print'] == 1) {
            return Excel::download(new SlipsExport, 'slips.xlsx');
            // } else {
            //     return response()->json([
            //         "result" => false,
            //         "message" => "Error, anda tidak punya akses!",
            //     ]);
            // }
        } catch (\Exception $e) {
            Log::error("Error when export excel master slip");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Error when export excel master slip",
            ]);
        }
    }

    public function copy_data(Request $request)
    {
        try {
            DB::beginTransaction();
            // Check if cabang destination == cabang source
            $cabang_source = $request->id_cabang;
            $cabang_dest = $request->cabang;
            if ($cabang_source == $cabang_dest) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Cabang asal dan cabang tujuan sama, pilih cabang tujuan yang lain",
                ]);
            }

            // Get data slip from cabang source
            $data_slip = Slip::where("id_cabang", $cabang_source)->get();
            if ($data_slip) {
                foreach ($data_slip as $slip) {
                    // Check if already didnt exist
                    $check_slip = Slip::where("id_cabang", $cabang_dest)->where("kode_slip", $slip->kode_slip)->where("nama_slip", $slip->nama_slip)->first();
                    if (!$check_slip) {
                        $ins_slip = new Slip;
                        $ins_slip->id_cabang = $cabang_dest;
                        $ins_slip->kode_slip = $slip->kode_slip;
                        $ins_slip->nama_slip = $slip->nama_slip;
                        $ins_slip->jenis_slip = $slip->jenis_slip;
                        $ins_slip->id_akun = $slip->id_akun;
                        if (!$ins_slip->save()) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "message" => "Error when save copy master slip",
                            ]);
                        }
                    }
                }
            }
            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully copying master slip",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when copy data master slip");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Error when copy data master slip",
            ]);
        }
    }

    public function getSlipByCabang($id_cabang, $id_slip)
    {
        try {
            $data_slip = Slip::where("master_slip.id_cabang", $id_cabang)
                ->where("master_slip.jenis_slip", $id_slip)
                ->join("master_akun", "master_akun.id_akun", "master_slip.id_akun")
                ->get();

            if (!empty($data_slip)) {
                return response()->json([
                    "result" => true,
                    "message" => "Sucessfully get slip data",
                    "data" => $data_slip,
                ]);
            } else {
                return response()->json([
                    "result" => false,
                    "message" => "Failed, slip data not found",
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error when get slip data by cabang");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Failed, error when get slip data",
            ]);
        }
    }
}
