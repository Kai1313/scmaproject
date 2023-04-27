<?php

namespace App\Http\Controllers;

use App\Models\Accounting\InventoryTransferHeader;
use App\Models\Accounting\InventoryTransferDetail;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
use App\Models\Accounting\TrxSaldo;
use App\Models\Master\Akun;
use App\Barang;
use App\Models\Master\Cabang;
use App\Models\Master\Pelanggan;
use App\Models\Master\Pemasok;
use App\Models\Master\Setting;
use App\Models\Transaction\SalesDetail;
use App\Models\Transaction\SalesHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;

class ClosingJournalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cabang = Cabang::find(1);
        $data_cabang = Cabang::all();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Closing | List",
            "cabang" => $cabang,
            "data_cabang" => $data_cabang,
        ];

        return view('accounting.journal.closing_journal.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data_cabang = Cabang::where("status_cabang", 1)->get();
        $data_pelanggan = Pelanggan::all();
        $data_pemasok = Pemasok::all();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Closing | Create",
            "data_cabang" => $data_cabang,
            "data_pelanggan" => $data_pelanggan,
            "data_pemasok" => $data_pemasok,
        ];

        return view('accounting.journal.closing_journal.form', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function inventoryTransfer(Request $request)
    {
        try {
            // Init data
            $id_cabang = $request->id_cabang;
            $journal_type = "ME";
            $month = $request->month;
            $year = $request->year;
            $start_date = date("Y-m-d", strtotime("$year-$month-1"));
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));
            $void = 0;
            $status = 1;
            $hpp_account = Setting::where("id_cabang", $id_cabang)->where("code", "HPP Transfer Cabang")->first();
            // dd($hpp_account);
            if (!$hpp_account) {
                return response()->json([
                    "result" => FALSE,
                    "message" => "Akun HPP Transfer Cabang tidak ditemukan"
                ]);
            }

            // Get data pindah barang
            $data_header = InventoryTransferHeader::where("id_cabang2", "<>", $id_cabang)->whereBetween("tanggal_pindah_barang", [$start_date, $end_date])->where("void", 0)->where("status_pindah_barang", 1)->get();
            $details_out = [];
            $details_in = [];
            // dd($data_header);
            DB::beginTransaction();
            foreach ($data_header as $key => $header) {
                // Log::info($header->kode_pindah_barang);
                $id_transaksi = $header->kode_pindah_barang;
                // Delete detail and header existing first
                JurnalDetail::where("id_transaksi", $id_transaksi)->where("keterangan", "HPP Transfer Cabang Keluar ".$id_transaksi)->delete();
                JurnalHeader::where("id_transaksi", $id_transaksi)->where("catatan", "Closing Transfer Barang Keluar")->delete();
                JurnalDetail::where("id_transaksi", $id_transaksi)->where("keterangan", "HPP Transfer Cabang Masuk ".$id_transaksi)->delete();
                JurnalHeader::where("id_transaksi", $id_transaksi)->where("catatan", "Closing Transfer Barang Masuk")->delete();
                if ($header->type == 0) {
                    // Get header out detail
                    $data_detail = InventoryTransferDetail::select("pindah_barang_detail.id_barang", "pindah_barang_detail.qr_code", "master_qr_code.beli_master_qr_code", "master_qr_code.biaya_beli_master_qr_code", "master_qr_code.jumlah_master_qr_code", "master_qr_code.produksi_master_qr_code", "master_qr_code.listrik_master_qr_code", "master_qr_code.pegawai_master_qr_code")->join("master_qr_code", "kode_batang_master_qr_code", "pindah_barang_detail.qr_code")->where("id_pindah_barang", $header->id_pindah_barang)->get();
                    foreach ($data_detail as $key => $detail) {
                        $qty = $detail->jumlah_master_qr_code;
                        $sum = ($qty*$detail->beli_master_qr_code)+($qty*$detail->biaya_beli_master_qr_code)+($qty*$detail->produksi_master_qr_code)+($qty*$detail->listrik_master_qr_code)+($qty*$detail->pegawai_master_qr_code);
                        $details_out[] = [
                            "qr_code"=>$detail->qr_code,
                            "barang"=>$detail->id_barang,
                            "qty"=>$qty,
                            "sum"=>$sum
                        ];
                    }
                    // Log::info(json_encode($details_out));
                    // Grouping and sum the same barang
                    $grouped_out = array_reduce($details_out, function($result, $out) {
                        $product = $out['barang'];
                        $sum = $out['sum'];
                        if (isset($result[$product])) {
                            $result[$product] += $sum;
                        }
                        else {
                            $result[$product] = $sum;
                        }
                        return $result;
                    }, []);
                    // Create journal memorial
                    // Store Header
                    $header = new JurnalHeader();
                    $header->id_cabang = $id_cabang;
                    $header->jenis_jurnal = $journal_type;
                    $header->id_transaksi = $id_transaksi;
                    $header->catatan = "Closing Transfer Barang Keluar";
                    $header->void = 0;
                    $header->tanggal_jurnal = $end_date;
                    $header->user_created = NULL;
                    $header->user_modified = NULL;
                    $header->dt_created = $end_date;
                    $header->dt_modified = $end_date;
                    $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
                    // dd($header);
                    if (!$header->save()) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table header",
                        ]);
                    }

                    // Store detail
                    $i = 0;
                    $sum_debet = 0;
                    // Log::info(json_encode($grouped_out));
                    // Log::info(count($grouped_out));
                    foreach ($grouped_out as $key => $out) {
                        // Get akun barang
                        $barang = Barang::find($key);
                        if (!$barang) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "message" => "Error when store Jurnal data on table detail, barang not found",
                            ]);
                        }
                        // Log::info(json_encode($barang->id_barang));
                        $detail = new JurnalDetail();
                        $detail->id_jurnal = $header->id_jurnal;
                        $detail->index = $i + 1;
                        $detail->id_akun = $barang->id_akun;
                        $detail->keterangan = "HPP Transfer Cabang Keluar ".$id_transaksi;
                        $detail->id_transaksi = $id_transaksi;
                        $detail->debet = 0;
                        $detail->credit = $out;
                        $detail->user_created = NULL;
                        $detail->user_modified = NULL;
                        $detail->dt_created = $end_date;
                        $detail->dt_modified = $end_date;
                        // Log::info(json_encode($detail));
                        if (!$detail->save()) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "message" => "Error when store Jurnal data on table detail",
                            ]);
                        }
                        $sum_debet += $out;
                        $i++;
                    }
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $hpp_account->value2;
                    $detail->keterangan = "HPP Transfer Cabang Keluar ".$id_transaksi;
                    $detail->id_transaksi = $id_transaksi;
                    $detail->debet = $sum_debet;
                    $detail->credit = 0;
                    $detail->user_created = NULL;
                    $detail->user_modified = NULL;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // dd(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail",
                        ]);
                    }
                    // Log::info(json_encode($grouped_out));
                    // dd(json_encode($grouped_out));
                }
                else {
                    // Get header in detail
                    $data_detail = InventoryTransferDetail::select("pindah_barang_detail.id_barang", "pindah_barang_detail.qr_code", "master_qr_code.beli_master_qr_code", "master_qr_code.biaya_beli_master_qr_code", "master_qr_code.jumlah_master_qr_code", "master_qr_code.produksi_master_qr_code", "master_qr_code.listrik_master_qr_code", "master_qr_code.pegawai_master_qr_code")->join("master_qr_code", "kode_batang_master_qr_code", "pindah_barang_detail.qr_code")->where("id_pindah_barang", $header->id_pindah_barang)->get();
                    foreach ($data_detail as $key => $detail) {
                        $qty = $detail->jumlah_master_qr_code;
                        $sum = ($qty*$detail->beli_master_qr_code)+($qty*$detail->biaya_beli_master_qr_code)+($qty*$detail->produksi_master_qr_code)+($qty*$detail->listrik_master_qr_code)+($qty*$detail->pegawai_master_qr_code);
                        $details_in[] = [
                            "qr_code"=>$detail->qr_code,
                            "barang"=>$detail->id_barang,
                            "qty"=>$qty,
                            "sum"=>$sum
                        ];
                    }
                    // Log::info(json_encode($details_out));
                    // Grouping and sum the same barang
                    $grouped_in = array_reduce($details_in, function($result, $in) {
                        $product = $in['barang'];
                        $sum = $in['sum'];
                        if (isset($result[$product])) {
                            $result[$product] += $sum;
                        }
                        else {
                            $result[$product] = $sum;
                        }
                        return $result;
                    }, []);
                    // Create journal memorial
                    // Store Header
                    $header = new JurnalHeader();
                    $header->id_cabang = $id_cabang;
                    $header->jenis_jurnal = $journal_type;
                    $header->id_transaksi = $id_transaksi;
                    $header->catatan = "Closing Transfer Barang Masuk";
                    $header->void = 0;
                    $header->tanggal_jurnal = $end_date;
                    $header->user_created = NULL;
                    $header->user_modified = NULL;
                    $header->dt_created = $end_date;
                    $header->dt_modified = $end_date;
                    $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
                    // dd($header);
                    if (!$header->save()) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table header",
                        ]);
                    }

                    // Store detail
                    $i = 0;
                    $sum_kredit = 0;
                    // Log::info(json_encode($grouped_out));
                    // Log::info(count($grouped_out));
                    foreach ($grouped_in as $key => $in) {
                        // Get akun barang
                        $barang = Barang::find($key);
                        if (!$barang) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "message" => "Error when store Jurnal data on table detail, barang not found",
                            ]);
                        }
                        // Log::info(json_encode($barang->id_barang));
                        $detail = new JurnalDetail();
                        $detail->id_jurnal = $header->id_jurnal;
                        $detail->index = $i + 1;
                        $detail->id_akun = $barang->id_akun;
                        $detail->keterangan = "HPP Transfer Cabang Masuk ".$id_transaksi;
                        $detail->id_transaksi = $id_transaksi;
                        $detail->debet = $in;
                        $detail->credit = 0;
                        $detail->user_created = NULL;
                        $detail->user_modified = NULL;
                        $detail->dt_created = $end_date;
                        $detail->dt_modified = $end_date;
                        // Log::info(json_encode($detail));
                        if (!$detail->save()) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "message" => "Error when store Jurnal data on table detail",
                            ]);
                        }
                        $sum_kredit += $in;
                        $i++;
                    }
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $hpp_account->value2;
                    $detail->keterangan = "HPP Transfer Cabang Masuk ".$id_transaksi;
                    $detail->id_transaksi = $id_transaksi;
                    $detail->debet = 0;
                    $detail->credit = $sum_kredit;
                    $detail->user_created = NULL;
                    $detail->user_modified = NULL;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // dd(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail",
                        ]);
                    }
                }
            }
            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing journal inventory transfer"
            ]);
        }
        catch (\Exception $e) {
            $message = "Error when inventory transfer";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
    }


    public function penjualan(Request $request){
        try {
            // Init data
            $id_cabang = $request->id_cabang;
            $journal_type = "ME";
            $month = $request->month;
            $year = $request->year;
            $start_date = date("Y-m-d", strtotime("$year-$month-1"));
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));
            $void = 0;
            $status = 1;
            $hpp_account = Setting::where("id_cabang", $id_cabang)->where("code", "HPP Penjualan")->first();
            // dd($hpp_account);
            if (!$hpp_account) {
                return response()->json([
                    "result" => FALSE,
                    "message" => "Akun HPP Penjualan tidak ditemukan"
                ]);
            }

            // Get data pindah barang
            $data_header = SalesHeader::where("id_cabang", $id_cabang)->whereBetween("tanggal_penjualan", [$start_date, $end_date])->get();
            // dd($data_header);
            DB::beginTransaction();
            foreach ($data_header as $key => $header) {
                // Log::info($header->kode_pindah_barang);
                $id_transaksi = $header->nama_penjualan;
                // Delete detail and header existing first
                $jurnal_header = JurnalHeader::where("id_transaksi", $id_transaksi)->where('tanggal_jurnal', $end_date)->where("catatan", "Closing Penjualan")->get();

                foreach($jurnal_header as $jurnal){
                    JurnalDetail::where("id_jurnal", $jurnal->id_jurnal)->delete();
                    JurnalHeader::where("id_jurnal", $jurnal->id_jurnal)->delete();
                }


                // Get header out detail
                $data_detail = SalesDetail::select("penjualan_detail.id_barang", "penjualan_detail.kode_batang_lama_penjualan_detail", "master_qr_code.beli_master_qr_code", "master_qr_code.biaya_beli_master_qr_code", "master_qr_code.jumlah_master_qr_code", "master_qr_code.produksi_master_qr_code", "master_qr_code.listrik_master_qr_code", "master_qr_code.pegawai_master_qr_code")->join("master_qr_code", "kode_batang_master_qr_code", "pindah_barang_detail.kode_batang_lama_penjualan_detail")->where("id_penjualan", $header->id_penjualan)->get();

                foreach ($data_detail as $key => $detail) {
                    $qty = $detail->jumlah_master_qr_code;
                    $sum = ($qty*$detail->beli_master_qr_code)+($qty*$detail->biaya_beli_master_qr_code)+($qty*$detail->produksi_master_qr_code)+($qty*$detail->listrik_master_qr_code)+($qty*$detail->pegawai_master_qr_code);
                    $details_out[] = [
                        "qr_code"=>$detail->kode_batang_lama_penjualan_detail,
                        "barang"=>$detail->id_barang,
                        "qty"=>$qty,
                        "sum"=>$sum
                    ];
                }
                // Log::info(json_encode($details_out));
                // Grouping and sum the same barang
                $grouped_out = array_reduce($details_out, function($result, $out) {
                    $product = $out['barang'];
                    $sum = $out['sum'];
                    if (isset($result[$product])) {
                        $result[$product] += $sum;
                    }
                    else {
                        $result[$product] = $sum;
                    }
                    return $result;
                }, []);

                // Create journal memorial
                // Store Header
                $header = new JurnalHeader();
                $header->id_cabang = $id_cabang;
                $header->jenis_jurnal = $journal_type;
                $header->id_transaksi = $id_transaksi;
                $header->catatan = "Closing Penjualan";
                $header->void = 0;
                $header->tanggal_jurnal = $end_date;
                $header->user_created = NULL;
                $header->user_modified = NULL;
                $header->dt_created = $end_date;
                $header->dt_modified = $end_date;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
                // dd($header);
                if (!$header->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table header",
                    ]);
                }

                // Store detail
                $i = 0;
                $sum_debet = 0;
                // Log::info(json_encode($grouped_out));
                // Log::info(count($grouped_out));
                foreach ($grouped_out as $key => $out) {
                    // Get akun barang
                    $barang = Barang::find($key);
                    if (!$barang) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail, barang not found",
                        ]);
                    }
                    // Log::info(json_encode($barang->id_barang));
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $barang->id_akun;
                    $detail->keterangan = "Harga Produksi Penjualan ".$id_transaksi;
                    // $detail->id_transaksi = $id_transaksi;
                    $detail->debet = 0;
                    $detail->credit = $out;
                    $detail->user_created = NULL;
                    $detail->user_modified = NULL;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // Log::info(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail",
                        ]);
                    }
                    $sum_debet += $out;
                    $i++;
                }
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $i + 1;
                $detail->id_akun = $hpp_account->value2;
                $detail->keterangan = "Harga Produksi Penjualan ".$id_transaksi;
                // $detail->id_transaksi = $id_transaksi;
                $detail->debet = $sum_debet;
                $detail->credit = 0;
                $detail->user_created = NULL;
                $detail->user_modified = NULL;
                $detail->dt_created = $end_date;
                $detail->dt_modified = $end_date;
                // dd(json_encode($detail));
                if (!$detail->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail",
                    ]);
                }
                // Log::info(json_encode($grouped_out));
                // dd(json_encode($grouped_out));

            }
            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing journal penjualan"
            ]);
        }
        catch (\Exception $e) {
            $message = "Error when closing journal penjualan";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
    }

    public function penyusutan(Request $request){
        try {
            // Init data
            $id_cabang = $request->id_cabang;
            $journal_type = "ME";
            $month = $request->month;
            $year = $request->year;
            $start_date = date("Y-m-d", strtotime("$year-$month-1"));
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));
            $void = 0;
            $status = 1;
            $asset_account = Setting::where("id_cabang", $id_cabang)->where("code", "Kategori Asset")->first();
            // dd($hpp_account);
            if (!$asset_account) {
                return response()->json([
                    "result" => FALSE,
                    "message" => "Akun Kategori Asset tidak ditemukan"
                ]);
            }

            // Get data pindah barang
            $data_asset = Barang::join('master_qr_code', 'master_qr_code.id_barang', 'barang.id_barang')->join('gudang', 'gudang.id_gudang', 'master_qr_code.id_gudang')->where("status_barang", 1)->where('gudang.id_cabang', $id_cabang)->where('id_kategori_barang', $asset_account)->where('sisa_master_qr_code', '>', 0)->get();
            // dd($data_header);
            DB::beginTransaction();
            foreach ($data_asset as $key => $header) {
                // Log::info($header->kode_pindah_barang);
                // Create journal memorial
                // Store Header
                $header = new JurnalHeader();
                $header->id_cabang = $id_cabang;
                $header->jenis_jurnal = $journal_type;
                $header->id_transaksi = "Jurnal Penyusutan";
                // $header->catatan = "Closing Penjualan";
                $header->void = 0;
                $header->tanggal_jurnal = $end_date;
                $header->user_created = NULL;
                $header->user_modified = NULL;
                $header->dt_created = $end_date;
                $header->dt_modified = $end_date;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
                // dd($header);
                if (!$header->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table header",
                    ]);
                }

                // Store detail
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = 1;
                $detail->id_akun = $header->id_biaya;
                $detail->keterangan = "Biaya Penyusutan ".$header->nama_barang;
                // $detail->id_transaksi = $id_transaksi;
                $detail->debet = 0;
                $detail->credit = 0;
                $detail->user_created = NULL;
                $detail->user_modified = NULL;
                $detail->dt_created = $end_date;
                $detail->dt_modified = $end_date;
                // Log::info(json_encode($detail));
                if (!$detail->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail",
                    ]);
                }

                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = 2;
                $detail->id_akun = $header->id_akun;
                $detail->keterangan = "Penyusutan ".$header->nama_barang;
                // $detail->id_transaksi = $id_transaksi;
                $detail->debet = 0;
                $detail->credit = 0;
                $detail->user_created = NULL;
                $detail->user_modified = NULL;
                $detail->dt_created = $end_date;
                $detail->dt_modified = $end_date;
                // dd(json_encode($detail));
                if (!$detail->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail",
                    ]);
                }
                // Log::info(json_encode($grouped_out));
                // dd(json_encode($grouped_out));

            }
            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing journal penyusutan"
            ]);
        }
        catch (\Exception $e) {
            $message = "Error when closing journal penyusutan";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
    }

    public function generateJournalCode($cabang, $jenis)
    {
        try {
            $ex = 0;
            do {
                // Init data
                $kodeCabang = Cabang::find($cabang);
                $prefix = $kodeCabang->kode_cabang . "." . $jenis . "." . date("ym");

                // Check exist
                $check = JurnalHeader::where("kode_jurnal", "LIKE", "$prefix%")->orderBy("kode_jurnal", "DESC")->get();
                if (count($check) > 0) {
                    $max = (int) substr($check[0]->kode_jurnal, -4);
                    $max += 1;
                    $code = $prefix . "." . sprintf("%04s", $max);
                } else {
                    $code = $prefix . ".0001";
                }
                $ex++;
                if ($ex >= 5) {
                    $code = "error";
                    break;
                }
            } while (JurnalHeader::where("kode_jurnal", $code)->first());
            return $code;
        } catch (\Exception $e) {
            Log::error("Error when generate journal code");
        }
    }
}
