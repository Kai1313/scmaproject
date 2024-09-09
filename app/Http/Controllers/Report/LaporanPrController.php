<?php

namespace App\Http\Controllers\Report;

use App\Exports\ReportPurchaseRequestExport;
use App\Http\Controllers\Controller;
use DB;
use Excel;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LaporanPrController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_pr', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getData($request, 'datatable');
        }
        return view('report_ops.laporanPr.index', [
            "pageTitle" => "SCA OPS | Laporan Purchase Request | List",
            'typeReport' => ['Detail'],
        ]);
    }

    public function getData($request, $type)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);
        $idGudang = explode(',', $request->id_gudang);
        $poStatus = $request->po_status;
        $data = DB::table('purchase_request_detail as pd')->select(
            'g.nama_gudang',
            'ph.purchase_request_date',
            'ph.purchase_request_code',
            'b.nama_barang',
            'pd.notes',
            'sb.nama_satuan_barang',
            'pd.qty',
            'pph.tanggal_permintaan_pembelian',
            'pph.nama_permintaan_pembelian',
            'ppd.jumlah_permintaan_pembelian_detail',
            'pengguna.nama_pengguna'
        )
            ->join('purchase_request_header as ph', 'pd.purchase_request_id', 'ph.purchase_request_id')
            ->leftJoin('permintaan_pembelian_detail as ppd', function ($i) {
                $i->on('ppd.purchase_request_id', 'ph.purchase_request_id')->whereRaw('ppd.index = pd.index');
            })
            ->leftJoin('permintaan_pembelian as pph', 'ppd.id_permintaan_pembelian', 'pph.id_permintaan_pembelian')
            ->join('gudang as g', 'ph.id_gudang', 'g.id_gudang')
            ->join('barang as b', 'pd.id_barang', 'b.id_barang')
            ->join('satuan_barang as sb', 'pd.id_satuan_barang', 'sb.id_satuan_barang')
            ->join('pengguna', 'ph.purchase_request_user_id', 'pengguna.id_pengguna');
        if (count($date) > 1) {
            $data = $data->whereBetween('purchase_request_date', $date);
        }

        $data = $data->whereIn('ph.id_cabang', $idCabang)
            ->whereIn('ph.id_gudang', $idGudang);
        if ($poStatus != 'all') {
            if ($poStatus == '1') {
                $data = $data->where('nama_permintaan_pembelian', '!=', null);
            } else {
                $data = $data->where('nama_permintaan_pembelian', null);
            }
        }

        $data = $data->orderBy('ph.purchase_request_date', 'desc');

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }

    public function getExcel(Request $request)
    {
        if (checkAccessMenu('laporan_pr', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request, 'print');

        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);
        $idGudang = explode(',', $request->id_gudang);
        $poStatus = $request->po_status;

        $array = [
            "datas" => $data,
            'date' => $request->date,
            'cabang' => $request->id_cabang,
            'gudang' => $request->id_gudang,
            'po_status' => $request->po_status,
        ];

        // return view('report_ops.laporanPr.excel', $array);
        return Excel::download(new ReportPurchaseRequestExport('report_ops.laporanPr.excel', $array), 'laporan purchase request.xlsx');
    }
}
