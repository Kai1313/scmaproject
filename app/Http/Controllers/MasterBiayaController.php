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
            $data = DB::table('master_biaya as mb')
                ->select('id_biaya', 'nama_biaya', 'ispph', 'isppn', 'value_pph', 'aktif', 'mb.dt_created as created_at', 'ma.nama_akun as akun_biaya', 'man.nama_akun as akun_pph')
                ->leftJoin('master_akun as ma', 'id_akun_biaya', '=', 'ma.id_akun')
                ->leftJoin('master_akun as man', 'id_akun_pph', '=', 'man.id_akun');
            if (isset($request->c)) {
                $data = $data->where('mb.id_cabang', $request->c);
            }

            $data = $data->orderBy('created_at', 'asc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="horizontal-list">';
                    $btn .= '<li><a href="' . route('master-biaya-entry', $row->id_biaya) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    $btn .= '<li><a href="' . route('master-biaya-delete', $row->id_biaya) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Hapus</a></li>';
                    $btn .= '</ul>';
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

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.master.biaya.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Master Biaya | List",
        ]);
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
            "pageTitle" => "SCA OPS | Master Biaya | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id)
    {
        $paramValidate = [
            'id_cabang' => 'required',
            'nama_biaya' => 'required',
            'id_akun_biaya' => 'required',
        ];

        $messages = [
            'id_cabang.required' => 'Cabang harus diisi',
            'nama_biaya.required' => 'Nama biaya harus diisi',
            'id_akun_biaya.required' => 'Akun biaya harus diisi',
        ];

        if (isset($request->ispph)) {
            $paramValidate['value_pph'] = 'required';
            $paramValidate['id_akun_pph'] = 'required';
            $messages['value_pph.required'] = 'Nilai PPh harus diisi';
            $messages['id_akun_pph.required'] = 'Akun PPh harus diisi';
        }

        $valid = Validator::make($request->all(), $paramValidate, $messages);
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

        return redirect()
            ->route('master-biaya-entry', $data->id_biaya)
            ->with('success', 'Data berhasil tersimpan');
    }

    public function destroy(Request $request, $id)
    {
        $data = MasterBiaya::find($id);
        if (!$data) {
            return response()->json(['message' => 'data tidak ditemukan'], 500);
        }

        $data->delete();

        return redirect()
            ->route('master-biaya-page')
            ->with('success', 'Data berhasil terhapus');
    }
}
