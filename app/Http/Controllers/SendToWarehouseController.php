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

    public function printQrcode(Request $request, $id)
    {
        $data = DB::table('pindah_barang')->where('id_pindah_barang', $id)->first();
        if (!$data) {
            return abort(404);
        }

        $details = DB::table('pindah_barang_detail')
            ->select(
                'kode_batang_master_qr_code',
                'nama_barang',
                'nama_satuan_barang',
                'jumlah_master_qr_code',
                'sisa_master_qr_code',
                'sg_master_qr_code',
                'batch_master_qr_code',
                DB::raw('master_qr_code.weight_zak + master_qr_code.weight as total_tare'),
                'kode_batang_rak'
            )
            ->join('master_qr_code', 'pindah_barang_detail.qr_code', 'kode_batang_master_qr_code')
            ->join('barang', 'master_qr_code.id_barang', 'barang.id_barang')
            ->join('satuan_barang', 'master_qr_code.id_satuan_barang', 'satuan_barang.id_satuan_barang')
            ->leftJoin('rak', 'master_qr_code.id_rak', 'rak.id_rak')
            ->where('pindah_barang_detail.id_pindah_barang', $id);

        // if (isset($request->start) && isset($request->end)) {
        //     $start = $request->start - 1;
        //     $end = $request->end - $start;
        //     $details = $details->skip($start)->limit($end);
        // } else {
        //     $details = $details->limit(20);
        // }

        $details = $details->get();

        if (count($details) > 0) {
            $mpdf = PDF::loadView('ops.sendToWarehouse.print-qrcode', ['data' => $data, 'details' => $details]);
            $mpdf->setPaper([0, 0, 283.465, 113.386], 'portrait');
            $mpdf->output();

            return $mpdf->stream('Qrcode kirim gudang ' . $data->kode_pindah_barang . '.pdf');
        }

        return abort(404);
    }
}
