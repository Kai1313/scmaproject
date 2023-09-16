<?php

namespace App\Http\Controllers;

use App\Barang;
use App\Cabang;
use App\Exports\ReportStokMinHistoryExport;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;

class StokMinHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getExcel(Request $request)
    {
        // if (checkUserSession($request, 'stok_minimal-excel', 'show') == false) {
        //     return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        // }

        $historyHeader = \DB::table('stok_minimal_hitung')
            ->where('id_barang', $request->id)
            ->where('id_cabang', $request->id_cabang)
            ->orderBy('id', 'DESC')->first();

        $sumStok = \DB::table('master_qr_code')
            ->where('id_barang', $request->id)->value(\DB::raw('sum(sisa_master_qr_code) as stok_aktif'));
        $barang = Barang::find($request->id);
        $cabang = Cabang::find($request->id_cabang);
        $data = \DB::table('stok_minimal_hitung_detil')
            ->where('stok_minimal_hitung_id', $historyHeader->id)->orderBy('id', 'asc')->get();
        $historyHeader->penj_dari = Carbon::createFromFormat('Y-m-d', $historyHeader->penj_dari)
            ->format('d/m/Y');
        $historyHeader->penj_sampai = Carbon::createFromFormat('Y-m-d', $historyHeader->penj_sampai)
            ->format('d/m/Y');
        $array = [
            'historyHeader' => $historyHeader,
            'sumStok' => $sumStok,
            'cabang' => $cabang,
            'barang' => $barang,
            "datas" => $data,
        ];
        return Excel::download(new ReportStokMinHistoryExport('report_ops.stokMinHistory.excel', $array), 'StokMinHistory.xlsx');
    }
}
