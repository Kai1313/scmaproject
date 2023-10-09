<?php

namespace App\Http\Controllers;

use App\MoveBranch;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class ReceivedFromBranchController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'terima_dari_cabang', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        if ($request->ajax()) {
            $data = DB::table('pindah_barang as pb')
                ->select(
                    'pb.id_pindah_barang',
                    'pb.type',
                    'nama_gudang',
                    'pb.tanggal_pindah_barang',
                    'pb.kode_pindah_barang',
                    'nama_cabang',
                    'pb.status_pindah_barang',
                    'pb.keterangan_pindah_barang',
                    'pb.transporter',
                    'pb2.kode_pindah_barang as ref_code'
                )
                ->leftJoin('gudang', 'pb.id_gudang', '=', 'gudang.id_gudang')
                ->leftJoin('cabang', 'pb.id_cabang2', '=', 'cabang.id_cabang')
                ->leftJoin('pindah_barang as pb2', 'pb.id_pindah_barang2', 'pb2.id_pindah_barang')
                ->where('pb.id_jenis_transaksi', 22)
                ->where('pb.type', 1);
            if (isset($request->c)) {
                $data = $data->where('pb.id_cabang', $request->c);
            }

            $data = $data->orderBy('pb.tanggal_pindah_barang', 'desc')->orderBy('pb.kode_pindah_barang', 'desc');

            $idUser = session()->get('user')['id_pengguna'];
            $filterUser = DB::table('pengguna')
                ->where(function ($w) {
                    $w->where('id_grup_pengguna', session()->get('user')['id_grup_pengguna'])->orWhere('id_grup_pengguna', 1);
                })
                ->where('status_pengguna', '1')->pluck('id_pengguna')->toArray();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($idUser, $filterUser) {
                    $btn = '<ul class="horizontal-list">';
                    $btn .= '<li><a href="' . route('received_from_branch-view', $row->id_pindah_barang) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    if ($row->status_pindah_barang == 0 && (in_array($idUser, $filterUser) || $idUser == $row->user_created)) {
                        $btn .= '<li><a href="' . route('received_from_branch-entry', $row->id_pindah_barang) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    }

                    $btn .= '</ul>';
                    return $btn;
                })
                ->editColumn('status_pindah_barang', function ($row) {
                    if ($row->status_pindah_barang == '0') {
                        return '<label class="label label-warning">Dalam Perjalanan</label>';
                    } else if ($row->status_pindah_barang == '1') {
                        return '<label class="label label-success">Diterima</label>';
                    } else {
                        return '';
                    }
                })
                ->rawColumns(['action', 'status_pindah_barang'])
                ->make(true);
        }

        $cabang = session()->get('access_cabang');
        return view('ops.receivedFromBranch.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Terima Dari Cabang | List",
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('terima_dari_cabang', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MoveBranch::find($id);
        $cabang = session()->get('access_cabang');
        return view('ops.receivedFromBranch.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Terima Dari Cabang | Lihat",
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = MoveBranch::find($id);
        if (!$data) {
            $checkUsed = MoveBranch::where('id_pindah_barang2', $request->id_pindah_barang2)->first();
            if ($checkUsed) {
                return response()->json([
                    "result" => false,
                    "message" => "Kode kirim ke cabang sudah di dalam kode penerimaan " . $checkUsed->kode_pindah_barang,
                ], 500);
            }

            $data = new MoveBranch;
            $period = $this->checkPeriod($request->tanggal_pindah_barang);
            if ($period['result'] == false) {
                return response()->json($period, 500);
            }
        } else {
            $period = $this->checkPeriod($data->tanggal_pindah_barang);
            if ($period['result'] == false) {
                return response()->json($period, 500);
            }
        }

        try {
            DB::beginTransaction();
            $data->fill($request->all());
            if ($id == 0) {
                $data->kode_pindah_barang = MoveBranch::createcodeCabang($request->id_cabang, $request->tanggal_pindah_barang);
                $data->status_pindah_barang = 0;
                $data->type = 1;
                $data->user_created = session()->get('user')['id_pengguna'];
            } else {
                $data->user_modified = session()->get('user')['id_pengguna'];
            }

            $data->save();
            $data->saveDetails($request->details, 'in');

            $parent = MoveBranch::find($data->id_pindah_barang2);
            $parent->saveChangeStatusFromParent($data->details->pluck('qr_code')->toArray());

            if (count($data->details) == count($parent->details)) {
                $data->status_pindah_barang = 1;
                $data->save();

                $parent->status_pindah_barang = 1;
                $parent->save();
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('received_from_branch-entry', $data->id_pindah_barang),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save purchase request");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function viewData($id)
    {
        if (checkAccessMenu('terima_dari_cabang', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MoveBranch::where('type', 1)->where('id_pindah_barang', $id)->first();
        return view('ops.receivedFromBranch.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Terima Dari Cabang | Detail",
        ]);
    }

    // public function destroy($id)
    // {
    //     if (checkAccessMenu('terima_dari_cabang', 'delete') == false) {
    //         return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
    //     }

    // $data = PurchaseRequest::find($id);
    // if (!$data) {
    //     return response()->json([
    //         "result" => false,
    //         "message" => "Data tidak ditemukan",
    //     ]);
    // }

    // try {
    //     DB::beginTransaction();
    //     $data->void = 1;
    //     $data->void_user_id = session()->get('user')['id_pengguna'];
    //     $data->save();

    //     DB::commit();
    //     return response()->json([
    //         "result" => true,
    //         "message" => "Data berhasil dibatalkan",
    //         "redirect" => route('purchase-request'),
    //     ]);
    // } catch (\Exception $e) {
    //     DB::rollback();
    //     Log::error("Error when void purchase request");
    //     Log::error($e);
    //     return response()->json([
    //         "result" => false,
    //         "message" => "Data gagal dibatalkan",
    //     ]);
    // }
    // }

    public function autoCode(Request $request)
    {
        $idCabang = $request->cabang;
        $data = MoveBranch::select(
            'kode_pindah_barang as text',
            'id_pindah_barang as id',
            'transporter',
            'nomor_polisi',
            'nama_cabang',
            'keterangan_pindah_barang',
            'pindah_barang.id_cabang'
        )
            ->leftJoin('cabang', 'pindah_barang.id_cabang', '=', 'cabang.id_cabang')
            ->where('id_cabang2', $idCabang)
            ->where('status_pindah_barang', 0)
            ->where('id_jenis_transaksi', 21)
            ->where('void', 0)
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $data,
            'message' => '',
        ], 200);
    }

    public function getDetailItem(Request $request)
    {
        $idPindahBarang = $request->id_pindah_barang;
        $qrcode = $request->qrcode;

        if ($idPindahBarang == '' || $qrcode == '') {
            return response()->json([
                'message' => 'Pastikan referensi kode pindah cabang dan qrcode sudah benar',
            ], 500);
        }

        $data = DB::table('pindah_barang_detail')
            ->select(
                'be', 'bentuk',
                'pindah_barang_detail.id_barang',
                'pindah_barang_detail.id_satuan_barang',
                'qty',
                'keterangan',
                'qr_code',
                'nama_barang',
                'nama_satuan_barang',
                'ph',
                'sg',
                'warna',
                'status_diterima',
                'batch',
                'tanggal_kadaluarsa',
                'zak',
                'weight_zak',
                'id_wrapper_zak',
                'status_diterima'
            )
            ->leftJoin('barang', 'pindah_barang_detail.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'pindah_barang_detail.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
            ->where('id_pindah_barang', $idPindahBarang)->where('qr_code', $qrcode)->first();
        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 500);
        }

        if ($data->status_diterima == 1) {
            return response()->json([
                'message' => 'Barang sudah diterima',
            ], 500);
        }

        return response()->json([
            'data' => $data,
        ], 200);
    }

    public function printData($id)
    {
        if (checkAccessMenu('terima_dari_cabang', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MoveBranch::find($id);
        if (!$data) {
            return 'data tidak ditemukan';
        }

        return view('ops.receivedFromBranch.print', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Terima Dari Cabang | Cetak",
        ]);
    }

    public function checkPeriod($date)
    {
        if (!$date) {
            return ['result' => false, 'message' => 'Tanggal tidak ditemukan'];
        }

        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));

        $data = DB::table('periode')->where('tahun_periode', $year)->where('bulan_periode', $month)->first();
        if (!$data) {
            return ['result' => false, 'message' => 'Periode tidak ditemukan'];
        }

        if ($data->status_periode == '0') {
            return ['result' => false, 'message' => 'Periode sudah ditutup'];
        }

        return ['result' => true];
    }
}
