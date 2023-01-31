<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MasterSlipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            "pageTitle"=>"SCA Accounting | Master Slip | List"
        ];

        return view('accounting.master.slip.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data_akun = DB::select('select * from master_akun');

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | Create",
            "data_akun" => $data_akun
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {
        $data = [
            "pageTitle"=>"SCA Accounting | Master Slip | List"
        ];

        return view('accounting.master.slip.detail', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id = null)
    {
        $data_akun = DB::select('select * from master_akun');

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | Create",
            "data_akun" => $data_akun
        ];

        return view('accounting.master.slip.form', $data);
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
}
