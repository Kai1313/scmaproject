<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use App\Exports\AkunsExport;
use Log;
use DB;
use Excel;

class MasterCoaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data_akun_level1 = Akun::whereNull('header1')->whereNull('header2')->whereNull('header3')->get();
        $data_akun_level2 = Akun::whereNotNull('header1')->whereNull('header2')->whereNull('header3')->get();
        $data_akun_level3 = Akun::whereNotNull('header1')->whereNotNull('header2')->whereNull('header3')->get();
        $data_akun_level4 = Akun::whereNotNull('header1')->whereNotNull('header2')->whereNotNull('header3')->get();

        $cabang = Cabang::find(1);
        $data_cabang = Cabang::all();

        $data = [
            "pageTitle"=>"SCA Accounting | Master CoA | List",
            "cabang" => $cabang,
            "data_cabang" => $data_cabang,
            "data_akun_level1" => $data_akun_level1,
            "data_akun_level2" => $data_akun_level2,
            "data_akun_level3" => $data_akun_level3,
            "data_akun_level4" => $data_akun_level4
        ];
        return view('accounting.master.coa.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get data for select
        $data_cabang = Cabang::all();
        $data_akun = Akun::all();
        // dd($akun);
        $data = [
            "pageTitle"=>"SCA Accounting | Master CoA | Create",
            "data_cabang"=>$data_cabang,
            "data_akun"=>$data_akun,
        ];
        return view('accounting.master.coa.form', $data);
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
            DB::beginTransaction();
            // Init data
            $akun = new Akun;
            $akun->id_cabang = $request->cabang;
            $akun->kode_akun = $request->kode;
            $akun->nama_akun = $request->nama;
            $akun->tipe_akun = $request->tipe;
            $akun->id_parent = $request->parent;
            $akun->isshown = $request->shown;
            $akun->header1 = $request->header1;
            $akun->header2 = $request->header2;
            $akun->header3 = $request->header3;
            $akun->catatan = $request->notes;

            // Save data
            if (!$akun->save()) {
                DB::rollback();
                Log::error("Error when saving data akun");
                return response()->json([
                    "result"=>FALSE,
                    "message"=>"Error when saving data akun"
                ]);
            }
            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "akun"=>$akun,
                "message"=>"Successfully saving data akun"
            ]);
        }
        catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when store akun");
            Log::error($e);
            return response()->json([
                "result"=>FALSE,
                "message"=>"Error when store akun"
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Get data for select
        $data_cabang = Cabang::all();
        $data_akun = Akun::all();

        // Get data akun
        $akun = Akun::where("id_akun", $id)->first();

        $data = [
            "pageTitle"=>"SCA Accounting | Master CoA | Show",
            "data_cabang"=>$data_cabang,
            "data_akun"=>$data_akun,
            "akun"=>$akun
        ];
        return view('accounting.master.coa.form', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Get data for select
        $data_cabang = Cabang::all();
        $data_akun = Akun::all();

        // Get data akun
        $akun = Akun::where("id_akun", $id)->first();

        $data = [
            "pageTitle"=>"SCA Accounting | Master CoA | Edit",
            "data_cabang"=>$data_cabang,
            "data_akun"=>$data_akun,
            "akun"=>$akun
        ];
        return view('accounting.master.coa.form', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            // Get akun data
            $akun = Akun::where("id_akun", $id)->first();
            if ($akun) {
                // Init data
                $akun->id_cabang = $request->cabang;
                $akun->kode_akun = $request->kode;
                $akun->nama_akun = $request->nama;
                $akun->tipe_akun = $request->tipe;
                $akun->id_parent = $request->parent;
                $akun->isshown = $request->shown;
                $akun->header1 = $request->header1;
                $akun->header2 = $request->header2;
                $akun->header3 = $request->header3;
                $akun->catatan = $request->notes;
                // $akun->dt_modified = date('Y-m-d H:i:s');

                // Save data
                if (!$akun->save()) {
                    DB::rollback();
                    Log::error("Error when updating data akun");
                    return response()->json([
                        "result"=>FALSE,
                        "message"=>"Error when updating data akun"
                    ]);
                }
                DB::commit();
                return response()->json([
                    "result"=>TRUE,
                    "akun"=>$akun,
                    "message"=>"Successfully updating data akun"
                ]);
            }
            else {
                DB::rollback();
                return response()->json([
                    "result"=>FALSE,
                    "message"=>"Could not find akun with id ".$id
                ]);
            }
        }
        catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when update akun");
            Log::error($e);
            return response()->json([
                "result"=>FALSE,
                "message"=>"Error when update akun"
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            // Get akun data
            $akun = Akun::where("id_akun", $id)->first();
            if ($akun) {
                // Delete data
                if (!$akun->delete()) {
                    DB::rollback();
                    Log::error("Error when deleting data akun");
                    return response()->json([
                        "result"=>FALSE,
                        "message"=>"Error when deleting data akun"
                    ]);
                }
                DB::commit();
                return response()->json([
                    "result"=>TRUE,
                    "message"=>"Successfully deleting data akun"
                ]);
            }
            else {
                DB::rollback();
                return response()->json([
                    "result"=>FALSE,
                    "message"=>"Could not find akun with id ".$id
                ]);
            }
        }
        catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when delete akun");
            Log::error($e);
            return response()->json([
                "result"=>FALSE,
                "message"=>"Error when delete akun"
            ]);
        }
    }

    public function get_header1(Request $request)
    {
        try {
            $data_header = Akun::distinct()->get(['header1']);
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully get header1",
                "options"=>$data_header
            ]);
        }
        catch (\Exception $e) {
            Log::error("Error when get header1");
            Log::error($e);
            return response()->json([
                "result"=>FALSE,
                "message"=>"Error when get header1"
            ]);
        }
    }

    public function get_header2(Request $request)
    {
        try {
            $data_header = Akun::distinct()->get(['header2']);
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully get header2",
                "options"=>$data_header
            ]);
        }
        catch (\Exception $e) {
            Log::error("Error when get header2");
            Log::error($e);
            return response()->json([
                "result"=>FALSE,
                "message"=>"Error when get header2"
            ]);
        }
    }

    public function get_header3(Request $request)
    {
        try {
            $data_header = Akun::distinct()->get(['header3']);
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully get header3",
                "options"=>$data_header
            ]);
        }
        catch (\Exception $e) {
            Log::error("Error when get header3");
            Log::error($e);
            return response()->json([
                "result"=>FALSE,
                "message"=>"Error when get header3"
            ]);
        }
    }

    public function export_excel(Request $request)
    {
        try {
            return Excel::download(new AkunsExport, 'akuns.xlsx');
        }
        catch (\Exception $e) {
            Log::error("Error when export excel master coa");
            Log::error($e);
            return response()->json([
                "result"=>FALSE,
                "message"=>"Error when export excel master coa"
            ]);
        }
    }
}
