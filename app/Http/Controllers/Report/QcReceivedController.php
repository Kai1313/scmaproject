<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class QcReceivedController extends Controller
{
    public function index(Request $request)
    {
        // if (checkUserSession($request, 'master_wrapper', 'show') == false) {
        //     return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        // }

        if ($request->ajax()) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $idCabang = explode(',', $request->id_cabang);

            $data = DB::table('pembelian_detail')
                ->select(
                    'id_pembelian_detail',
                    'tanggal_qc',
                    'nama_pembelian',
                    'nama_barang',
                    DB::raw('sum(pembelian_detail.jumlah_purchase) as jumlah_pembelian_detail'),
                    'status_qc',
                    'nama_satuan_barang',
                    'reason',
                    'qc.sg_pembelian_detail',
                    'qc.be_pembelian_detail',
                    'qc.ph_pembelian_detail',
                    'qc.warna_pembelian_detail',
                    'qc.keterangan_pembelian_detail',
                    'qc.bentuk_pembelian_detail'
                )
                ->leftJoin('qc', function ($qc) {
                    $qc->on('pembelian_detail.id_pembelian', '=', 'qc.id_pembelian')->on('pembelian_detail.id_barang', '=', 'qc.id_barang');
                })
                ->leftJoin('pembelian', 'pembelian_detail.id_pembelian', '=', 'pembelian.id_pembelian')
                ->leftJoin('barang', 'pembelian_detail.id_barang', '=', 'barang.id_barang')
                ->leftJoin('satuan_barang', 'pembelian_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
                ->whereBetween('pembelian.tanggal_pembelian', [$startDate, $endDate])
                ->whereIn('pembelian.id_cabang', $idCabang);

            $data = $data->groupBy('pembelian_detail.id_pembelian', 'pembelian_detail.id_barang')
                ->orderBy('pembelian.tanggal_pembelian', 'desc')->get();

            return response()->json([
                'datas' => $data,
            ]);
        }

        $duration = DB::table('setting')->where('code', 'QC Duration')->first();
        $countdown = $duration->value2;

        return view('report_ops.qualityControl.index', [
            "pageTitle" => "SCA OPS | Laporan QC Penerimaan Barang | List",
            'countDown' => $countdown,
        ]);
    }

    function print(Request $request) {
        // if (checkUserSession($request, 'master_wrapper', 'print') == false) {
        //     return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        // }

        return view('report_ops.qualityControl.print', [
            "pageTitle" => "SCA OPS | Laporan QC Penerimaan Barang | Print",
        ]);
    }
}
