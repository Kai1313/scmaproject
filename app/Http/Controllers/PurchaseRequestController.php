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

        return view('ops.purchaseRequest.form', [
            'data' => $data,
            'cabang' => $cabang,
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
}
