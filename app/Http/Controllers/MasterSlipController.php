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
        if (checkUserSession($request, 'master/slip', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_slip = Slip::join('master_akun', 'master_slip.id_akun', '=', 'master_akun.id_akun')->select('master_slip.*', 'master_akun.nama_akun')->get();
        // $cabang = Cabang::find(1); // REDUNDANT
        $data_cabang = getCabang();
        $user_id = $request->user_id;

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | List",
            // "cabang" => $cabang,
            "data_cabang" => $data_cabang,
            "data_slip" => $data_slip,
        ];

        return view('accounting.master.slip.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (checkAccessMenu('master/slip', 'create') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = getCabang();
        if (count($data_cabang) == 0) {
            $cabang = Cabang::find(1);
        } else {
            $cabang = Cabang::find($data_cabang[0]->id_cabang);
        }
        $data_akun = Akun::where("isshown", 1)->where("id_cabang", $cabang->id_cabang)->get(); //DB::select('select * from master_akun');

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | Create",
            "data_akun" => $data_akun,
            "data_cabang" => $data_cabang,
        ];

        return view('accounting.master.slip.form', $data);
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
        if (checkAccessMenu('master/slip', 'show') == false) {
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

        return view('accounting.master.slip.detail', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id = null)
    {
        if (checkAccessMenu('master/slip', 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_akun = DB::select('select * from master_akun');
        $data_slip = Slip::join('master_akun', 'master_slip.id_akun', 'master_akun.id_akun')->where('id_slip', $id)->select('master_slip.*', 'master_akun.nama_akun')->first();
        $data_cabang = getCabang();
        // $session = $request->session()->get('access');

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | Create",
            "data_akun" => $data_akun,
            "data_slip" => $data_slip,
            "data_cabang" => $data_cabang,
        ];

        return view('accounting.master.slip.form', $data);
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

        if (checkAccessMenu('master/slip', 'delete') == false) {
            return response()->json([
                "result" => false,
                "message" => "Maaf, tidak bisa menghapus slip dengan kode slip " . $kode_slip . ", anda tidak punya akses!",
            ]);
        }

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
        if (checkAccessMenu('master/slip', 'print') == false) {
            return response()->json([
                "result" => false,
                "message" => "Error, anda tidak punya akses!",
            ]);
        }

        try {
            return Excel::download(new SlipsExport, 'slips.xlsx');
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

    public function getSlipGiroByCabang($id_cabang, $jenis)
    {
        try {
            $jenis = explode(",", $jenis);
            $data_slip = Slip::where("master_slip.id_cabang", $id_cabang)->whereIn("master_slip.jenis_slip", $jenis)->join("master_akun", "master_akun.id_akun", "master_slip.id_akun")->get();
            // Log::info(count($data_slip));
            if (empty($data_slip)) {
                return response()->json([
                    "result" => false,
                    "message" => "Data slip not found!!!",
                ]);
            }
            return response()->json([
                "result" => true,
                "message" => "Successfully get slip giro by cabang",
                "data" => $data_slip,
            ]);
        } catch (\Exception $e) {
            $message = "Error when get slip giro by cabang";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }
}
