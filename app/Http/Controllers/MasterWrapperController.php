<?php

namespace App\Http\Controllers;

use App\MasterWrapper;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Log;
use Yajra\DataTables\DataTables;

class MasterWrapperController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('master_wrapper')
                ->select('id_wrapper', 'nama_wrapper', 'weight', 'catatan', 'path2', 'path', 'dt_created as created_at');
            if (isset($request->c)) {
                $data = $data->where('id_cabang', $request->c);
            }

            $data = $data->orderBy('master_wrapper.dt_created', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="horizontal-list">';
                    $btn .= '<li><a href="' . route('master-wrapper-view', $row->id_wrapper) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    $btn .= '<li><a href="' . route('master-wrapper-entry', $row->id_wrapper) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    $btn .= '<li><a href="' . route('master-wrapper-delete', $row->id_wrapper) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Hapus</a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->editColumn('path2', function ($row) use ($request) {
                    if ($request->show_img == "true") {
                        return '<img src="' . env('FTP_GET_FILE') . $row->path2 . '" width="100">';
                    } else {
                        return '<span style="color:#a9a9a9;">Gambar tidak ditampilkan</span>';
                    }
                })
                ->rawColumns(['action', 'path2'])
                ->make(true);
        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.master.wrapper.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Master Wrapper | List",
        ]);
    }

    public function entry($id = 0)
    {
        $data = MasterWrapper::find($id);
        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.master.wrapper.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Master Wrapper | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id)
    {
        $paramValidate = [
            'id_cabang' => 'required',
            'nama_wrapper' => 'required',
            'weight' => 'required',
        ];

        $messages = [
            'id_cabang.required' => 'Cabang harus diisi',
            'nama_wrapper.required' => 'Nama Pembungkus harus diisi',
            'weight.required' => 'Berat harus diisi',
        ];

        $valid = Validator::make($request->all(), $paramValidate, $messages);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid)->withInput($request->all());
        }

        $data = MasterWrapper::find($id);
        try {
            DB::beginTransaction();
            if (!$data) {
                $data = new MasterWrapper;
                $data['dt_created'] = date('Y-m-d H:i:s');
            } else {
                $data['dt_modified'] = date('Y-m-d H:i:s');
            }

            $data->fill($request->all());
            $data['weight'] = normalizeNumber($request->weight);
            $data->save();

            $data->uploadfile($request, $data);

            DB::commit();
            return redirect()
                ->route('master-wrapper-entry', $data->id_wrapper)
                ->with('success', 'Data berhasil tersimpan');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save wrapper");
            Log::error($e);
            return redirect()
                ->route('master-wrapper-entry', $data ? $data->id_wrapper : 0)
                ->with('error', 'Data gagal tersimpan');
        }
    }

    public function viewData($id)
    {
        $data = MasterWrapper::find($id);

        return view('ops.master.wrapper.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Master Wrapper | Detail",
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $data = MasterWrapper::find($id);
        if (!$data) {
            return response()->json(['message' => 'data tidak ditemukan'], 500);
        }

        if ($data && $data->path) {
            \Storage::delete([$data->path, $data->path2]);
        }

        $data->delete();
        return redirect()
            ->route('master-wrapper-page')
            ->with('success', 'Data berhasil terhapus');
    }
}
