<?php

namespace App\Http\Controllers;

use App\Purchase;
use DB;
use Illuminate\Http\Request;
use PDF;

class PurchaseReceiveController extends Controller
{
    public function printQrcode(Request $request, $id)
    {
        $data = DB::table('pembelian')->where('id_pembelian', $id)->first();
        if (!$data) {
            return abort(404);
        }

        $details = DB::table('pembelian_detail')
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
            ->join('master_qr_code', 'kode_batang_pembelian_detail', 'kode_batang_master_qr_code')
            ->join('barang', 'master_qr_code.id_barang', 'barang.id_barang')
            ->join('satuan_barang', 'master_qr_code.id_satuan_barang', 'satuan_barang.id_satuan_barang')
            ->leftJoin('rak', 'master_qr_code.id_rak', 'rak.id_rak')
            ->where('pembelian_detail.id_pembelian', $id);

        if (isset($request->start) && isset($request->end)) {
            $start = $request->start - 1;
            $end = $request->end - $start;
            $details = $details->skip($start)->limit($end);
        } else {
            $details = $details->limit(20);
        }

        $details = $details->get();

        if (count($details) > 0) {
            $mpdf = PDF::loadView('ops.purchaseReceive.print', ['data' => $data, 'details' => $details]);
            $mpdf->setPaper([0, 0, 283.465, 113.386], 'portrait');
            $mpdf->output();

            return $mpdf->stream('Qrcode Hasil Penerimaan ' . $data->nama_pembelian . '.pdf');
        }

        return abort(404);
    }

    public function printData($id)
    {
        $data = Purchase::find($id);
        if (!$data) {
            return 'data tidak ditemukan';
        }
        $month = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $pdf = PDF::loadView('ops.purchaseReceive.print_data', ['data' => $data, 'month' => $month]);
        $pdf->setPaper('a5', 'landscape');
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $font = $dom_pdf->getFontMetrics()->get_font("sans-serif", "bold");
        $canvas = $dom_pdf->get_canvas();
        $canvas->page_text(518, 70.5, "{PAGE_NUM} dari {PAGE_COUNT}", $font, 9, array(0, 0, 0));

        return $pdf->stream('Penerimaan barang.pdf');
    }
}
