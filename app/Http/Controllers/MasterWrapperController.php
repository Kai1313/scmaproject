<?php

namespace App\Http\Controllers;

use App\MasterWrapper;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class MasterWrapperController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MasterWrapper::select('*', 'dt_created as created_at')->latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {

                    $btn = '<a href="' . route('master-wrapper-entry', $row->id_wrapper) . '" class="edit btn btn-warning btn-sm">Edit</a>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('ops.master.wrapper.index');
    }

    public function getDatatable(Request $request)
    {

    }

    public function entry($id = 0)
    {
        $data = MasterWrapper::find($id);
        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.master.wrapper.form', [
            'data' => $data,
            'cabang' => $cabang,
        ]);
    }

    public function saveEntry(Request $request, $id)
    {
        $paramValidate = [
            'nama_wrapper' => 'required',
            'weight' => 'required',
        ];

        $valid = Validator::make($request->all(), $paramValidate);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid)->whiteInput($request->all());
        }

        $data = MasterWrapper::find($id);
        if (!$data) {
            $data = new MasterWrapper;
            $data['dt_created'] = date('Y-m-d H:i:s');
        } else {
            $data['dt_modified'] = date('Y-m-d H:i:s');
        }

        $data->fill($request->all());
        $data->save();

        return redirect()->back();
    }
}
