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
    public function index(Request $request)
    {
        $data = [
            "pageTitle"=>"SCA Accounting | Master Slip | List"
        ];

        if ($request->session()->has('token')) {
            return view('accounting.master.slip.index', $data);
        }else{
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
        $data_akun = DB::select('select * from master_akun');

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | Create",
            "data_akun" => $data_akun
        ];

        if($request->session()->has('token')){
            return view('accounting.master.slip.form', $data);
        }else{
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
    public function show(Request $request, $id = null)
    {
        $data = [
            "pageTitle"=>"SCA Accounting | Master Slip | List"
        ];

        if($request->session()->has('token')){
            return view('accounting.master.slip.detail', $data);
        }else{
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

        $data = [
            "pageTitle" => "SCA Accounting | Master Slip | Create",
            "data_akun" => $data_akun
        ];

        if($request->session()->has('token')){
            return view('accounting.master.slip.form', $data);
        }else{
            return view('exceptions.forbidden');
        }
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
