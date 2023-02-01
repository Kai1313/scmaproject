<?php

namespace App\Http\Controllers;

use App\MasterBiaya;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class MasterBiayaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('master_biaya as mb')->select('*', 'mb.dt_created as created_at', 'ma.nama_akun as akun_biaya', 'man.nama_akun as akun_pph')
                ->leftJoin('master_akun as ma', 'id_akun_biaya', '=', 'ma.id_akun')
                ->leftJoin('master_akun as man', 'id_akun_pph', '=', 'man.id_akun')
                ->latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('master-biaya-entry', $row->id_biaya) . '" class="btn btn-warning btn-sm">Edit</a>';
                    $btn .= '<a href="' . route('master-biaya-delete', $row->id_biaya) . '" class="btn btn-danger btn-sm btn-destroy">Delete</a>';
                    return $btn;
                })
                ->editColumn('isppn', function ($row) {
                    return '<i class="fa fa-' . ($row->isppn ? 'check' : 'times') . '" aria-hidden="true"></i>';
                })
                ->editColumn('ispph', function ($row) {
                    return '<i class="fa fa-' . ($row->ispph ? 'check' : 'times') . '" aria-hidden="true"></i>';
                })
                ->editColumn('aktif', function ($row) {
                    return '<i class="fa fa-' . ($row->aktif ? 'check' : 'times') . '" aria-hidden="true"></i>';
                })
                ->rawColumns(['action', 'isppn', 'ispph', 'aktif'])
                ->make(true);
        }

        return view('ops.master.biaya.index');
    }

    public function entry($id = 0)
    {
        $data = MasterBiaya::find($id);
        $akunBiaya = DB::table('master_akun')->where('isshown', 1)->get();
        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.master.biaya.form', [
            'data' => $data,
            'akunBiaya' => $akunBiaya,
            'cabang' => $cabang,
        ]);
    }

    public function saveEntry(Request $request, $id)
    {
        $paramValidate = [
            'id_cabang' => 'required',
            'nama_biaya' => 'required',
            'id_akun_biaya' => 'required',
        ];

        if (isset($request->ispph)) {
            $paramValidate['value_pph'] = 'required';
            $paramValidate['id_akun_pph'] = 'required';
        }

        $valid = Validator::make($request->all(), $paramValidate);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid)->withInput($request->all());
        }

        $data = MasterBiaya::find($id);
        if (!$data) {
            $data = new MasterBiaya;
            $data['dt_created'] = date('Y-m-d H:i:s');
        } else {
            $data['dt_modified'] = date('Y-m-d H:i:s');
        }

        $data->fill($request->all());
        $data['isppn'] = isset($request->isppn) ? $request->isppn : 0;
        $data['ispph'] = isset($request->ispph) ? $request->ispph : 0;
        $data['aktif'] = isset($request->aktif) ? $request->aktif : 0;
        $data->save();

        return redirect()->route('master-biaya-entry', $data->id_biaya);
    }

    public function destroy(Request $request, $id)
    {
        $data = MasterBiaya::find($id);
        if (!$data) {
            return response()->json(['message' => 'data tidak ditemukan'], 500);
        }

        $data->delete();

        return redirect()->route('master-wrapper-page');
    }
}
