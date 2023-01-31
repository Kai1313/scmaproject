<?php

namespace App\Http\Controllers;

use App\MasterBiaya;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterBiayaController extends Controller
{
    public function index()
    {
        return view('ops.master.biaya.index');
    }

    public function entry($id = 0)
    {
        $data = MasterBiaya::find($id);
        $akunBiaya = DB::table('master_akun')->where('isshown', 1)->get();

        return view('ops.master.biaya.form', [
            'data' => $data,
            'akunBiaya' => $akunBiaya,
        ]);
    }

    public function saveEntry(Request $request, $id)
    {
        $paramValidate = [
            'nama_biaya' => 'required',
            'id_akun_biaya' => 'required',
        ];

        if (isset($request->ispph)) {
            $paramValidate['value_pph'] = 'required';
            $paramValidate['id_akun_pph'] = 'required';
        }

        $valid = Validator::make($request->all(), $paramValidate);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid)->whiteInput($request->all());
        }

        return $request->all();

        $data = MasterBiaya::find($id);
        if (!$data) {
            $data = new MasterBiaya;
            $data['dt_created'] = date('Y-m-d H:i:s');
        } else {
            $data['dt_modifield'] = date('Y-m-d H:i:s');
        }

        $data->fill($request->all());
        $data['isppn'] = isset($request->isppn) ? $request->isppn : 0;
        $data['ispph'] = isset($request->ispph) ? $request->ispph : 0;
        $data['aktif'] = isset($request->aktif) ? $request->aktif : 0;
        $data->save();

        return redirect()->back();
    }
}
