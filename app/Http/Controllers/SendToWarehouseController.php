<?php

namespace App\Http\Controllers;

use App\MoveBranch;
use DB;
use Illuminate\Http\Request;
use PDF;

class SendToWarehouseController extends Controller
{
    public function print(Request $request, $id)
    {
        $data = MoveBranch::where('id_jenis_transaksi', 23)->where('id_pindah_barang', $id)->first();
        if (!$data) {
            return 'data tidak ditemukan';
        }

        $pdf = PDF::loadView('ops.sendToWarehouse.print', ['data' => $data]);
        $pdf->setPaper('a5', 'landscape');
        return $pdf->stream('Surat jalan pindah gudang ' . $data->kode_pindah_barang . '.pdf');
    }

    public function print2(Request $request, $id)
    {
        $data = MoveBranch::where('id_jenis_transaksi', 23)->where('id_pindah_barang', $id)->first();
        if (!$data) {
            return 'data tidak ditemukan';
        }

        $dataSatuan = DB::table('isi_satuan_barang')->select(DB::raw('distinct(isi_satuan_barang.id_satuan_barang)'), 'id_barang', 'nama_satuan_barang')
            ->leftJoin('satuan_barang', 'isi_satuan_barang.id_satuan_barang', 'satuan_barang.id_satuan_barang')
            ->where('satuan_wadah_isi_satuan_barang', '1')->get();

        $arraySatuan = [];
        foreach ($dataSatuan as $satuan) {
            $arraySatuan[$satuan->id_barang] = $satuan->nama_satuan_barang;
        }

        $pdf = PDF::loadView('ops.sendToWarehouse.print2', ['data' => $data, 'arraySatuan' => $arraySatuan]);
        $pdf->setPaper('a5', 'landscape');
        return $pdf->stream('Surat jalan pindah gudang ' . $data->kode_pindah_barang . '.pdf');
    }
}
