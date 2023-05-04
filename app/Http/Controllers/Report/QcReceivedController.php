<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class QcReceivedController extends Controller
{
    public $arrayStatus = ['' => 'Pending', '1' => 'Passed', '2' => 'Reject'];
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_qc_penerimaan', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getData($request, 'datatable');
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

        $data = $this->getData($request, 'print');
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

    public function getData($request, $type)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);
        $kodePembelian = $request->kode_pembelian;
        $statusQc = $request->status_qc;
        $namaBarang = $request->nama_barang;

        $data = DB::table('pembelian_detail as pd')
            ->select(
                'pd.id_pembelian_detail',
                'qc.tanggal_qc',
                'p.nama_pembelian',
                'b.nama_barang',
                DB::raw('sum(pd.jumlah_purchase) as jumlah_pembelian_detail'),
                DB::raw('(CASE
                    WHEN qc.status_qc = 1 THEN "Passed"
                    WHEN qc.status_qc = 2 THEN "Reject"
                    WHEN qc.status_qc = 3 THEN "Hold"
                    ELSE "Belum di QC"
                END) AS status_qc'),
                'sb.nama_satuan_barang',
                'qc.reason',
                'qc.sg_pembelian_detail',
                'qc.be_pembelian_detail',
                'qc.ph_pembelian_detail',
                'qc.warna_pembelian_detail',
                'qc.keterangan_pembelian_detail',
                'qc.bentuk_pembelian_detail'
            )
            ->leftJoin('qc', function ($qc) {
                $qc->on('pd.id_pembelian', '=', 'qc.id_pembelian')->on('pd.id_barang', '=', 'qc.id_barang');
            })
            ->leftJoin('pembelian as p', 'pd.id_pembelian', '=', 'p.id_pembelian')
            ->leftJoin('barang as b', 'pd.id_barang', '=', 'b.id_barang')
            ->leftJoin('satuan_barang as sb', 'pd.id_satuan_barang', '=', 'sb.id_satuan_barang')
            ->whereBetween('p.tanggal_pembelian', [$date])
            ->whereIn('p.id_cabang', $idCabang);
        if ($statusQc != 'all') {
            $data = $data->where('qc.status_qc', $statusQc);
        }

        $data = $data->groupBy('pd.id_pembelian', 'pd.id_barang')
            ->orderBy('p.tanggal_pembelian', 'asc');

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }
}
