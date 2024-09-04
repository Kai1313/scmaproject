<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LaporanPrController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'report_pr-index', 'show') == false) {
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
        $data = DB::table('purchase_request_detail as pd')->select(
            'g.nama_gudang',
            'purchase_request_date',
            'purchase_request_code',
            'b.nama_barang',
            'notes',
            'sb.nama_satuan_barang',
            'qty',
            'tanggal_permintaan_pembelian',
            'nama_permintaan_pembelian',
            'jumlah_permintaan_pembelian_detail'
        )
            ->join('purchase_request_header as ph', 'pd.purchase_request_id', 'ph.purchase_request_id')
            ->leftJoin('permintaan_pembelian_detail as ppd', function ($i) {
                $i->on('ppd.purchase_request_id', 'ph.purchase_request_id')->whereRaw('ppd.index = pd.index');
            })
            ->leftJoin('permintaan_pembelian as pph', 'ppd.id_permintaan_pembelian', 'pph.id_permintaan_pembelian')
            ->join('gudang as g', 'ph.id_gudang', 'g.id_gudang')
            ->join('barang as b', 'pd.id_barang', 'b.id_barang')
            ->join('satuan_barang as sb', 'pd.id_satuan_barang', 'sb.id_satuan_barang')
            ->whereBetween('purchase_request_date', $date)
            ->whereIn('ph.id_cabang', $idCabang)
            ->whereIn('ph.id_gudang', $idGudang)
            ->orderBy('ph.purchase_request_date', 'desc');

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }
}
