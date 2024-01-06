<?php

namespace App\Http\Controllers;

use App\MasterQrCode;
use App\MoveBranch;
use App\MoveBranchDetail;
use DB;
use Illuminate\Http\Request;
use Log;
use PDF;
use Yajra\DataTables\DataTables;

class SendToBranchController extends Controller
{
    public function index(Request $request)
    {
        if (checkUserSession($request, 'kirim_ke_cabang', 'show') == false) {
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
                    'pb.user_created',
                    'pb.void',
                    'pb2.kode_pindah_barang as ref_code'
                )
                ->leftJoin('gudang', 'pb.id_gudang', '=', 'gudang.id_gudang')
                ->leftJoin('cabang', 'pb.id_cabang2', '=', 'cabang.id_cabang')
                ->leftJoin('pindah_barang as pb2', 'pb.id_pindah_barang', 'pb2.id_pindah_barang2')
                ->where('pb.id_jenis_transaksi', 21)
                ->where('pb.type', 0);
            if (isset($request->c)) {
                $data = $data->where('pb.id_cabang', $request->c);
            }

            if ($request->show_void == 'false') {
                $data = $data->where('pb.void', '0');
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
                ->addColumn('action', function ($row) use ($filterUser, $idUser) {
                    $btn = '';
                    $btn .= '<a href="' . route('send_to_branch-view', $row->id_pindah_barang) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a>';
                    if ($row->status_pindah_barang == 0 && $row->void == 0 && (in_array($idUser, $filterUser) || $idUser == $row->user_created)) {
                        $btn .= '<a href="' . route('send_to_branch-entry', $row->id_pindah_barang) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a>';
                        if ($row->ref_code == null) {
                            $btn .= '<a href="' . route('send_to_branch-delete', $row->id_pindah_barang) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a>';
                        }
                    }

                    $btn .= '<a href="' . route('send_to_branch-print-data', $row->id_pindah_barang) . '" class="btn btn-default btn-xs mr-1 mb-1" target="_blank"><i class="glyphicon glyphicon-print"></i> Cetak</a>';
                    return $btn;
                })
                ->editColumn('status_pindah_barang', function ($row) {
                    if ($row->status_pindah_barang == '0') {
                        if ($row->void == 1) {
                            return '<label class="label label-default">Batal</label>';
                        }

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
        return view('ops.sendToBranch.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | Kirim Ke Cabang | List",
        ]);
    }

    public function entry($id = 0)
    {
        if (checkAccessMenu('kirim_ke_cabang', $id == 0 ? 'create' : 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MoveBranch::find($id);
        $qrcodeReceived = [];
        $dataReceived = MoveBranch::where('id_pindah_barang2', $id)->first();
        if ($dataReceived) {
            $qrcodeReceived = MoveBranchDetail::where('id_pindah_barang', $dataReceived->id_pindah_barang)->pluck('qr_code')->toArray();
        }

        $cabang = session()->get('access_cabang');
        $allCabang = DB::table('cabang')->select('id_cabang as id', 'nama_cabang as text')->where('status_cabang', 1)->get();
        return view('ops.sendToBranch.form', [
            'data' => $data,
            'cabang' => $cabang,
            'allCabang' => $allCabang,
            "pageTitle" => "SCA OPS | Kirim Ke Cabang | " . ($id == 0 ? 'Create' : 'Edit'),
            'qrcodeReceived' => $qrcodeReceived,
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        $data = MoveBranch::find($id);
        if (!$data) {
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
                $data->type = 0;
                $data->user_created = session()->get('user')['id_pengguna'];
            } else {
                $data->user_modified = session()->get('user')['id_pengguna'];
            }

            $data->save();
            // if (isset($request->detele_details)) {
            //     $data->removedetails($request->detele_details, 'out');
            // }

            // $data->saveDetails($request->details, 'out');
            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('send_to_branch-entry', $data->id_pindah_barang),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save send to branch");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
    }

    public function viewData($id)
    {
        if (checkAccessMenu('kirim_ke_cabang', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data = MoveBranch::where('type', 0)->where('id_pindah_barang', $id)->first();
        $groupPengguna = DB::table('pengguna')->select(DB::raw('distinct(id_grup_pengguna)'))->where('id_pengguna', $data->user_created)->orWhere('id_grup_pengguna', 1)->get()->toArray();
        $groups = [];
        foreach ($groupPengguna as $grup) {
            $groups[] = $grup->id_grup_pengguna;
        }

        return view('ops.sendToBranch.detail', [
            'data' => $data,
            "pageTitle" => "SCA OPS | Kirim Ke Cabang | Lihat",
            'isEdit' => in_array(session()->get('user')['id_grup_pengguna'], $groups),
        ]);
    }

    public function destroy($id)
    {
        if (checkAccessMenu('kirim_ke_cabang', 'delete') == false) {
            return response()->json([
                "result" => false,
                "message" => "Tidak mendapatkan akses halaman",
            ], 500);
        }

        $data = MoveBranch::find($id);
        if (!$data) {
            return response()->json([
                "result" => false,
                "message" => "Data tidak ditemukan",
            ], 500);
        }

        $period = $this->checkPeriod($data->tanggal_pindah_barang);
        if ($period['result'] == false) {
            return response()->json($period, 500);
        }

        try {
            DB::beginTransaction();
            $data->void = 1;
            $data->void_user_id = session()->get('user')['id_pengguna'];
            $data->save();
            $data->voidDetails();

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil dibatalkan",
                "redirect" => route('send_to_branch'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when void pindah barang");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal dibatalkan",
            ], 500);
        }
    }

    public function autoQRCode(Request $request)
    {
        $idCabang = $request->id_cabang;
        $idGudang = $request->id_gudang;
        $qrcode = $request->qrcode;
        $id = $request->id;
        $data = DB::table('master_qr_code as mqc')
            ->select(
                'mqc.id_barang',
                'nama_barang',
                'mqc.id_satuan_barang',
                'nama_satuan_barang',
                'kode_batang_master_qr_code as qr_code',
                'sisa_master_qr_code as qty',
                'sg_master_qr_code as sg',
                'be_master_qr_code as be',
                'ph_master_qr_code as ph',
                'bentuk_master_qr_code as bentuk',
                'warna_master_qr_code as warna',
                'keterangan_master_qr_code as keterangan',
                'id_rak',
                'sisa_master_qr_code',
                'tanggal_expired_master_qr_code as tanggal_kadaluarsa',
                'batch_master_qr_code as batch',
                'zak',
                'id_wrapper_zak',
                'weight_zak',
                'mqc.status_qc_qr_code'
            )
            ->leftJoin('barang', 'mqc.id_barang', '=', 'barang.id_barang')
            ->leftJoin('satuan_barang', 'mqc.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
            ->where('id_cabang', $idCabang)->where('mqc.id_gudang', $idGudang)
        // ->where('mqc.status_qc_qr_code', 1)
            ->where('mqc.kode_batang_master_qr_code', $qrcode)
            ->first();

        if (!$data) {
            $status = 500;
            $message = 'Barang tidak ditemukan';
        } else if ($data && $data->id_rak != null) {
            $status = 500;
            $message = "Barang masih berada di rak";
        } else if ($data && $data->sisa_master_qr_code <= 0) {
            $status = 500;
            $message = "Barang sudah habis";
        } else if ($data && $data->status_qc_qr_code != '1') {
            $status = 500;
            $message = 'Barang belum di QC';
        } else {
            $message = '';
            $status = 200;
        }

        $checkDetail = MoveBranchDetail::where('id_pindah_barang', $id)->where('qr_code', $qrcode)->first();
        if ($checkDetail) {
            $status = 500;
            $message = 'Barang sudah discan';
        }

        return response()->json([
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    public function printData($id)
    {
        if (checkAccessMenu('kirim_ke_cabang', 'print') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $dataSatuan = DB::table('isi_satuan_barang')->select(DB::raw('distinct(isi_satuan_barang.id_satuan_barang)'), 'id_barang', 'nama_satuan_barang')
            ->leftJoin('satuan_barang', 'isi_satuan_barang.id_satuan_barang', 'satuan_barang.id_satuan_barang')
            ->where('satuan_wadah_isi_satuan_barang', '1')->get();
        $arraySatuan = [];
        foreach ($dataSatuan as $satuan) {
            $arraySatuan[$satuan->id_barang] = $satuan->nama_satuan_barang;
        }

        $data = MoveBranch::where('id_jenis_transaksi', 21)->where('id_pindah_barang', $id)->first();
        if (!$data) {
            return 'data tidak ditemukan';
        }

        $pdf = PDF::loadView('ops.sendToBranch.print', ['data' => $data, 'arraySatuan' => $arraySatuan]);
        $pdf->setPaper('a5', 'landscape');
        return $pdf->stream('Surat jalan pindah cabang ' . $data->kode_pindah_barang . '.pdf');
    }

    public function saveEntryDetail(Request $request)
    {
        $store = MoveBranchDetail::find($request->id_pindah_barang_detail);
        if (!$store) {
            return response()->json([
                "result" => false,
                "message" => "Data tidak ditemukan",
            ], 500);
        }

        DB::beginTransaction();
        try {
            $store->keterangan = $request->keterangan;
            $store->keterangan_sj = $request->keterangan_sj;
            $store->save();

            DB::table('kartu_stok')
                ->where('id_jenis_transaksi', 21)
                ->where('kode_batang_kartu_stok', $store->qr_code)->update([
                'keterangan_kartu_stok' => $store->keterangan,
            ]);

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('send_to_branch-view', $store->id_pindah_barang),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save send to branch");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ], 500);
        }
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

    public function saveDetailEntry(Request $request, $id)
    {
        $data = MoveBranch::find($id);
        if (!$data) {
            return response()->json(['result' => false, 'message' => 'Pengiriman tidak ditemukan'], 500);
        }

        $stock = MasterQrCode::where('kode_batang_master_qr_code', $request->qr_code)->first();
        if (!$stock) {
            return response()->json(['result' => false, 'message' => 'Stok tidak ditemukan'], 500);
        }

        if ($stock->id_cabang != $data->id_cabang || $stock->id_gudang != $data->id_gudang) {
            return response()->json(['result' => false, 'message' => 'Lokasi stok dan lokasi pengiriman tidak sama'], 500);
        }

        if ($stock->id_rak != null) {
            return response()->json(['result' => false, 'message' => 'Stok masih dalam rak'], 500);
        }

        DB::beginTransaction();
        $array[] = $request->all();
        $s = $data->savedetails(json_encode($array), 'out');
        if ($s['result'] == false) {
            DB::rollback();
            return response()->json($s, 500);
        }

        DB::commit();
        return response()->json([
            "result" => true,
            "message" => "Data berhasil disimpan",
            "redirect" => route('send_to_branch-entry', $id),
        ], 200);
    }

    public function deleteDetail(Request $request, $parent, $id)
    {
        $data = MoveBranch::where('id_pindah_barang', $parent)->first();
        if (!$data) {
            return response()->json(['result' => false, 'message' => 'Data tidak ditemukan'], 500);
        }

        $detail = MoveBranchDetail::find($id);
        if (!$detail) {
            return response()->json(['result' => false, 'message' => 'Data tidak ditemukan'], 500);
        }

        DB::beginTransaction();
        $array[] = $detail;
        $r = $data->removedetails(json_encode($array), 'out');
        if ($r['result'] == false) {
            DB::rollback();
            return response()->json($r, 500);
        }

        DB::commit();
        return response()->json([
            "result" => true,
            "message" => "Data berhasil diproses",
            "redirect" => route('send_to_branch-entry', $parent),
        ], 200);
    }

    public function saveDetailDescEntry(Request $request, $id)
    {
        $data = MoveBranch::where('id_pindah_barang', $parent)->first();
        if (!$data) {
            return response()->json(['result' => false, 'message' => 'Data tidak ditemukan'], 500);
        }

        $detail = MoveBranchDetail::find($id);
        if (!$detail) {
            return response()->json(['result' => false, 'message' => 'Data tidak ditemukan'], 500);
        }
    }
}
