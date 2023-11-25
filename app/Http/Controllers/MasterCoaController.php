<?php

namespace App\Http\Controllers;

use App\Exports\AkunsExport;
use App\Models\Accounting\JurnalDetail;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use App\Models\Master\Slip;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterCoaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (checkUserSession($request, 'master/coa', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = getCabang();

        if (count($data_cabang) == 0) {
            $cabang = Cabang::find(1);
        } else {
            $cabang = Cabang::find($data_cabang[0]->id_cabang);
        }

        $data = [
            "pageTitle" => "SCA Accounting | Master CoA | List",
            "cabang_user" => $cabang,
            "data_cabang" => $data_cabang,
        ];

        return view('accounting.master.coa.index', $data);
    }

    /**
     * Show populate form data.
     *
     * @param  int $id_cabang
     * @return \Illuminate\Http\Response
     */
    public function populate($id_cabang)
    {
        try {
            $coa = Akun::where('id_cabang', $id_cabang)->get();

            $data = $this->buildTree($coa);

            return response()->json([
                "result" => true,
                "data" => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "result" => false,
                "message" => "Error get data akun",
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (checkAccessMenu('master/coa', 'create') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        // Get data for select
        $data_cabang = getCabang();
        $data_akun = Akun::all();
        // $session = $request->session()->get('access');
        // dd($akun);
        $data = [
            "pageTitle" => "SCA Accounting | Master CoA | Create",
            "data_cabang" => $data_cabang,
            "data_akun" => $data_akun,
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
            // Check if already exist
            $check_akun = Akun::where("id_cabang", $request->cabang)->where("kode_akun", $request->kode)->where("nama_akun", $request->nama)->first();
            if ($check_akun) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "akun" => null,
                    "message" => "Akun sudah pernah dibuat, cek kembali kode akun, cabang, dan nama akun",
                ]);
            }

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
                    "result" => false,
                    "akun" => $akun,
                    "message" => "Error when saving data akun",
                ]);
            }
            DB::commit();
            return response()->json([
                "result" => true,
                "akun" => $akun,
                "message" => "Successfully saving data akun",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when store akun");
            Log::error($e);
            return response()->json([
                "result" => false,
                "akun" => null,
                "message" => "Error when store akun",
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if (checkAccessMenu('master/coa', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        // Get data for select
        $data_cabang = getCabang();
        // $data_akun = Akun::all();

        // Get data akun
        $data_akun = Akun::leftjoin('master_akun as parent', 'parent.id_akun', 'master_akun.id_parent')
            ->join('cabang', 'cabang.id_cabang', 'master_akun.id_cabang')
            ->where("master_akun.id_akun", $id)
            ->select('master_akun.*', 'cabang.*', 'parent.kode_akun as kode_parent', 'parent.nama_akun as nama_parent')
            ->first();
        Log::debug(json_encode($data_akun));

        $data = [
            "pageTitle" => "SCA Accounting | Master CoA | Detail",
            "data_cabang" => $data_cabang,
            "data_akun" => $data_akun,
        ];
        return view('accounting.master.coa.detail', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if (checkAccessMenu('master/coa', 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        // Get data for select
        $data_cabang = getCabang();
        $data_akun = Akun::all();

        // Get data akun
        $akun = Akun::where("id_akun", $id)->first();

        $data = [
            "pageTitle" => "SCA Accounting | Master CoA | Edit",
            "data_cabang" => $data_cabang,
            "data_akun" => $data_akun,
            "akun" => $akun,
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
                        "result" => false,
                        "message" => "Error when updating data akun",
                    ]);
                }
                DB::commit();
                return response()->json([
                    "result" => true,
                    "akun" => $akun,
                    "message" => "Successfully updating data akun",
                ]);
            } else {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Could not find akun with id " . $id,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when update akun");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Error when update akun",
            ]);
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
        if (checkAccessMenu('master/coa', 'delete') == false) {
            return response()->json([
                "result" => false,
                "message" => "Error, Anda tidak punya akses!",
            ]);
        }

        try {
            DB::beginTransaction();
            // Get akun data
            $akun = Akun::where("id_akun", $id)->first();
            $kode_akun = $akun->kode_akun;
            if (checkAccessMenu('master/coa', 'delete') == false) {
                return response()->json([
                    "result" => false,
                    "message" => "Error, Anda tidak punya akses!",
                ]);
            }

            if ($akun) {

                // Init check
                $children = Akun::where('id_parent', $id)->get();
                $akun_slip = Slip::where('id_akun', $id)->get();
                $jurnal_detail = JurnalDetail::where('id_akun', $id)->get();

                // checking
                if ($children->isNotEmpty()) {
                    return response()->json([
                        "result" => false,
                        "message" => "Maaf, tidak bisa menghapus akun dengan kode akun  " . $kode_akun . ". Karena mempunyai sub akun",
                    ]);
                } else if ($akun_slip->isNotEmpty()) {
                    return response()->json([
                        "result" => false,
                        "message" => "Maaf, tidak bisa menghapus akun dengan kode akun  " . $kode_akun . ". Karena telah digunakan pada Master Slip",
                    ]);
                } else if ($jurnal_detail->isNotEmpty()) {
                    return response()->json([
                        "result" => false,
                        "message" => "Maaf, tidak bisa menghapus akun dengan kode akun  " . $kode_akun . ". Karena telah digunakan pada Jurnal",
                    ]);
                }

                // Delete data
                if (!$akun->delete()) {
                    DB::rollback();
                    Log::error("Error when deleting data akun");
                    return response()->json([
                        "result" => false,
                        "message" => "Error when deleting data akun",
                    ]);
                }
                DB::commit();
                return response()->json([
                    "result" => true,
                    "message" => "Successfully deleting data akun",
                ]);
            } else {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Could not find akun with id " . $id,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when delete akun");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Error when delete akun",
            ]);
        }
    }

    public function get_header1(Request $request)
    {
        try {
            $data_header = Akun::distinct()->get(['header1']);
            return response()->json([
                "result" => true,
                "message" => "Successfully get header1",
                "options" => $data_header,
            ]);
        } catch (\Exception $e) {
            Log::error("Error when get header1");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Error when get header1",
            ]);
        }
    }

    public function get_header2(Request $request)
    {
        try {
            $data_header = Akun::distinct()->get(['header2']);
            return response()->json([
                "result" => true,
                "message" => "Successfully get header2",
                "options" => $data_header,
            ]);
        } catch (\Exception $e) {
            Log::error("Error when get header2");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Error when get header2",
            ]);
        }
    }

    public function get_header3(Request $request)
    {
        try {
            $data_header = Akun::distinct()->get(['header3']);
            return response()->json([
                "result" => true,
                "message" => "Successfully get header3",
                "options" => $data_header,
            ]);
        } catch (\Exception $e) {
            Log::error("Error when get header3");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Error when get header3",
            ]);
        }
    }

    public function export_excel(Request $request)
    {
        if (checkAccessMenu('master/coa', 'print') == false) {
            return response()->json([
                "result" => false,
                "message" => "Error, Anda tidak punya akses!",
            ]);
        }

        try {
            // $session = $request->session()->get('access');
            if (checkAccessMenu('master/coa', 'edit') == false) {
                return response()->json([
                    "result" => false,
                    "message" => "Error, anda tidak punya akses!",
                ]);
            }

            return Excel::download(new AkunsExport, 'akuns.xlsx');
        } catch (\Exception $e) {
            Log::error("Error when export excel master coa");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Error when export excel master coa",
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

            // Get data akun from cabang source
            $data_akun = Akun::where("id_cabang", $cabang_source)->get();

            if ($data_akun) {
                $data_akun = $this->buildTree($data_akun);
                $this->saveTreeCoa($data_akun, null, $cabang_dest);
                // Log::debug('tesstt');
                // Log::debug(json_encode($data_akun));
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully copying master akun",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when copy data master akun");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Error when copy data master akun",
            ]);
        }
    }

    private function buildTree($elements, $parentId = 0)
    {
        $branch = array();

        foreach ($elements as $element) {
            if ($element->id_parent == $parentId) {
                $children = $this->buildTree($elements, $element->id_akun);
                if ($children) {
                    $element->children = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    public function saveTreeCoa($data_akun, $parent_id = null, $cabang_dest)
    {
        foreach ($data_akun as $akun) {
            $check_akun = Akun::where("id_cabang", $cabang_dest)
                ->where("kode_akun", $akun->kode_akun)
                ->where("nama_akun", $akun->nama_akun)
                ->first();

            if (!$check_akun) {
                $ins_akun = new Akun;
                $ins_akun->id_cabang = $cabang_dest;
                $ins_akun->kode_akun = $akun->kode_akun;
                $ins_akun->nama_akun = $akun->nama_akun;
                $ins_akun->tipe_akun = $akun->tipe_akun;
                $ins_akun->id_parent = $parent_id;
                $ins_akun->isshown = $akun->isshown;
                $ins_akun->catatan = $akun->catatan;
                $ins_akun->header1 = $akun->header1;
                $ins_akun->header2 = $akun->header2;
                $ins_akun->header3 = $akun->header3;
                $ins_akun->save();
                if (isset($akun->children)) {
                    $this->saveTreeCoa($akun->children, $ins_akun->id_akun, $cabang_dest);
                }
                if (!$ins_akun->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when save copy master akun",
                    ]);
                }
            }
        }
    }

    public function getCoaByCabang($id_cabang)
    {
        try {
            $data_akun = Akun::where("isshown", 1);
            if ($id_cabang != "all") {
                $data_akun = $data_akun->where("id_cabang", $id_cabang);
            }
            else {
                $data_akun = $data_akun->groupBy('kode_akun');
            }
            $data_akun = $data_akun->get();
            if (!empty($data_akun)) {
                return response()->json([
                    "result" => true,
                    "message" => "Sucessfully get coa data",
                    "data" => $data_akun,
                ]);
            } else {
                return response()->json([
                    "result" => false,
                    "message" => "Failed, coa data not found",
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error when get data master akun");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Failed, error when get coa data",
            ]);
        }
    }

    public function getCoa($id)
    {
        try {
            $data_akun = Akun::find($id);
            if (!empty($data_akun)) {
                return response()->json([
                    "result" => true,
                    "message" => "Sucessfully get coa data",
                    "data" => $data_akun,
                ]);
            } else {
                return response()->json([
                    "result" => false,
                    "message" => "Failed, coa data not found",
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error when get data master akun");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Failed, error when get coa data",
            ]);
        }
    }
}
