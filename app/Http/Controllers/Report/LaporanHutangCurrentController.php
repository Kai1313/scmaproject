<?php

namespace App\Http\Controllers\Report;

use App\Exports\ReportLaporanHutangCurrentExport;
use App\Http\Controllers\Controller;
use DB;
use Excel;
use Illuminate\Http\Request;
use PDF;
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

    public function print(Request $request)
    {
        if (checkAccessMenu('laporan_hutang_current', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request, 'print');
        $date = $request->dateReport;
        $idPemasok = $request->id_pemasok;
        $pemasok = 'Semua Pemasok';
        if ($idPemasok != 'all') {
            $result = DB::table('pemasok')->where('id_pemasok', $idPemasok)->first();
            if (!empty($result)) {
                $pemasok = "({$result->kode_pemasok}) {$result->nama_pemasok}";
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
            'pemasok' => $pemasok,
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
        $date = $request->dateReport;
        $idPemasok = $request->id_pemasok;
        $pemasok = 'Semua Pemasok';
        if ($idPemasok != 'all') {
            $result = DB::table('pemasok')->where('id_pemasok', $idPemasok)->first();
            if (!empty($result)) {
                $pemasok = "({$result->kode_pemasok}) {$result->nama_pemasok}";
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
            'pemasok' => $pemasok,
            'type' => $request->type,
        ];
        return Excel::download(new ReportLaporanHutangCurrentExport('report_ops.laporanHutangCurrent.excel', $array), 'LaporanHutangSaatIni.xlsx');
    }

    public function getData($request, $type)
    {
        $date = $request->dateReport;
        $idPemasok = $request->id_pemasok;
        $idCabang = explode(',', $request->id_cabang);
        $transactionStatus = $request->transaction_status;

        $joinJurnal = DB::table('jurnal_header as jh')
            ->select('jd.id_transaksi', DB::raw('ifnull(sum(jd.debet-jd.credit),0) as Total'))
            ->leftJoin('jurnal_detail AS jd', function ($join) {
                $join->on('jh.id_jurnal', '=', 'jd.id_jurnal')
                    ->on(DB::Raw("ifnull(jd.id_transaksi,'')"), '<>', DB::Raw("''"));
            })
            ->leftJoin('saldo_transaksi AS st', 'st.id_transaksi', 'jd.id_transaksi')
            ->where('jh.void', 0)
            ->where('jh.tanggal_jurnal', '<=', $date)
            ->whereIn('st.tipe_transaksi', ['Pembelian', 'Retur Pembelian'])
            ->groupBy('jd.id_transaksi');
        if ($idPemasok != 'all') {
            $joinJurnal->where('st.id_pemasok', $idPemasok);
        }

        $data = DB::table('saldo_transaksi as a')->Select(
            'pe.kode_pemasok',
            'pe.nama_pemasok',
            'a.id_transaksi',
            'p2.tanggal_pembelian',
            DB::raw('DATE_ADD(p2.tanggal_pembelian, INTERVAL p2.tempo_hari_pembelian DAY) as top'),
            'a.total as mtotal_pembelian',
            DB::raw('(a.total+a.uang_muka)-ifnull(p.total,0) as sisa'),
            DB::raw('ifnull(p.Total,0) as bayar'),
            DB::raw('DATEDIFF("' . $date . '",DATE(DATE_ADD(p2.tanggal_pembelian, INTERVAL p2.tempo_hari_pembelian DAY))) as aging'),
            'a.uang_muka')
            ->leftJoinSub($joinJurnal, 'p', function ($join) {
                $join->on('a.id_transaksi', '=', 'p.id_transaksi');
            })
            ->leftJoin('pemasok as pe', 'pe.id_pemasok', 'a.id_pemasok')
            ->leftJoin('pembelian as p2', 'a.id_transaksi', 'p2.nama_pembelian')
            ->where('a.tanggal', '<=', $date);
        if ($transactionStatus != 'all') {
            if ($transactionStatus == '1') {
                $data = $data->where(DB::raw('a.total-ifnull(p.total,0)'), 0);
            } else {
                $data = $data->where(DB::raw('a.total-ifnull(p.total,0)'), '<>', 0);
            }
        }

        $data = $data->whereIn('a.tipe_transaksi', ['Pembelian', 'Retur Pembelian'])
            ->whereIn('p2.id_cabang', $idCabang);

        if ($idPemasok != 'all') {
            $data->where('a.id_pemasok', $idPemasok);
        }

        if ($type == 'print') {
            $data = $data->orderBy('p2.tanggal_pembelian', 'asc');
        }

        if ($type == 'datatable') {
            $datatable = Datatables::of($data);
            $datatable = $datatable->filterColumn('top', function ($query, $keyword) {
                $keywords = trim($keyword);
                $query->whereRaw("DATE_ADD(p2.tanggal_pembelian, INTERVAL p2.tempo_hari_pembelian DAY) like ?", ["%{$keywords}%"]);
            })->filterColumn('mtotal_pembelian', function ($query, $keyword) {
                $keywords = trim($keyword);
                $query->whereRaw("a.total like ?", ["%{$keywords}%"]);
            })->filterColumn('sisa', function ($query, $keyword) {
                $keywords = trim($keyword);
                $query->whereRaw("(a.total+a.uang_muka)-ifnull(p.total,0) like ?", ["%{$keywords}%"]);
            })->filterColumn('bayar', function ($query, $keyword) {
                $keywords = trim($keyword);
                $query->whereRaw("ifnull(p.Total,0) like ?", ["%{$keywords}%"]);
            })->filterColumn('aging', function ($query, $keyword) use ($date) {
                $keywords = trim($keyword);
                $q = "DATEDIFF(" . $date . ",DATE(DATE_ADD(p2.tanggal_pembelian, INTERVAL p2.tempo_hari_pembelian DAY)))";
                $query->whereRaw($q . ' like ?', ["%{$keywords}%"]);
            });

            $datatable = $datatable->make(true);

            return $datatable;
        }

        $data = $data->get();
        return $data;
    }
}
