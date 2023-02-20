<?php

namespace App\Http\Controllers;

use App\Models\Master\Slip;
use App\Models\Master\Cabang;
use App\Models\Master\Akun;
use App\Exports\SlipsExport;
use Illuminate\Http\Request;
use DB;
use Log;
use Excel;

class MasterSlipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data_slip = Slip::join('master_akun', 'master_slip.id_akun', '=', 'master_akun.id_akun')->select('master_slip.*', 'master_akun.nama_akun')->get();
        $cabang = Cabang::find(1);
        $data_cabang = Cabang::all();

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | List",
            "cabang" => $cabang,
            "data_cabang" => $data_cabang,
            "data_slip" => $data_slip
        ];

        if ($request->session()->has('token')) {
            return view('accounting.master.slip.index', $data);
        }
        else {
            return view('exceptions.forbidden');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $cabang = Cabang::find(1);
        $data_akun = Akun::where("isshown", 1)->where("id_cabang", $cabang->id_cabang)->get();//DB::select('select * from master_akun');
        $data_cabang = DB::select('select * from cabang where status_cabang = 1');

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | Create",
            "data_akun" => $data_akun,
            "data_cabang" => $data_cabang
        ];

        if ($request->session()->has('token')) {
            return view('accounting.master.slip.form', $data);
        }
        else {
            return view('exceptions.forbidden');
        }
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
                    "result" => FALSE,
                    "message" => "Error when saving data slip"
                ]);
            }

            DB::commit();

            $data = [
                'result' => true,
                'message' => 'Success save ' . $request->nama_slip
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            DB::rollback();
            Log::debug(json_encode($request->all()));
            $data = [
                'result' => false,
                'message' => 'Failed when saving data slip ' . $e
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
        $data_slip = Slip::join('master_akun', 'master_slip.id_akun', 'master_akun.id_akun')
            ->join('cabang', 'cabang.id_cabang', 'master_slip.id_cabang')
            ->where('id_slip', $id)
            ->select('master_slip.*', 'master_akun.nama_akun', 'cabang.kode_cabang', 'cabang.nama_cabang')->first();
        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | List",
            "data_slip" => $data_slip
        ];

        if ($request->session()->has('token')) {
            return view('accounting.master.slip.detail', $data);
        }
        else {
            return view('exceptions.forbidden');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id = null)
    {
        $data_akun = DB::select('select * from master_akun');
        $data_slip = Slip::join('master_akun', 'master_slip.id_akun', 'master_akun.id_akun')->where('id_slip', $id)->select('master_slip.*', 'master_akun.nama_akun')->first();
        $data_cabang = DB::select('select * from cabang where status_cabang = 1');

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | Create",
            "data_akun" => $data_akun,
            "data_slip" => $data_slip,
            "data_cabang" => $data_cabang
        ];

        if ($request->session()->has('token')) {
            return view('accounting.master.slip.form', $data);
        }
        else {
            return view('exceptions.forbidden');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // try {
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
                    "result" => FALSE,
                    "message" => "Error when saving data slip"
                ]);
            }

            DB::commit();

            $data = [
                'result' => true,
                'message' => 'Success save ' . $request->nama_slip
            ];
        } else {
            DB::rollback();
            $data = [
                'result' => false,
                'message' => "Can't find slip " . $request->id_slip
            ];
        }

        return response()->json($data);
        // } catch (\Exception $e) {
        //     DB::rollback();
        //     Log::debug(json_encode($request->all()));
        //     $data = [
        //         'result' => false,
        //         'message' => 'Failed when saving data slip ' . $e
        //     ];

        //     return response()->json($data);
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data_journal_header = DB::select('select * from jurnal_header where id_slip = ' . $id);
        $data_slip = Slip::find($id);
        $kode_slip = $data_slip->kode_slip;
        if (!empty($data_journal_header)) {
            // return back()->with("failed", "Maaf, tidak bisa menghapus slip" . $data_slip->kode_slip . "karena sudah digunakan pada jurnal");
            return response()->json([
                "result"=>FALSE,
                "message"=>"Maaf, tidak bisa menghapus slip dengan kode slip ".$kode_slip.", karena sudah digunakan pada jurnal"
            ]);
        }

        Slip::find($id)->delete();
        // return back()->with("success", "Berhasil menghapus slip " .  $data_slip->kode_slip);
        return response()->json([
            "result"=>TRUE,
            "message"=>"Berhasil menghapus slip dengan kode slip ".$kode_slip
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

        foreach($request->order as $key => $order){
            $columnIdx = $order['column'];
            $sortDir = $order['dir'];
            $sort[] = [
                'column' => $request->columns[$columnIdx]['name'],
                'dir' => $sortDir
            ];
        }

        $draw = $request->draw;
        $current_page = $offset/$limit +1;

        $data_slip = Slip::join('master_akun', 'master_akun.id_akun', 'master_slip.id_akun')
                ->select('master_slip.*', 'master_akun.nama_akun', DB::raw('
                    (CASE
                        WHEN jenis_slip = 0 THEN "Kas"
                        WHEN jenis_slip = 1 THEN "Bank"
                        WHEN jenis_slip = 2 THEN "Piutang Dagang"
                        WHEN jenis_slip = 3 THEN "Hutang Dagang"
                    END) as jenis_name
                '));

        $data_slip_table = DB::table(DB::raw('(' . $data_slip->toSql() . ') as master_slip'));
        $data_slip_table = $data_slip_table->where('id_cabang', $cabang);

        if(!empty($keyword)){
            $data_slip_table->where(function ($query) use($keyword){
                $query->orWhere('kode_slip', 'LIKE', "%$keyword%")
                    ->orWhere('nama_slip', 'LIKE', "%$keyword%")
                    ->orWhere('jenis_name', 'LIKE', "%$keyword%")
                    ->orWhere('nama_akun', 'LIKE', "%$keyword%");
            });
        }

        $filtered_data = $data_slip_table->get();

        if($sort){
            if(!is_array($sort)){
                $message = "Invalid array for parameter sort";
                $data = [
                    'result' => false,
                    'message' => $message
                ];
                return response()->json($data);
            }

            foreach($sort as $key => $s){
                $column = $s['column'];
                $directon = $s['dir'];
                $data_slip_table->orderBy($column, $directon);
            }
        }else{
            $data_slip_table->orderBy('kode_slip', 'ASC');
        }

        // pagination
        if($current_page){
            $page = $current_page;
            $limit_data = $data_slip_table->count();

            if($limit){
                $limit_data = $limit;
            }

            $offset = ($page - 1) * $limit_data;
            if($offset < 0){
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
            return Excel::download(new SlipsExport, 'slips.xlsx');
        }
        catch (\Exception $e) {
            Log::error("Error when export excel master slip");
            Log::error($e);
            return response()->json([
                "result"=>FALSE,
                "message"=>"Error when export excel master slip"
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
                    "result"=>FALSE,
                    "message"=>"Cabang asal dan cabang tujuan sama, pilih cabang tujuan yang lain"
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
                                "result"=>FALSE,
                                "message"=>"Error when save copy master slip"
                            ]);
                        }
                    }
                }
            }
            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully copying master slip"
            ]);
        }
        catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when copy data master slip");
            Log::error($e);
            return response()->json([
                "result"=>FALSE,
                "message"=>"Error when copy data master slip"
            ]);
        }
    }
}
