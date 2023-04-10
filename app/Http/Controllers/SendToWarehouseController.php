<?php

namespace App\Http\Controllers;

use App\MoveBranch;
use Illuminate\Http\Request;

class SendToWarehouseController extends Controller
{
    function print(Request $request, $id) {
        $data = MoveBranch::where('id_jenis_transaksi', 23)->where('id_pindah_barang', $id)->first();
        if (!$data) {
            return 'data tidak ditemukan';
        }

        return view('ops.sendToWarehouse.print', [
            'data' => $data,
        ]);
    }
}
