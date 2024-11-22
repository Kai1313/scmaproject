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

        $data = DB::table('saldo_transaksi as a')->Select(
            'pe.kode_pemasok',
            'pe.nama_pemasok',
            'a.id_transaksi',
            'p2.tanggal_pembelian',
            DB::raw('DATE_ADD(p2.tanggal_pembelian, INTERVAL p2.tempo_hari_pembelian DAY) as top'),
            DB::raw('(a.total + a.uang_muka) as mtotal_pembelian'),
            DB::raw('ifnull((a.total+a.uang_muka)-(ifnull(a.bayar,0)+a.uang_muka),1) as sisa'),
            'a.bayar',
            DB::raw('if(ifnull((a.total+a.uang_muka)-(ifnull(a.bayar,0)+a.uang_muka),1) <> 0,DATEDIFF("' . $date . '",DATE(DATE_ADD(p2.tanggal_pembelian, INTERVAL p2.tempo_hari_pembelian DAY))),0) as aging'),
            'a.uang_muka',
            DB::raw('ifnull(a.bayar+a.uang_muka,0.00) as terbayar'),
            'p2.id_pembelian'
        )
            ->leftJoin('pemasok as pe', 'pe.id_pemasok', 'a.id_pemasok')
            ->leftJoin('pembelian as p2', 'a.id_transaksi', 'p2.nama_pembelian')
            ->where('a.tanggal', '<=', $date);
        if ($transactionStatus != 'all') {
            if ($transactionStatus == '1') {
                $data = $data->where(DB::raw('(a.total+a.uang_muka)-(a.bayar+a.uang_muka)'), 0);
            } else {
                $data = $data->where(DB::raw('(a.total+a.uang_muka)-(a.bayar+a.uang_muka)'), '<>', 0);
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
            $datatable = $datatable->editColumn('bayar', function ($row) {
                return $row->bayar != 0 ? '<a href="javascript:void(0)" data-id="' . $row->id_transaksi . '" data-transaksi="payment" class="show-payment">' . formatNumber2($row->bayar, 2) . '</a>' : formatNumber2($row->bayar, 2);
            })->editColumn('uang_muka', function ($row) {
                return $row->uang_muka != 0 ? '<a href="javascript:void(0)" data-id="' . $row->id_transaksi . '" data-transaksi="down_payment" class="show-payment">' . formatNumber2($row->uang_muka, 2) . '</a>' : formatNumber2($row->uang_muka, 2);
            })->editColumn('id_transaksi', function ($row) {
                return '<a href="' . env('OLD_URL_ROOT') . '#pembelian_invoice&data_master=' . $row->id_pembelian . '" target="_blank">' . $row->id_transaksi . '</a>';
            })->editColumn('aging', function ($row) {
                return $row->aging != 0 ? $row->aging : '';
            })->filterColumn('top', function ($query, $keyword) {
                $keywords = trim($keyword);
                $query->whereRaw("DATE_ADD(p2.tanggal_pembelian, INTERVAL p2.tempo_hari_pembelian DAY) like ?", ["%{$keywords}%"]);
            })->filterColumn('mtotal_pembelian', function ($query, $keyword) {
                $keywords = trim($keyword);
                $query->whereRaw("(a.total + a.uang_muka) like ?", ["%{$keywords}%"]);
            })->filterColumn('sisa', function ($query, $keyword) {
                $keywords = trim($keyword);
                $query->whereRaw("ifnull((a.total+a.uang_muka)-(a.bayar+a.uang_muka),0.00) like ?", ["%{$keywords}%"]);
            })->filterColumn('terbayar', function ($query, $keyword) {
                $keywords = trim($keyword);
                $query->whereRaw("ifnull(a.bayar+a.uang_muka,0.00) like ?", ["%{$keywords}%"]);
            })->filterColumn('aging', function ($query, $keyword) use ($date) {
                $keywords = trim($keyword);
                $q = "if(ifnull((a.total+a.uang_muka)-(ifnull(a.bayar,0)+a.uang_muka),1) <> 0, DATEDIFF(" . $date . ",DATE(DATE_ADD(p2.tanggal_pembelian, INTERVAL p2.tempo_hari_pembelian DAY))),0)";
                $query->whereRaw($q . ' like ?', ["%{$keywords}%"]);
            });

            $datatable = $datatable->rawColumns(['bayar', 'id_transaksi', 'aging', 'uang_muka'])->make(true);
            return $datatable;
        }

        $data = $data->get();
        return $data;
    }

    public function getJournal(Request $request)
    {
        $idTransaksi = $request->id_transaksi;
        $transactionType = $request->transaction;
        if ($transactionType == 'down_payment') {
            $pembelian = DB::table('pembelian')->select('nomor_po_pembelian')->where('nama_pembelian', $idTransaksi)->first();
            if (!$pembelian) {
                return response(['status' => 'error', 'message' => 'Penerimaan tidak ditemukan'], 500);
            }

            $uangMuka = DB::table('uang_muka_pembelian as u')
                ->join('permintaan_pembelian as p', 'u.id_permintaan_pembelian', 'p.id_permintaan_pembelian')
                ->where('nama_permintaan_pembelian', $pembelian->nomor_po_pembelian)->pluck('kode_uang_muka_pembelian');

            $idTransaksi = $uangMuka;
        } else {
            $idTransaksi = [$idTransaksi];
        }

        $datas = DB::table('jurnal_detail as jd')->select('jh.kode_jurnal', 'jh.tanggal_jurnal', 'jd.debet', 'jh.id_jurnal', 'jh.jenis_jurnal', 'jd.keterangan')
            ->join('jurnal_header as jh', 'jd.id_jurnal', 'jh.id_jurnal')
            ->whereIn('jd.id_transaksi', $idTransaksi)
            ->where('jh.void', '0')
            ->where('jh.id_transaksi', null)
            ->get();
        $html = '';
        $sum = 0;
        foreach ($datas as $key => $data) {
            $link = route('transaction-general-ledger-show', $data->id_jurnal);
            $html .= '<tr><td class="text-center">' . ($key + 1) . '</td>';
            $html .= '<td><a href="' . $link . '" target="_blank">' . $data->kode_jurnal . '</a></td>';
            $html .= '<td class="text-center">' . $data->tanggal_jurnal . '</td>';
            $html .= '<td class="text-right">' . formatNumber2($data->debet, 2) . '</td></tr>';
            $sum += $data->debet;
        }

        $html .= '<tr><td colspan="3" class="text-right"><b>Total</b></td><td class="text-right">' . formatNumber2($sum, 2) . '</td></tr>';

        if (count($datas) == 0) {
            $html .= '<tr><td colspan="4">Pembayaran tidak ditemukan</td></tr>';
        }

        return response()->json(['status' => 'success', 'html' => $html, 'datas' => $datas], 200);
    }
}
