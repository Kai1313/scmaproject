<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserToken;
use App\Purchase;
use App\QualityControl;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\DataTables;

class QcReceiptController extends Controller
{
    public $arrayStatus = [
        ['text' => '', 'class' => 'label label-default', 'id' => ''],
        ['text' => 'Passed', 'class' => 'label label-success', 'id' => '1'],
        ['text' => 'Reject', 'class' => 'label label-danger', 'id' => '2'],
        ['text' => 'Hold', 'class' => 'label label-default', 'id' => '3'],
    ];

    public function index(Request $request)
    {
        $checkAuth = $this->checkUser($request);
        if ($checkAuth['status'] == false) {
            return view('exceptions.forbidden');
        }

        if ($request->ajax()) {
            $data = DB::table('qc')->select('id', 'tanggal_qc', 'nama_pembelian', 'nama_barang', 'jumlah_pembelian_detail', 'status_qc', 'nama_satuan_barang', 'reason', 'sg_pembelian_detail', 'be_pembelian_detail', 'ph_pembelian_detail', 'warna_pembelian_detail', 'keterangan_pembelian_detail')
                ->leftJoin('pembelian', 'qc.id_pembelian', '=', 'pembelian.id_pembelian')
                ->leftJoin('barang', 'qc.id_barang', '=', 'barang.id_barang')
                ->leftJoin('satuan_barang', 'qc.id_satuan_barang', '=', 'satuan_barang.id_satuan_barang')
                ->whereBetween('tanggal_qc', [$request->start_date, $request->end_date]);
            if (isset($request->c)) {
                $data = $data->where('qc.id_cabang', $request->c);
            }

            $data = $data->orderBy('qc.tanggal_qc', 'desc');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    // $btn = '<ul class="horizontal-list">';
                    // $btn .= '<li><a href="' . route('purchase-request-view', $row->id) . '" class="btn btn-info btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-search"></i> Lihat</a></li>';
                    // $btn .= '<li><a href="' . route('purchase-request-entry', $row->id) . '" class="btn btn-warning btn-xs mr-1 mb-1"><i class="glyphicon glyphicon-pencil"></i> Ubah</a></li>';
                    // $btn .= '<li><a href="' . route('purchase-request-delete', $row->id) . '" class="btn btn-danger btn-xs btn-destroy mr-1 mb-1"><i class="glyphicon glyphicon-trash"></i> Void</a></li>';
                    // $btn .= '</ul>';

                    return '';
                })
                ->editColumn('status_qc', function ($row) {
                    return '<label class="' . $this->arrayStatus[$row->status_qc]['class'] . '">' . $this->arrayStatus[$row->status_qc]['text'] . '</label>';
                })
                ->rawColumns(['action', 'status_qc'])
                ->make(true);
        }

        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();
        return view('ops.qualityControl.index', [
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | QC Permintaan Pembelian | List",
        ]);
    }

    public function entry($id = 0)
    {
        $data = QualityControl::find($id);
        $cabang = DB::table('cabang')->where('status_cabang', 1)->get();

        return view('ops.qualityControl.form', [
            'data' => $data,
            'cabang' => $cabang,
            "pageTitle" => "SCA OPS | QC Penerimaan Pembelian | " . ($id == 0 ? 'Create' : 'Edit'),
            'arrayStatus' => $this->arrayStatus,
        ]);
    }

    public function saveEntry(Request $request, $id = 0)
    {
        try {
            DB::beginTransaction();
            $datas = json_decode($request->details);
            foreach ($datas as $value) {
                $data = QualityControl::find($value->id);
                if (!$data) {
                    $data = new QualityControl;
                    $data->tanggal_qc = date('Y-m-d');
                    $data->id_cabang = $request->id_cabang;
                    $data->id_pembelian = $request->id_pembelian;
                    $data->id_barang = $value->id_barang;
                    $data->id_satuan_barang = $value->id_satuan_barang;
                    $data->jumlah_pembelian_detail = $value->jumlah_pembelian_detail;
                }

                $data->status_qc = $value->status_qc;
                $data->reason = $value->reason;
                $data->sg_pembelian_detail = $value->sg_pembelian_detail;
                $data->be_pembelian_detail = $value->be_pembelian_detail;
                $data->ph_pembelian_detail = $value->ph_pembelian_detail;
                $data->warna_pembelian_detail = $value->warna_pembelian_detail;
                $data->keterangan_pembelian_detail = $value->keterangan_pembelian_detail;
                $data->save();

                $data->updatePembelianDetail();
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
                "redirect" => route('qc_receipt'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error when save qc receipt");
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => "Data gagal tersimpan",
            ]);
        }
    }

    public function autoPurchasing(Request $request)
    {
        $cabang = $request->cabang;
        $duration = DB::table('setting')->where('code', 'QC Duration')->first();
        $startDate = date('Y-m-d', strtotime('-' . intval($duration->value2) . ' months'));
        $endDate = date('Y-m-d');
        $datas = DB::table('pembelian')->select('nama_pembelian as text', 'id_pembelian as id')
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->where('id_cabang', $cabang)
            ->get();

        return response()->json([
            'result' => true,
            'data' => $datas,
        ]);
    }

    public function autoItem(Request $request)
    {
        $idPembelian = $request->number;
        $parent = Purchase::find($idPembelian);
        return response()->json([
            'result' => true,
            'list_item' => $parent->detailgroup,
            'qc' => $parent->qc,
        ]);
    }

    public function checkUser($request)
    {
        $user_id = $request->user_id;
        if ($user_id != '' && $request->session()->has('token') == false || $request->session()->has('token') == true) {
            if ($request->session()->has('token') == true) {
                $user_id = $request->session()->get('user')->id_pengguna;
            }
            $user = User::where('id_pengguna', $user_id)->first();
            $token = UserToken::where('id_pengguna', $user_id)->where('status_token_pengguna', 1)->whereRaw("waktu_habis_token_pengguna > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", Carbon::now()->format('Y-m-d H:i:s'))->first();

            $sql = "SELECT
                a.id_pengguna,
                a.id_grup_pengguna,
                d.id_menu,
                d.nama_menu,
                c.lihat_akses_menu,
                c.tambah_akses_menu,
                c.ubah_akses_menu,
                c.hapus_akses_menu,
                c.cetak_akses_menu
            FROM
                pengguna a,
                grup_pengguna b,
                akses_menu c,
                menu d
            WHERE
                a.id_grup_pengguna = b.id_grup_pengguna
                AND b.id_grup_pengguna = c.id_grup_pengguna
                AND c.id_menu = d.id_menu
                AND a.id_pengguna = $user_id
                AND d.keterangan_menu = 'Accounting'
                AND d.status_menu = 1";
            $access = DB::connection('mysql')->select($sql);

            $user_access = array();
            foreach ($access as $value) {
                $user_access[$value->nama_menu] = ['show' => $value->lihat_akses_menu, 'create' => $value->tambah_akses_menu, 'edit' => $value->ubah_akses_menu, 'delete' => $value->hapus_akses_menu, 'print' => $value->cetak_akses_menu];
            }

            if ($token && $request->session()->has('token') == false) {
                $request->session()->put('token', $token->nama_token_pengguna);
                $request->session()->put('user', $user);
                $request->session()->put('access', $user_access);
            } else if ($request->session()->has('token')) {
            } else {
                $request->session()->flush();
            }

            $session = $request->session()->get('access');

            return ['status' => true];
        } else {
            $request->session()->flush();
            return ['status' => false];
        }
    }
}
