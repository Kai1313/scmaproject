<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\PurchaseDownPayment;
use App\TransactionBalance;
use DB;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;

class LaporanPiutangCurrentController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_piutang_current', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getData($request, 'datatable');
        }

        return view('report_ops.laporanPiutangCurrent.index', [
            "pageTitle" => "SCA OPS | Laporan Piutang Saat Ini | List",
            'typeReport' => ['Rekap', 'Detail'],
        ]);
    }

    function print(Request $request) {
        if (checkAccessMenu('laporan_piutang_current', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request, 'print');
        $arrayCabang = [];
        foreach (session()->get('access_cabang') as $c) {
            $arrayCabang[$c['id']] = $c['text'];
        }
        // dd($data);
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
        ];

        $pdf = PDF::loadView('report_ops.laporanPiutangCurrent.print', $array);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('LaporanPiutangSaatIni.pdf');
    }

    public function getExcel(Request $request)
    {
        if (checkAccessMenu('laporan_piutang_current', 'print') == false) {
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
        ];
        return Excel::download(new ReportPurchaseDownPaymentExport('report_ops.purchaseDownPayment.excel', $array), 'laporan uang muka pembelian.xlsx');
    }

    public function getData($request, $type)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);

        $data = DB::table('saldo_transaksi as a')->Select(
            'p.kode_pelanggan',
            'p.nama_pelanggan',
            'a.id_transaksi',
            'p2.tanggal_penjualan',
            DB::Raw('DATE_ADD(p2.tanggal_penjualan, INTERVAL p2.tempo_hari_penjualan DAY) as top'),
            'p2.mtotal_penjualan',
            'a.sisa',
            DB::Raw('0 as sisa_tax'),
            DB::Raw('DATEDIFF(NOW(),DATE_ADD(p2.tanggal_penjualan, INTERVAL p2.tempo_hari_penjualan DAY)) as aging'),
            )
            ->join('pelanggan as p', 'p.id_pelanggan', 'a.id_pelanggan')
            ->join('penjualan as p2', 'a.id_transaksi', 'p2.nama_penjualan')
            ->where('a.sisa','>', 0)
            ->whereIn('a.tipe_transaksi',['Penjualan','Retur Penjualan'])
            ->whereIn('p2.id_cabang',$idCabang)
            ->orderBy('p.nama_pelanggan', 'asc');

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }
}
