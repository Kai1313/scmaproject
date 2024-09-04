<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use PDF;

class DeliveryOrderController extends Controller
{
    public function printNpb(Request $request, $id)
    {
        $data = DB::table('penjualan')->select('penjualan.*', 'pelanggan.nama_pelanggan', 'pelanggan.alamat_pelanggan', 'nama_ekspedisi')
            ->join('pelanggan', 'penjualan.id_pelanggan', 'pelanggan.id_pelanggan')
            ->leftJoin('ekspedisi', 'penjualan.id_ekspedisi', 'ekspedisi.id_ekspedisi')
            ->where('id_penjualan', $id)->first();

        if (!$data) {
            return abort(404);
        }

        $details = DB::table('penjualan_detail')
            ->select(
                'penjualan_detail.*',
                'nama_barang',
                'satuan_barang.nama_satuan_barang',
                DB::raw('count(*) as total'),
                DB::raw('sum(jumlah_penjualan_detail) sum_total_weight'),
                DB::raw("(select b.nama_satuan_barang FROM isi_satuan_barang a, satuan_barang b WHERE a.id_satuan_barang=b.id_satuan_barang AND a.id_barang=penjualan_detail.id_barang AND a.id_satuan_barang NOT IN (6,7,8) LIMIT 1) as nama_satuan_baru")
            )
            ->join('barang', 'penjualan_detail.id_barang', 'barang.id_barang')
            ->join('satuan_barang', 'penjualan_detail.id_satuan_barang', 'satuan_barang.id_satuan_barang')
            ->where('id_penjualan', $id)
            ->groupBy(['penjualan_detail.id_barang', 'penjualan_detail.jumlah_penjualan_detail'])
            ->get();

        $month = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $pdf = PDF::loadView('ops.deliveryOrder.print-npb', ['data' => $data, 'details' => $details, 'month' => $month]);
        $pdf->setPaper('a5', 'landscape');
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $font = $dom_pdf->getFontMetrics()->get_font("sans-serif", "normal");
        $canvas = $dom_pdf->get_canvas();
        $canvas->page_text(518, 74, "{PAGE_NUM} dari {PAGE_COUNT}", $font, 9, array(0, 0, 0));

        return $pdf->stream('Nota Pengeluaran Barang ' . $data->nama_penjualan . '.pdf');
    }
}
