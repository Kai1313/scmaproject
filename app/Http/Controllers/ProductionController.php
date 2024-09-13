<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use PDF;

class ProductionController extends Controller
{
    public function printHp(Request $request, $id)
    {
        $param = [];
        if ($request->detail) {
            $param = json_decode($request->detail);
        }

        $data = DB::table('produksi')->where('id_jenis_transaksi', 17)->where('id_produksi', $id)->first();
        if (!$data) {
            return abort(404);
        }

        $details = DB::table('produksi_detail')
            ->select(
                'kode_batang_master_qr_code',
                'nama_barang',
                'nama_satuan_barang',
                'jumlah_master_qr_code',
                'sisa_master_qr_code',
                'sg_master_qr_code',
                'batch_master_qr_code',
                DB::raw('weight_zak + weight as total_tare')
            )
            ->join('master_qr_code', 'kode_batang_produksi_detail', 'kode_batang_master_qr_code')
            ->join('barang', 'master_qr_code.id_barang', 'barang.id_barang')
            ->join('satuan_barang', 'master_qr_code.id_satuan_barang', 'satuan_barang.id_satuan_barang')
            ->where('produksi_detail.id_produksi', $id);
        if ($param) {
            $details = $details->whereIn('id_produksi_detail', $param);
        }

        $details = $details->get();

        if (count($details) > 0) {
            $mpdf = PDF::loadView('ops.production.print-hp', ['data' => $data, 'details' => $details]);
            $mpdf->setPaper([0, 0, 283.465, 113.386], 'portrait');
            $mpdf->output();

            return $mpdf->stream('Qrcode Hasil Produksi ' . $data->nama_produksi . '.pdf');
        }

        return view('ops.production.print-hp', ['data' => $data, 'details' => $details]);

        return abort(404);
    }
}
