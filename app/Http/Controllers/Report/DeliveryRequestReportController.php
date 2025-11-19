<?php
namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeliveryRequestReportController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'delivery_request_report', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            return $this->getData($request, 'datatable');
        }

        return view('report_ops.deliveryRequestReport.index', [
            "pageTitle" => "SCA OPS | Laporan Permintaan Pengiriman | List",
        ]);
    }

    public function getData($request, $type)
    {
        $date       = explode(' - ', $request->date);
        $idCabang   = explode(',', $request->id_cabang);
        $idGudang   = explode(',', $request->id_gudang);
        $status     = $request->status;
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
                        WHEN status_pindah_barang = "0" THEN "Dalam Perjalanan"
                        WHEN status_pindah_barang = "1" THEN "Diterima"
                        ELSE ""
                    END) AS status_pindah_barang')
                )
                    ->join('cabang as c', 'pb.id_cabang', 'c.id_cabang')
                    ->join('gudang as g', 'pb.id_gudang', 'g.id_gudang')
                    ->join('cabang as c2', 'pb.id_cabang2', 'c2.id_cabang')
                    ->where('id_jenis_transaksi', '21')->where('void', 0)
                    ->whereBetween('pb.tanggal_pindah_barang', $date)
                    ->where(function ($w) use ($idCabang) {
                        $w->whereIn('pb.id_cabang', $idCabang)->orWhereIn('pb.id_cabang2', $idCabang);
                    })
                    ->orderBy('pb.tanggal_pindah_barang', 'asc');

                if ($status != 'all') {
                    $data = $data->where('pb.status_pindah_barang', $status);
                }

                if ($request->id_gudang) {
                    $data = $data->whereIn('pb.id_gudang', $idGudang);
                }

                $data = $data->orderBy('pb.tanggal_pindah_barang', 'asc');
                break;
            case 'Detail':
                $data = DB::table('pindah_barang_detail as pbd')->select(
                    'pb.tanggal_pindah_barang',
                    'pb.kode_pindah_barang',
                    'c.nama_cabang',
                    'g.nama_gudang',
                    'c2.nama_cabang as nama_cabang2',
                    'pbd.qr_code',
                    'b.nama_barang',
                    'sb.nama_satuan_barang',
                    'pbd.qty',
                    'pbd.batch',
                    DB::raw('(CASE
                        WHEN status_diterima = "0" THEN "Belum Diterima"
                        WHEN status_diterima = "1" THEN "Sudah Diterima"
                        ELSE ""
                    END) AS status_diterima')
                )
                    ->join('pindah_barang as pb', 'pbd.id_pindah_barang', 'pb.id_pindah_barang')
                    ->join('barang as b', 'pbd.id_barang', 'b.id_barang')
                    ->join('cabang as c', 'pb.id_cabang', 'c.id_cabang')
                    ->join('gudang as g', 'pb.id_gudang', 'g.id_gudang')
                    ->join('cabang as c2', 'pb.id_cabang2', 'c2.id_cabang')
                    ->join('satuan_barang as sb', 'pbd.id_satuan_barang', 'sb.id_satuan_barang')
                    ->where('id_jenis_transaksi', '21')->where('void', 0)
                    ->whereBetween('pb.tanggal_pindah_barang', $date)
                    ->where(function ($w) use ($idCabang) {
                        $w->whereIn('pb.id_cabang', $idCabang)->orWhereIn('pb.id_cabang2', $idCabang);
                    })
                    ->orderBy('pb.tanggal_pindah_barang', 'asc');

                if ($status != 'all') {
                    $data = $data->where('pb.status_pindah_barang', $status);
                }

                if ($request->id_gudang) {
                    $data = $data->whereIn('pb.id_gudang', $idGudang);
                }

                $data = $data->orderBy('pb.tanggal_pindah_barang', 'asc');
                break;
            case 'Outstanding':
                $data = DB::table('pindah_barang_detail as pbd')->select(
                    'pb.tanggal_pindah_barang',
                    'pb.kode_pindah_barang',
                    'c.nama_cabang',
                    'g.nama_gudang',
                    'c2.nama_cabang as nama_cabang2',
                    'pbd.qr_code',
                    'b.nama_barang',
                    'sb.nama_satuan_barang',
                    'pbd.qty',
                    'pbd.batch',
                    DB::raw('(CASE
                        WHEN status_diterima = "0" THEN "Belum Diterima"
                        WHEN status_diterima = "1" THEN "Sudah Diterima"
                        ELSE ""
                    END) AS status_diterima')
                )
                    ->join('pindah_barang as pb', 'pbd.id_pindah_barang', 'pb.id_pindah_barang')
                    ->join('barang as b', 'pbd.id_barang', 'b.id_barang')
                    ->join('cabang as c', 'pb.id_cabang', 'c.id_cabang')
                    ->join('gudang as g', 'pb.id_gudang', 'g.id_gudang')
                    ->join('cabang as c2', 'pb.id_cabang2', 'c2.id_cabang')
                    ->join('satuan_barang as sb', 'pbd.id_satuan_barang', 'sb.id_satuan_barang')
                    ->where('id_jenis_transaksi', '21')->where('void', 0)
                    ->whereBetween('pb.tanggal_pindah_barang', $date)
                    ->where(function ($w) use ($idCabang) {
                        $w->whereIn('pb.id_cabang', $idCabang)->orWhereIn('pb.id_cabang2', $idCabang);
                    })
                    ->where('status_diterima', 0)
                    ->orderBy('pb.tanggal_pindah_barang', 'asc');

                if ($status != 'all') {
                    $data = $data->where('pb.status_pindah_barang', $status);
                }

                if ($request->id_gudang) {
                    $data = $data->whereIn('pb.id_gudang', $idGudang);
                }

                $data = $data->orderBy('pb.tanggal_pindah_barang', 'asc');
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
