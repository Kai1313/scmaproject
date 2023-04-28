<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\MoveBranch;
use App\MoveBranchDetail;
use Illuminate\Http\Request;

class SendToBranchController extends Controller
{
    public $arrayStatus = ['0' => 'Belum Diterima', '1' => 'Diterima'];
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_kirim_ke_cabang', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = $this->getData($request);

            $html = '';
            $html .= view('report_ops.sendToBranch.template', [
                'datas' => $data,
                'arrayStatus' => $this->arrayStatus,
                'type' => $request->type,
            ]);
            return response()->json([
                'html' => $html,
            ]);
        }

        return view('report_ops.sendToBranch.index', [
            "pageTitle" => "SCA OPS | Laporan Kirim Ke Cabang | List",
            'arrayStatus' => $this->arrayStatus,
            'typeReport' => ['Rekap', 'Detail', 'Outstanding'],
        ]);
    }

    function print(Request $request) {
        if (checkAccessMenu('laporan_kirim_ke_cabang', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = $this->getData($request);
        $arrayCabang = [];
        foreach (session()->get('access_cabang') as $c) {
            $arrayCabang[$c['id']] = $c['text'];
        }

        $eCabang = explode(',', $request->id_cabang);
        $sCabang = [];
        foreach ($eCabang as $e) {
            $sCabang[] = $arrayCabang[$e];
        }

        return view('report_ops.sendToBranch.print', [
            "pageTitle" => "SCA OPS | Laporan Kirim Ke Cabang | Print",
            "datas" => $data,
            'arrayStatus' => $this->arrayStatus,
            'cabang' => implode(', ', $sCabang),
            'date' => $request->date,
            'type' => $request->type,
        ]);
    }

    public function getData($request)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);
        $idGudang = $request->id_gudang == 'all' ? '' : explode(',', $request->id_gudang);
        $status = $request->status;
        $type = $request->type;

        switch ($type) {
            case 'Rekap':
            case 'Detail':
                $data = MoveBranch::where('id_jenis_transaksi', '21')->whereBetween('tanggal_pindah_barang', $date)
                    ->whereIn('id_cabang', $idCabang)->where('void', 0);
                if ($idGudang) {
                    $data = $data->whereIn('id_gudang', $idGudang);
                }

                if ($status != 'all') {
                    $data = $data->where('status_pindah_barang', $status);
                }

                $data = $data->orderBy('tanggal_pindah_barang', 'asc')->get();
                break;
            case 'Outstanding':
                $data = MoveBranchDetail::whereHas('parent', function ($query) use ($idCabang, $idGudang, $status, $date) {
                    $query = $query->whereBetween('tanggal_pindah_barang', $date)
                        ->where('id_jenis_transaksi', 21)
                        ->whereIn('id_cabang', $idCabang)
                        ->where('void', 0);
                    if ($idGudang) {
                        $query = $query->whereIn('id_gudang', $idGudang);
                    }

                    if ($status != 'all') {
                        $query = $query->where('status_pindah_barang', $status);
                    }

                    return $query;
                })->where('status_diterima', 0)->get();
                break;
            default:
                $data = [];
                break;
        }

        return $data;
    }
}
