<?php

namespace App\Http\Controllers;

use App\Models\Accounting\GeneralLedger;
use App\Models\Master\Cabang;
use App\Models\Master\Akun;
use Illuminate\Http\Request;
use DB;

class GeneralLedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cabang = Cabang::find(1);
        $data_cabang = Cabang::all();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Umum | List",
            "cabang" => $cabang,
            "data_cabang" => $data_cabang
        ];

        if ($request->session()->has('token')) {
            return view('accounting.journal.general_ledger.index', $data);
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
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Umum | Create",
            "data_akun" => $data_akun,
            "data_cabang" => $data_cabang
        ];

        if ($request->session()->has('token')) {
            return view('accounting.journal.general_ledger.form', $data);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

      /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function populate(Request $request)
    {
        $cabang = $request->cabang;
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

        $data_general_ledger = GeneralLedger::select('*');

        if(!empty($keyword)){
            $data_general_ledger->where(function ($query) use($keyword){
                $query->orWhere('id_jurnal_umum', 'LIKE', "%$keyword%")
                    ->orWhere('nama_jurnal_umum', 'LIKE', "%$keyword%")
                    ->orWhere('status_jurnal_umum', 'LIKE', "%$keyword%")
                    ->orWhere('date_jurnal_umum', 'LIKE', "%$keyword%");
            });
        }

        $filtered_data = $data_general_ledger->get();

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
                $data_general_ledger->orderBy($column, $directon);
            }
        }else{
            $data_general_ledger->orderBy('id_jurnal_umum', 'ASC');
        }

        // pagination
        if($current_page){
            $page = $current_page;
            $limit_data = $data_general_ledger->count();

            if($limit){
                $limit_data = $limit;
            }

            $offset = ($page - 1) * $limit_data;
            if($offset < 0){
                $offset = 0;
            }

            $data_general_ledger->skip($offset)->take($limit_data);
        }


        $table['draw'] = $draw;
        $table['recordsTotal'] = $data_general_ledger->count();
        $table['recordsFiltered'] = $filtered_data->count();
        $table['data'] = $data_general_ledger->get();

        return json_encode($table);
    }
}
