<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class QcReceivedController extends Controller
{
    public $arrayStatus = ['' => 'Pending', '1' => 'Passed', '2' => 'Reject'];
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_qc_penerimaan', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = $this->getData($request);

            $html = '';
            $html .= view('report_ops.qualityControl.template', ['datas' => $data, 'arrayStatus' => $this->arrayStatus]);
            return response()->json([
                'html' => $html,
            ]);
        }

        $duration = DB::table('setting')->where('code', 'QC Duration')->first();
        $countdown = $duration->value2;

        return view('report_ops.qualityControl.index', [
            "pageTitle" => "SCA OPS | Laporan QC Penerimaan Barang | List",
            'countDown' => $countdown,
            'arrayStatus' => $this->arrayStatus,
        ]);
    }

    function print(Request $request) {
        if (checkAccessMenu('laporan_qc_penerimaan', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request);
        $arrayCabang = [];
        foreach (session()->get('access_cabang') as $c) {
            $arrayCabang[$c['id']] = $c['text'];
        }

        $eCabang = explode(',', $request->id_cabang);
        $sCabang = [];
        foreach ($eCabang as $e) {
            $sCabang[] = $arrayCabang[$e];
        }

        return view('report_ops.qualityControl.print', [
            "pageTitle" => "SCA OPS | Laporan QC Penerimaan Barang | Print",
            "datas" => $data,
            'arrayStatus' => $this->arrayStatus,
            'cabang' => implode(', ', $sCabang),
            'date' => $request->date,
        ]);
    }

    public function getData($request)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);
        $kodePembelian = $request->kode_pembelian;
        $statusQc = $request->status_qc;
        $namaBarang = $request->nama_barang;

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
            ->whereBetween('pembelian.tanggal_pembelian', [$date])
            ->whereIn('pembelian.id_cabang', $idCabang);
        if ($statusQc != 'all') {
            $data = $data->where('status_qc', $statusQc);
        }

        if ($kodePembelian) {
            $data = $data->where('nama_pembelian', 'like', '%' . $kodePembelian . '%');
        }

        if ($namaBarang) {
            $data = $data->where('nama_barang', 'like', '%' . $namaBarang . '%');
        }

        $data = $data->groupBy('pembelian_detail.id_pembelian', 'pembelian_detail.id_barang')
            ->orderBy('pembelian.tanggal_pembelian', 'asc')->get();

        return $data;
    }
}
