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
        $transactionStatus = $request->transaction_status;

        $data = DB::table('saldo_transaksi as a')->Select(
            'pe.kode_pelanggan',
            'pe.nama_pelanggan',
            'a.id_transaksi',
            'p2.tanggal_penjualan',
            DB::raw('DATE_ADD(p2.tanggal_penjualan, INTERVAL p2.tempo_hari_penjualan DAY) as top'),
            DB::raw('(a.total + a.uang_muka) as mtotal_penjualan'),
            DB::raw('ifnull((a.total+a.uang_muka)-(ifnull(a.bayar,0)+a.uang_muka),0) as sisa'),
            'a.bayar',
            DB::raw('if(ifnull((a.total+a.uang_muka)-(ifnull(a.bayar,0)+a.uang_muka),0) <> 0,DATEDIFF("' . $date . '",DATE(DATE_ADD(p2.tanggal_penjualan, INTERVAL p2.tempo_hari_penjualan DAY))),0) as aging'),
            'a.uang_muka',
            DB::raw('ifnull(a.bayar+a.uang_muka,0.00) as terbayar'),
            'p2.id_penjualan'
        )
            ->leftJoin('pelanggan as pe', 'pe.id_pelanggan', 'a.id_pelanggan')
            ->leftJoin('penjualan as p2', 'a.id_transaksi', 'p2.nama_penjualan')
            ->where('a.tanggal', '<=', $date);
        if ($transactionStatus != 'all') {
            if ($transactionStatus == '1') {
                $data = $data->where(DB::raw('(a.total+a.uang_muka)-(a.bayar+a.uang_muka)'), 0);
            } else {
                $data = $data->where(DB::raw('(a.total+a.uang_muka)-(a.bayar+a.uang_muka)'), '<>', 0);
            }
        }

        $data = $data->whereIn('a.tipe_transaksi', ['Penjualan', 'Retur Penjualan'])
            ->whereIn('p2.id_cabang', $idCabang);
        if ($idPelanggan != 'all') {
            $data->where('a.id_pelanggan', $idPelanggan);
        }

        if ($type == 'print') {
            $data = $data->orderBy('p2.tanggal_penjualan', 'asc');
        }

        if ($type == 'datatable') {
            $datatable = Datatables::of($data);
            $datatable = $datatable->editColumn('bayar', function ($row) {
                return $row->bayar != 0 ? '<a href="javascript:void(0)" data-id="' . $row->id_transaksi . '" class="show-payment" data-transaksi="payment">' . formatNumber2($row->bayar, 2) . '</a>' : formatNumber2($row->bayar, 2);
            })->editColumn('uang_muka', function ($row) {
                return $row->uang_muka != 0 ? '<a href="javascript:void(0)" data-id="' . $row->id_transaksi . '" class="show-payment" data-transaksi="down_payment">' . formatNumber2($row->uang_muka, 2) . '</a>' : formatNumber2($row->uang_muka, 2);
            })->editColumn('id_transaksi', function ($row) {
                return '<a href="' . env('OLD_URL_ROOT') . '#penjualan_faktur&data_master=' . $row->id_penjualan . '" target="_blank">' . $row->id_transaksi . '</a>';
            })->editColumn('aging', function ($row) {
                return $row->aging != 0 ? $row->aging : '';
            })->filterColumn('top', function ($query, $keyword) {
                $keywords = trim($keyword);
                $query->whereRaw("DATE_ADD(p2.tanggal_penjualan, INTERVAL p2.tempo_hari_penjualan DAY) like ?", ["%{$keywords}%"]);
            })->filterColumn('mtotal_penjualan', function ($query, $keyword) {
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
                $q = "if(ifnull((a.total+a.uang_muka)-(ifnull(a.bayar,0)+a.uang_muka),0) <> 0,DATEDIFF(" . $date . ",DATE(DATE_ADD(p2.tanggal_penjualan, INTERVAL p2.tempo_hari_penjualan DAY))),0)";
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
            $pembelian = DB::table('penjualan')->select('nomor_so_penjualan')->where('nama_penjualan', $idTransaksi)->first();
            if (!$pembelian) {
                return response(['status' => 'error', 'message' => 'Penjualan tidak ditemukan'], 500);
            }

            $uangMuka = DB::table('uang_muka_penjualan as u')
                ->join('permintaan_penjualan as p', 'u.id_permintaan_penjualan', 'p.id_permintaan_penjualan')
                ->where('nama_permintaan_penjualan', $pembelian->nomor_so_penjualan)->pluck('kode_uang_muka_penjualan');

            $idTransaksi = $uangMuka;
        } else {
            $idTransaksi = [$idTransaksi];
        }

        $datas = DB::table('jurnal_detail as jd')->select('jh.kode_jurnal', 'jh.tanggal_jurnal', 'jd.credit', 'jh.id_jurnal', 'jh.jenis_jurnal')
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
            $html .= '<td class="text-right">' . formatNumber2($data->credit, 2) . '</td></tr>';
            $sum += $data->credit;
        }

        $html .= '<tr><td colspan="3" class="text-right"><b>Total</b></td><td class="text-right">' . formatNumber2($sum, 2) . '</td></tr>';

        if (count($datas) == 0) {
            $html .= '<tr><td colspan="4">Pembayaran tidak ditemukan</td></tr>';
        }

        return response()->json(['html' => $html], 200);
    }
}
