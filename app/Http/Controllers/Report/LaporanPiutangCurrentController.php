<?php

namespace App\Http\Controllers\Report;

use App\Exports\ReportLaporanPiutangCurrentExport;
use App\Http\Controllers\Controller;
use DB;
use Excel;
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

    public function print(Request $request)
    {
        if (checkAccessMenu('laporan_piutang_current', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request, 'print');
        $date = $request->dateReport;
        $idPelanggan = $request->id_pelanggan;
        $pelanggan = 'Semua Pelanggan';
        if ($idPelanggan != 'all') {
            $result = DB::table('pelanggan')->where('id_pelanggan', $idPelanggan)->first();
            if (!empty($result)) {
                $pelanggan = "({$result->kode_pelanggan}) {$result->nama_pelanggan}";
            }
        }
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
            'date' => $date,
            'pelanggan' => $pelanggan,
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
        $date = $request->dateReport;
        $idPelanggan = $request->id_pelanggan;
        $pelanggan = 'Semua Pelanggan';
        if ($idPelanggan != 'all') {
            $result = DB::table('pelanggan')->where('id_pelanggan', $idPelanggan)->first();
            if (!empty($result)) {
                $pelanggan = "({$result->kode_pelanggan}) {$result->nama_pelanggan}";
            }
        }
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
            'date' => $date,
            'pelanggan' => $pelanggan,
            'type' => $request->type,
        ];
        return Excel::download(new ReportLaporanPiutangCurrentExport('report_ops.laporanPiutangCurrent.excel', $array), 'LaporanPiutangSaatIni.xlsx');
    }

    public function getData($request, $type)
    {
        $date = $request->dateReport;
        $idPelanggan = $request->id_pelanggan;
        $idCabang = explode(',', $request->id_cabang);

        $joinJurnal = DB::table('jurnal_header as jh')
            ->select('jd.id_transaksi', DB::raw('ifnull(sum(jd.credit-jd.debet),0) as Total'), DB::raw('GROUP_CONCAT(concat(jh.tanggal_jurnal," ",kode_jurnal," Rp ",jd.credit) SEPARATOR " | ") as tanggal_jurnal'))
            ->leftJoin('jurnal_detail AS jd', function ($join) {
                $join->on('jh.id_jurnal', '=', 'jd.id_jurnal')
                    ->on(DB::Raw("ifnull(jd.id_transaksi,'')"), '<>', DB::Raw("''"));
            })
            ->leftJoin('saldo_transaksi AS st', 'st.id_transaksi', 'jd.id_transaksi')
            ->where('jh.void', 0)
            ->where('jh.tanggal_jurnal', '<=', $date)
            ->whereIn('st.tipe_transaksi', ['Penjualan', 'Retur Penjualan'])
            ->groupBy('jd.id_transaksi');
        if ($idPelanggan != 'all') {
            $joinJurnal->where('st.id_pelanggan', $idPelanggan);
        }

        $data = DB::table('saldo_transaksi as a')->Select(
            'pe.kode_pelanggan',
            'pe.nama_pelanggan',
            'a.id_transaksi',
            'p2.tanggal_penjualan',
            DB::raw('DATE_ADD(p2.tanggal_penjualan, INTERVAL p2.tempo_hari_penjualan DAY) as top'),
            'a.total as mtotal_penjualan',
            DB::raw('a.sisa as sisa'),
            DB::raw('ifnull(a.bayar,0) as bayar'),
            DB::raw('DATEDIFF("' . $date . '",DATE(DATE_ADD(p2.tanggal_penjualan, INTERVAL p2.tempo_hari_penjualan DAY))) as aging'),
            'a.tanggal',
            'p.tanggal_jurnal'
        )
            ->leftJoinSub($joinJurnal, 'p', function ($join) {
                $join->on('a.id_transaksi', '=', 'p.id_transaksi');
            })
            ->leftJoin('pelanggan as pe', 'pe.id_pelanggan', 'a.id_pelanggan')
            ->leftJoin('penjualan as p2', 'a.id_transaksi', 'p2.nama_penjualan')
            ->where('a.tanggal', '<=', $date)
            ->where(DB::raw('a.total-ifnull(p.total,0)'), '<>', 0)
            ->whereIn('a.tipe_transaksi', ['Penjualan', 'Retur Penjualan'])
            ->whereIn('p2.id_cabang', $idCabang)
        // ->where('a.sisa', '>', 0)
            ->orderBy('pe.nama_pelanggan', 'asc')->orderBy('p2.tanggal_penjualan', 'asc');
        if ($idPelanggan != 'all') {
            $data->where('a.id_pelanggan', $idPelanggan);
        }

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }
}
