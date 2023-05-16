<?php

namespace App\Http\Controllers;

use App\MoveBranch;
use Illuminate\Http\Request;
use PDF;

class SendToWarehouseController extends Controller
{
    function print(Request $request, $id) {
        $data = MoveBranch::where('id_jenis_transaksi', 23)->where('id_pindah_barang', $id)->first();
        if (!$data) {
            return 'data tidak ditemukan';
        }

        $pdf = PDF::loadView('ops.sendToWarehouse.print', ['data' => $data]);
        $pdf->setPaper('a5', 'landscape');
        return $pdf->stream('Surat jalan pindah gudang ' . $data->kode_pindah_barang . '.pdf');
    }
}
