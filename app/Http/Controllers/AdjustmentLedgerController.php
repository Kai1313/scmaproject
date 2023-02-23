<?php

namespace App\Http\Controllers;

use App\Models\Accounting\GeneralLedger;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use App\Models\Master\Slip;
use Illuminate\Http\Request;
use DB;
use Log;
use PDF;

class AdjustmentLedgerController extends Controller
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
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Penyesuaian | List",
            "cabang" => $cabang,
            "data_cabang" => $data_cabang
        ];

        if ($request->session()->has('token')) {
            return view('accounting.journal.adjusting_journal.index', $data);
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
        $data_cabang = Cabang::where("status_cabang", 1)->get();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Penyesuaian | Create",
            "data_cabang" => $data_cabang,
        ];

        Log::debug(json_encode($request->session()->get('user')));

        if ($request->session()->has('token')) {
            return view('accounting.journal.adjusting_journal.form', $data);
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

    public function generateJournalCode($cabang)
    {
        try {
            $ex = 0;
            do {
                // Init data
                $kodeCabang = Cabang::find($cabang);
                $prefix = $kodeCabang->kode_cabang.".".date("ym");

                // Check exist
                $check = JurnalHeader::where("kode_jurnal", "LIKE", "$prefix%")->orderBy("kode_jurnal", "DESC")->get();
                if (count($check) > 0) {
                    $max = count($check);
                    $max += 1;
                    $code = $prefix.".".sprintf("%04s", $max);
                }
                else {
                    $code = $prefix.".0001";
                }
                $ex++;
                if ($ex >= 5) {
                    $code = "error";
                    break;
                }
            } while (JurnalHeader::where("kode_jurnal", $code)->first());
            return $code;
        } 
        catch (\Exception $e) {
            Log::error("Error when generate journal code");
        }
    }
}
