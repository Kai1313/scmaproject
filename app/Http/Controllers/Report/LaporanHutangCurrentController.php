<?php

namespace App\Http\Controllers\Report;
use App\Exports\ReportLaporanHutangCurrentExport;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use PDF;
use Excel;
use Yajra\DataTables\DataTables;

class LaporanHutangCurrentController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_hutang_current', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getData($request, 'datatable');
        }

        return view('report_ops.laporanHutangCurrent.index', [
            "pageTitle" => "SCA OPS | Laporan Hutang Saat Ini | List",
            'typeReport' => ['Rekap', 'Detail'],
        ]);
    }

    function print(Request $request) {
        if (checkAccessMenu('laporan_hutang_current', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request, 'print');
        // dd($data);
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
            'date' => date('Y-m-d'),
            'type' => $request->type,
        ];

        $pdf = PDF::loadView('report_ops.laporanHutangCurrent.print', $array);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('LaporanHutangSaatIni.pdf');
    }

    public function getExcel(Request $request)
    {
        if (checkAccessMenu('laporan_hutang_current', 'print') == false) {
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
            'date' => date('Y-m-d'),
            'type' => $request->type,
        ];
        return Excel::download(new ReportLaporanHutangCurrentExport('report_ops.laporanHutangCurrent.excel', $array), 'LaporanHutangSaatIni.xlsx');
    }

    public function getData($request, $type)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);

        $data = DB::table('saldo_transaksi as a')->Select(
            'p.kode_pemasok',
            'p.nama_pemasok',
            'a.id_transaksi',
            'p2.tanggal_pembelian',
            DB::Raw('DATE_ADD(p2.tanggal_pembelian, INTERVAL p2.tempo_hari_pembelian DAY) as top'),
            'p2.mtotal_pembelian',
            'a.sisa',
            DB::Raw('0 as sisa_tax'),
            DB::Raw('DATEDIFF(NOW(),DATE_ADD(p2.tanggal_pembelian, INTERVAL p2.tempo_hari_pembelian DAY)) as aging'),
            )
            ->join('pemasok as p', 'p.id_pemasok', 'a.id_pemasok')
            ->join('pembelian as p2', 'a.id_transaksi', 'p2.nama_pembelian')
            ->where('a.sisa','>', 0)
            ->whereIn('a.tipe_transaksi',['Pembelian','Retur Pembelian'])
            ->whereIn('p2.id_cabang',$idCabang)
            ->orderBy('p.nama_pemasok', 'asc');

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }
}
