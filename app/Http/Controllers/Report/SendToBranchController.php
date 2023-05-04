<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SendToBranchController extends Controller
{
    public $arrayStatus = ['0' => 'Belum Diterima', '1' => 'Diterima'];
    public function index(Request $request)
    {
        if (checkUserSession($request, 'laporan_kirim_ke_cabang', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getData($request, 'datatable');
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

        return view('report_ops.sendToBranch.print', [
            "pageTitle" => "SCA OPS | Laporan Kirim Ke Cabang | Print",
            "datas" => $data,
            'arrayStatus' => $this->arrayStatus,
            'cabang' => implode(', ', $sCabang),
            'date' => $request->date,
            'type' => $request->type,
        ]);
    }

    public function getData($request, $type)
    {
        $date = explode(' - ', $request->date);
        $idCabang = explode(',', $request->id_cabang);
        $idGudang = explode(',', $request->id_gudang);
        $status = $request->status;
        $reportType = $request->type;

        switch ($reportType) {
            case 'Rekap':
                $data = DB::table('pindah_barang as pb')->select(
                    'pb.tanggal_pindah_barang',
                    'pb.kode_pindah_barang',
                    'c.nama_cabang',
                    'g.nama_gudang',
                    'c2.nama_cabang as nama_cabang2',
                    'pb.keterangan_pindah_barang',
                    'pb.transporter',
                    'pb.nomor_polisi',
                    DB::raw('(CASE 
                        WHEN status_pindah_barang = "0" 
                    )')'pb.status_pindah_barang'
                )
                    ->leftJoin('cabang as c', 'pb.id_cabang', 'c.id_cabang')
                    ->leftJoin('gudang as g', 'pb.id_gudang', 'g.id_gudang')
                    ->leftJoin('cabang as c2', 'pb.id_cabang2', 'c2.id_cabang')
                    ->whereBetween('pb.tanggal_pindah_barang', $date)
                    ->whereIn('pb.id_cabang', $idCabang)->whereIn('pb.id_gudang', $idGudang)
                    ->orderBy('pb.tanggal_pindah_barang', 'asc');

                if ($status != 'all') {
                    $data = $data->where('pb.status_pindah_barang', $status);
                }

                $data = $data->orderBy('pb.tanggal_pindah_barang', 'asc');
                break;
            case 'Detail':
                //belum
                $data = DB::table('pindah_barang as pb')->select(
                    'tanggal_pindah_barang',
                    'kode_pindah_barang',
                    'c.nama_cabang',
                    'g.nama_gudang',
                    'c2.nama_cabang',
                    'keterangan_pindah_barang',
                    'transporter',
                    'nomor_polisi',
                    'status_pindah_barang'
                )
                    ->leftJoin('cabang as c', 'pb.id_cabang', 'c.id_cabang')
                    ->leftJoin('gudang as g', 'mu.id_gudang', 'g.id_gudang')
                    ->leftJoin('cabang as c2', 'mu.id_cabang2', 'c2.id_cabang')
                    ->whereBetween('tanggal_pindah_barang', $date)
                    ->whereIn('pb.id_cabang', $idCabang)->whereIn('pb.id_gudang', $idGudang)
                    ->orderBy('tanggal_pindah_barang', 'asc');

                if ($status != 'all') {
                    $data = $data->where('status_pindah_barang', $status);
                }

                $data = $data->orderBy('tanggal_pindah_barang', 'asc');
                break;
            case 'Outstanding':
                //belum
                $data = DB::table('pindah_barang as pb')->select(
                    'tanggal_pindah_barang',
                    'kode_pindah_barang',
                    'c.nama_cabang',
                    'g.nama_gudang',
                    'c2.nama_cabang',
                    'keterangan_pindah_barang',
                    'transporter',
                    'nomor_polisi',
                    'status_pindah_barang'
                )
                    ->leftJoin('cabang as c', 'pb.id_cabang', 'c.id_cabang')
                    ->leftJoin('gudang as g', 'mu.id_gudang', 'g.id_gudang')
                    ->leftJoin('cabang as c2', 'mu.id_cabang2', 'c2.id_cabang')
                    ->whereBetween('tanggal_pindah_barang', $date)
                    ->whereIn('pb.id_cabang', $idCabang)->whereIn('pb.id_gudang', $idGudang)
                    ->orderBy('tanggal_pindah_barang', 'asc');

                if ($status != 'all') {
                    $data = $data->where('status_pindah_barang', $status);
                }

                $data = $data->orderBy('tanggal_pindah_barang', 'asc');
                break;
            default:
                $data = [];
                break;
        }

        if ($type == 'datatable') {
            return Datatables::of($data)
                ->toJson();
        }

        $data = $data->get();
        return $data;
    }
}
