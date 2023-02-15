<?php

namespace App\Http\Controllers;

use App\PurchaseRequest;
use DB;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.purchaseRequest.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Permintaan Pembelian | List",
        ]);
    }

    public function entry($id = 0)
    {
        $data = PurchaseRequest::find($id);
        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();
        $satuan = DB::table('satuan_barang')->select('id_satuan_barang as id', 'nama_satuan_barang as text')
            ->where('status_satuan_barang', 1)->get();
        // return $satuan;

        return view('ops.purchaseRequest.form', [
            'data' => $data,
            'cabang' => $cabang,
            'satuan' => $satuan,
            "pageTitle" => "SCA OPS | Permintaan Pembelian | " . ($id == 0 ? 'Create' : 'Edit'),
        ]);
    }

    public function saveEntry(Request $request, $id)
    {
        return $request->all();
    }

    public function destroy(Request $request, $id)
    {
        return $request->all();
    }

    public function autoWerehouse(Request $request)
    {
        $search = $request->search;
        $idCabang = $request->id_cabang;
        $datas = DB::table('gudang')->select('id_gudang as id', 'nama_gudang as text')
            ->where('id_cabang', $idCabang)
            ->where('status_gudang', 1)
            ->where('nama_gudang', 'like', '%' . $search . '%')->get();
        return $datas;
    }

    public function autoUser(Request $request)
    {
        $search = $request->search;
        $datas = DB::table('pengguna')->select('id_pengguna as id', 'nama_pengguna as text')
            ->where('status_pengguna', 1)
            ->where('nama_pengguna', 'like', '%' . $search . '%')->get();
        return $datas;
    }

    public function autoItem(Request $request)
    {
        $serach = $request->serach;
        $datas = DB::table('barang')->select('id_barang as id', 'nama_barang as text')
            ->where('status_barang', 1)
            ->where('nama_barang', 'like', '%' . $serach . '%')->limit(10)->get();
        return $datas;
    }
}
