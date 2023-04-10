<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class MaterialUsageController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'pemakaian_header', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('pemakaian_header')
                ->select(
                    'id_pemakaian',
                    'kode_pemakaian',
                    'tanggal',
                    'g.nama_gudang',
                    'c.nama_cabang',
                    'catatan'
                )
                ->leftJoin('gudang as g', 'pemakaian_header.id_gudang', '=', 'g.id_gudang')
                ->leftJoin('cabang as c', 'pemakaian_header.id_cabang', '=', 'c.id_cabang');
            if (isset($request->c)) {
                $data = $data->where('pemakaian_header.id_cabang', $request->c);
            }

            $data = $data->orderBy('pemakaian_header.dt_created', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="horizontal-list">';
                    $btn .= '<li><a href="' . route('material_usage-view', $row->id_pemakaian) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    $btn .= '<li><a href="' . route('material_usage-entry', $row->id_pemakaian) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    // $btn .= '<li><a href="' . route('received_from_branch-delete', $row->id_pindah_barang) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();
        return view('ops.materialUsage.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Pemakaian | List",
        ]);
    }
}
