<?php

namespace App\Http\Controllers\Report;

use App\Exports\ReportQcReceivedExport;
use App\Http\Controllers\Controller;
use DB;
use Excel;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;

class QcReceivedController extends Controller
{
    public $arrayStatus = ['' => 'Pending', '1' => 'Passed', '2' => 'Reject', '3' => 'Hold'];
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

    public function print(Request $request)
    {
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

        $array = [
            "datas" => $data,
            'cabang' => implode(', ', $sCabang),
            'date' => $request->date,
            'type' => $request->type,
            'status' => $request->status_qc,
        ];

        $pdf = PDF::loadView('report_ops.qualityControl.print', $array);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('laporan QC penerimaan.pdf');
    }

    public function getExcel(Request $request)
    {
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

        $array = [
            "datas" => $data,
            'cabang' => implode(', ', $sCabang),
            'date' => $request->date,
            'type' => $request->type,
            'status' => $request->status_qc,
        ];
        return Excel::download(new ReportQcReceivedExport('report_ops.qualityControl.excel', $array), 'laporan QC Penerimaan.xlsx');
    }

    public function getData($request, $type)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);
        $statusQc = $request->status_qc;
        $idBarang = $request->id_barang;

        $data = DB::table('pembelian_detail as pd')
            ->select(
                'pd.id_pembelian_detail',
                'qc.tanggal_qc',
                'p.tanggal_pembelian',
                'p.nama_pembelian',
                'b.nama_barang',
                DB::raw('sum(pd.jumlah_purchase) as total_jumlah_purchase'),
                'status_qc',
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
            ->where('b.status_stok_barang', '1')->where('b.needqc', '1')
            ->whereIn('p.id_cabang', $idCabang);
        if ($statusQc != 'all') {
            $data = $data->where('qc.status_qc', $statusQc);
        }

        if (count($date) > 1) {
            $data = $data->whereBetween('p.tanggal_pembelian', [$date]);
        }

        if ($idBarang) {
            $data = $data->where('b.id_barang', $idBarang);
        }

        $data = $data->groupBy('pd.id_pembelian', 'pd.id_barang', 'status_qc')
            ->orderBy('p.tanggal_pembelian', 'desc');

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }

    public function autocompleteItem(Request $request)
    {
        $search = $request->search;
        $datas = DB::table('barang')->select('nama_barang as text', 'id_barang as id');
        if ($search) {
            $datas = $datas->where('nama_barang', 'like', '%' . $search . '%');
        }

        $datas = $datas->where('status_barang', '1')
            ->where('status_stok_barang', '1')->limit(20)->get();
        return response()->json($datas);
    }
}
