<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Closing;
use App\Models\Accounting\InventoryTransferHeader;
use App\Models\Accounting\InventoryTransferDetail;
use App\Models\Accounting\StockCorrectionHeader;
use App\Models\Accounting\StockCorrectionDetail;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
use App\Models\Accounting\SaldoBalance;
use App\Models\Accounting\TrxSaldo;
use App\Models\Master\Akun;
use App\Barang;
use App\Models\Master\Cabang;
use App\Models\Master\Pelanggan;
use App\Models\Master\Pemasok;
use App\Models\Master\Setting;
use App\Models\Transaction\Production;
use App\Models\Transaction\ProductionCost;
use App\Models\Transaction\ProductionDetail;
use App\Models\Transaction\SalesDetail;
use App\Models\Transaction\SalesHeader;
use Exception;
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
    public function index(Request $request)
    {
        if (checkUserSession($request, 'closing_journal', 'show') == false) {
            // Log::debug(checkUserSession($request, 'closing_journal', 'show'));
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        // $cabang = Cabang::find(1);
        $data_cabang = getCabang();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Closing | List",
            // "cabang" => $cabang,
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
        $data_cabang = getCabang();
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
        try {
            // Init data
            $id_cabang = $request->id_cabang;
            $month = $request->month;
            $year = $request->year;

            // Store to closing table
            DB::beginTransaction();
            $check = Closing::where("month", $month)->where("year", $year)->first();
            if ($check) {
                return response()->json([
                    "result" => FALSE,
                    "message" => "Closing sudah pernah dilakukan"
                ]);
            }
            $closing = new Closing;
            $closing->month = $month;
            $closing->year = $year;
            if (!$closing->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when store data on table closing",
                ]);
            }
            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing journal data"
            ]);
        }
        catch (\Exception $e) {
            DB::rollback();
            $message = "Error when store closing";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
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

    // Start Step 1
    public function getProductionCost($date, $id_cabang){
        $param_bulan = date('m', strtotime($date));
        $param_tahun = date('Y', strtotime($date));

        $get_akun_biaya_listrik = Setting::where("id_cabang", $id_cabang)->where("code", "Biaya Listrik")->first();
        $get_akun_biaya_operator = Setting::where("id_cabang", $id_cabang)->where("code", "Biaya Operator")->first();
        $get_akun_pembulatan = Setting::where("id_cabang", $id_cabang)->where("code", "Pembulatan")->first();

        $biaya_listrik = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
                        ->whereRaw('MONTH(tanggal_jurnal) = ' . $param_bulan)
                        ->whereRaw('YEAR(tanggal_jurnal) = ' . $param_tahun)
                        ->where('jurnal_header.void', 0)
                        ->whereNull('jurnal_header.id_transaksi')
                        ->where('jurnal_detail.id_akun', $get_akun_biaya_listrik->value2)
                        ->select(DB::raw('SUM(jurnal_detail.debet - jurnal_detail.credit) as total_listrik'))
                        ->first();

        $biaya_operator = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
                        ->whereRaw('MONTH(tanggal_jurnal) = ' . $param_bulan)
                        ->whereRaw('YEAR(tanggal_jurnal) = ' . $param_tahun)
                        ->where('jurnal_header.void', 0)
                        ->whereNull('jurnal_header.id_transaksi')
                        ->where('jurnal_detail.id_akun', $get_akun_biaya_operator->value2)
                        ->select(DB::raw('SUM(jurnal_detail.debet - jurnal_detail.credit) as total_gaji'))
                        ->first();

        $data_beban_produksi = ProductionCost::join('produksi', 'produksi.id_produksi', 'beban_produksi.id_produksi')
            ->whereRaw('MONTH(tanggal_produksi) = ' . $param_bulan)
            ->whereRaw('YEAR(tanggal_produksi) = ' . $param_tahun)
            ->selectRaw('SUM(beban_produksi.tenaga_kerja_beban_produksi * beban_produksi.listrik_beban_produksi) as tenaga,
                SUM(beban_produksi.kwh_beban_produksi) as listrik')
            ->first();

        $avg_listrik = ($biaya_listrik->total_listrik && $data_beban_produksi->listrik)?$biaya_listrik->total_listrik / $data_beban_produksi->listrik:0;
        $avg_gaji = ($biaya_operator->total_gaji && $data_beban_produksi->tenaga)?$biaya_operator->total_gaji / $data_beban_produksi->tenaga:0;

        $data = [
          'listrik' => $avg_listrik,
          'gaji' => $avg_gaji
        ];

        return $data;
    }

    public function updateProductionCredit($id_produksi, $data_biaya){
        $beban_produksi = ProductionCost::where('id_produksi', $id_produksi)->first();

        $tenaga = ($beban_produksi->tenaga_kerja_beban_produksi * $beban_produksi->listrik_beban_produksi) * $data_biaya['gaji'];
        $listrik = $beban_produksi->kwh_beban_produksi * $data_biaya['listik'];

        $produksi_detail = ProductionDetail::join('barang', 'barang.id_barang', 'produksi_detail.id_barang')->where('id_produksi', $id_produksi)->groupBy('id_barang')->get();

        $kredit_produksi = [];

        foreach($produksi_detail as $detail){
            $qr_barang = ProductionDetail::join('master_qr_code', 'master_qr_code.kode_batang_master_qr_code', 'produksi_detail.kode_batang_produksi_detail')->where('id_produksi', $id_produksi)->where('id_barang', $detail->id_barang)->get();
            $sum_jumlah_master_qr_code = 0;

            foreach($qr_barang as $data){
                $sum_jumlah_master_qr_code += $data->jumlah_master_qr_code;
            }

            if($sum_jumlah_master_qr_code > 0){
                foreach($qr_barang as $data){
                    DB::table("master_qr_code")
                        ->where('id_barang', $data->id_barang)
                        ->where('kode_batang_master_qr_code', $data->kode_batang_produksi_detail)
                        ->update([
                            'listrik_master_qr_code' => $listrik/$sum_jumlah_master_qr_code,
                            'pegawai_master_qr_code' => $tenaga/$sum_jumlah_master_qr_code,
                        ]);
                }
            }

            $qr_barang_updated = ProductionDetail::join('master_qr_code', 'master_qr_code.kode_batang_master_qr_code', 'produksi_detail.kode_batang_produksi_detail')->where('id_produksi', $id_produksi)->where('id_barang', $detail->id_barang)
                    ->selectRaw('ROUND(
                                (jumlah_master_qr_code * produksi_master_qr_code) +
                                (jumlah_master_qr_code * listrik_master_qr_code) +
                                (jumlah_master_qr_code * pegawai_master_qr_code)
                            2) as jumlah, kode_batang_master_qr_code')
                    ->groupBy('kode_batang_master_qr_code')
                    ->first();

            $sum_kredit_detail = 0;

           foreach($qr_barang_updated as $data_qr){
                ProductionDetail::where('kode_batang_produksi_detail', $data_qr->kode_batang_master_qr_code)
                    ->update([
                        'kredit_produksi_detail' => $data_qr->jumlah
                    ]);

                $sum_kredit_detail += $data_qr->jumlah;
           }

           array_push($kredit_produksi, [
            'id_barang' => $detail->id_barang,
            'value' => $sum_kredit_detail,
            'id_akun' => $detail->id_akun
           ]);
        }

        $data = [
            'biaya' => [
                'tenaga' => $tenaga,
                'listrik' => $listrik
            ],
            'kredit_produksi' => $kredit_produksi
        ];

        return $data;
    }

    public function production(Request $request){
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

            $biaya_produksi = $this->getProductionCost($end_date, $id_cabang);

            $data_produksi = Production::whereRaw("MONTH(tanggal_produksi)", $month)->whereRaw('YEAR(tanggal_produksi)', $year)->where('id_jenis_transaksi', 17)->get();

            DB::beginTransaction();
            foreach($data_produksi as $produksi){
                $data_hpp = $this->updateProductionCredit($produksi->id_produksi, $biaya_produksi);
                $data_hpp_biaya = $data_hpp['biaya'];
                $data_hpp_kredit_hasil = $data_hpp['kredit_produksi'];

                $sumber_produksi = Production::where('nomor_referensi_produksi', $produksi->id_produksi)->first();

                $jurnal_header = JurnalHeader::where('id_transaksi', $sumber_produksi->nama_produksi)->where('jenis_jurnal', 'ME')->where('void', 0)->first();

                // update jurnal detail biaya
                $jurnal_biaya_listrik = JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->where('id_transaksi', 'Biaya Listrik')->first();
                $jurnal_biaya_listrik->credit = $data_hpp_biaya['listrik'];
                if (!$jurnal_biaya_listrik->save()) {
                    DB::rollback();
                     // Revert post closing
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    Log::error("Error when updating journal detail on update jurnal biaya listrik hpp produksi");
                    return FALSE;
                }

                $jurnal_biaya_operator = JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->where('id_transaksi', 'Biaya Operator')->first();
                $jurnal_biaya_operator->credit = $data_hpp_biaya['tenaga'];
                if (!$jurnal_biaya_operator->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    Log::error("Error when updating journal detail on update jurnal biaya operator hpp produksi");
                    return FALSE;
                }


                foreach($data_hpp_kredit_hasil as $kredit_hasil){
                    $jurnal_hasil_produksi = JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->where('id_transaksi', $kredit_hasil['id_barang'])->first();
                    $jurnal_hasil_produksi->debet = $kredit_hasil->value;
                    if (!$jurnal_hasil_produksi->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        Log::error("Error when updating journal detail on update jurnal hasil produksi " . $sumber_produksi->nama_produksi . " barang " . $kredit_hasil->id_barang . " hpp produksi");
                        return FALSE;
                    }
                }


                $jurnal_detail = JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->orderBy('index', 'ASC')->get();
                $jurnal_pembulatan = JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->where('id_transaksi', 'Pembulatan')->first();

                $sum_credit_jurnal = 0;
                $sum_debet_jurnal = 0;
                $index = 0;
                foreach($jurnal_detail as $detail){
                    if($detail->id_transaksi != 'Pembulatan'){
                        $sum_credit_jurnal += $detail->credit;
                        $sum_debet_jurnal += $detail->debet;
                        $index = $detail->index;
                    }
                }

                if($sum_credit_jurnal !=  $sum_debet_jurnal){
                    $selisih = $sum_credit_jurnal - $sum_debet_jurnal;

                    if(empty($jurnal_pembulatan)){
                        $get_akun_pembulatan = Setting::where("id_cabang", $id_cabang)->where("code", "Pembulatan")->first();

                        // Detail Biaya Listrik
                        $detail = new JurnalDetail();
                        $detail->id_jurnal = $jurnal_header->id_jurnal;
                        $detail->index = $index++;
                        $detail->id_akun = $get_akun_pembulatan->value2;
                        $detail->keterangan = "Pembulatan Produksi " . $sumber_produksi->nama_produksi;
                        $detail->id_transaksi = "Pembulatan";
                        if($selisih > 0){
                            $detail->debet = floatval($selisih);
                            $detail->credit = 0;
                        }else{
                            $detail->debet = 0;
                            $detail->credit = floatval(abs($selisih));
                        }
                        $detail->dt_created = $end_date;
                        $detail->dt_modified = $end_date;

                        if (!$detail->save()) {
                            DB::rollback();
                            $check = Closing::where("month", $month)->where("year", $year)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->delete();
                            }
                            Log::error("Error when storing journal detail on store jurnal pembulatan hpp produksi");
                            return FALSE;
                        }
                    }else{
                        if($selisih > 0){
                            $jurnal_pembulatan->debet = floatval($selisih);
                            $jurnal_pembulatan->credit = 0;
                        }else{
                            $jurnal_pembulatan->debet = 0;
                            $jurnal_pembulatan->credit = floatval(abs($selisih));
                        }

                        if (!$jurnal_pembulatan->save()) {
                            DB::rollback();
                            $check = Closing::where("month", $month)->where("year", $year)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->delete();
                            }
                            Log::error("Error when update journal detail on update jurnal pembulatan hpp produksi");
                            return FALSE;
                        }
                    }
                }
            }

            $jurnal_header = JurnalHeader::where('id_transaksi', "Selisih HPP Produksi " . date('Y m', strtotime($end_date)))->first();
            if(!empty($jurnal_header)){
                JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->delete();
                JurnalHeader::where('id_jurnal', $jurnal_header->id_jurnal)->delete();
            }

            $get_akun_biaya_listrik = Setting::where("id_cabang", $id_cabang)->where("code", "Biaya Listrik")->first();
            $get_akun_biaya_operator = Setting::where("id_cabang", $id_cabang)->where("code", "Biaya Operator")->first();

            $sum_biaya_listrik_manual = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
                                            ->whereRaw('MONTH(tanggal_jurnal)', $month)
                                            ->whereRaw('YEAR(tanggal_jurnal)', $year)
                                            ->where('void', 0)
                                            ->whereNull('jurnal_header.id_transaksi')
                                            ->where('jurnal_detail.id_akun', $get_akun_biaya_listrik->value2)
                                            ->selectRaw('ROUND(SUM(debet-credit), 2) as value')
                                            ->first();

            $sum_biaya_operator_manual = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
                                            ->whereRaw('MONTH(tanggal_jurnal)', $month)
                                            ->whereRaw('YEAR(tanggal_jurnal)', $year)
                                            ->where('void', 0)
                                            ->whereNull('jurnal_header.id_transaksi')
                                            ->where('jurnal_detail.id_akun', $get_akun_biaya_operator->value2)
                                            ->selectRaw('ROUND(SUM(debet-credit), 2) as value')
                                            ->first();

            $sum_biaya_listrik_otomatis = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
                                            ->whereRaw('MONTH(tanggal_jurnal)', $month)
                                            ->whereRaw('YEAR(tanggal_jurnal)', $year)
                                            ->where('void', 0)
                                            ->whereNotNull('jurnal_header.id_transaksi')
                                            ->whereRaw('jurnal_header.id_transaksi NOT LIKE "%Closing%"')
                                            ->where('jurnal_header.id_transaksi', '<>', 'Selisih HPP Produksi')
                                            ->where('jurnal_detail.id_akun', $get_akun_biaya_listrik->value2)
                                            ->selectRaw('ROUND(SUM(credit-debet), 2) as value')
                                            ->first();

            $sum_biaya_operator_otomatis = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
                                            ->whereRaw('MONTH(tanggal_jurnal)', $month)
                                            ->whereRaw('YEAR(tanggal_jurnal)', $year)
                                            ->where('void', 0)
                                            ->whereNotNull('jurnal_header.id_transaksi')
                                            ->whereRaw('jurnal_header.id_transaksi NOT LIKE "%Closing%"')
                                            ->where('jurnal_header.id_transaksi', '<>', 'Selisih HPP Produksi')
                                            ->where('jurnal_detail.id_akun', $get_akun_biaya_operator->value2)
                                            ->selectRaw('ROUND(SUM(credit-debet), 2) as value')
                                            ->first();

            $selisih_listrik = $sum_biaya_listrik_otomatis->value - $sum_biaya_listrik_manual->value;
            $selisih_tenaga = $sum_biaya_operator_otomatis->value - $sum_biaya_operator_manual->value;

            if($selisih_listrik != 0 || $selisih_tenaga != 0){
                // Create journal memorial
                // Store Header
                $header = new JurnalHeader();
                $header->id_cabang = $id_cabang;
                $header->jenis_jurnal = $journal_type;
                $header->id_transaksi = "Selisih HPP Produksi " . date('Y m', strtotime($end_date));
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
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table header",
                    ]);
                }


                $sum_selisih_debet = 0;
                $sum_selisih_credit = 0;
                $index = 0;

                if($selisih_listrik != 0){
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $index + 1;
                    $detail->id_akun = $get_akun_biaya_listrik->value2;
                    $detail->keterangan = "Selisih Produksi Biaya Listrik ". date('Y m', strtotime($end_date));
                    if($selisih_listrik > 0){
                        $detail->debet = floatval($selisih_listrik);
                        $detail->credit = 0;
                    }else{
                        $detail->debet = 0;
                        $detail->credit = floatval(abs($selisih_listrik));
                    }
                    $detail->user_created = NULL;
                    $detail->user_modified = NULL;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // Log::info(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail",
                        ]);
                    }

                    $sum_selisih_debet += $detail->debet;
                    $sum_selisih_credit += $detail->credit;
                }

                if($selisih_tenaga != 0){
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $index + 1;
                    $detail->id_akun = $get_akun_biaya_operator->value2;
                    $detail->keterangan = "Selisih Produksi Biaya Pegawai " . date('Y m', strtotime($end_date));
                    if($selisih_tenaga > 0){
                        $detail->debet = floatval($selisih_tenaga);
                        $detail->credit = 0;
                    }else{
                        $detail->debet = 0;
                        $detail->credit = floatval(abs($selisih_tenaga));
                    }
                    $detail->user_created = NULL;
                    $detail->user_modified = NULL;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // Log::info(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail",
                        ]);
                    }

                    $sum_selisih_debet += $detail->debet;
                    $sum_selisih_credit += $detail->credit;
                }

                if($sum_selisih_debet != $sum_selisih_credit){
                    $selisih_pembulatan = $sum_selisih_credit - $sum_selisih_debet;
                    // Detail Biaya Listrik
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $index;
                    $detail->id_akun = $get_akun_pembulatan->value2;
                    $detail->keterangan = "Pembulatan Produksi " . date('Y m', strtotime($end_date));
                    if($selisih_pembulatan > 0){
                        $detail->debet = floatval($selisih_pembulatan);
                        $detail->credit = 0;
                    }else{
                        $detail->debet = 0;
                        $detail->credit = floatval(abs($selisih_pembulatan));
                    }
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;

                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        Log::error("Error when storing journal detail on table detail");
                        return FALSE;
                    }
                }
            }

            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing journal Hpp Production"
            ]);
        }
        catch (\Exception $e) {
            DB::rollback();
            $check = Closing::where("month", $month)->where("year", $year)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->delete();
            }
            $message = "Error when closing journal Hpp Production";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
    }
    // End of Step 1

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
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->delete();
                }
                return response()->json([
                    "result" => FALSE,
                    "message" => "Jurnal Closing Transfer Cabang Gagal. Akun HPP Transfer Cabang tidak ditemukan"
                ]);
            }

            // Get data pindah barang
            $data_header = InventoryTransferHeader::where("id_cabang2", "<>", $id_cabang)->whereBetween("tanggal_pindah_barang", [$start_date, $end_date])->where("void", 0)->where("status_pindah_barang", 1)->get();
            $details_out = [];
            $details_in = [];
            // Log::info("jumlah data header");
            // Log::info(count($data_header));
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
                    $data_detail = InventoryTransferDetail::select("pindah_barang_detail.id_barang", "pindah_barang_detail.qr_code", "master_qr_code.beli_master_qr_code", "master_qr_code.biaya_beli_master_qr_code", "master_qr_code.jumlah_master_qr_code", "master_qr_code.produksi_master_qr_code", "master_qr_code.listrik_master_qr_code", "master_qr_code.pegawai_master_qr_code")->join("master_qr_code", "kode_batang_master_qr_code", "pindah_barang_detail.qr_code")->where("pindah_barang_detail.id_pindah_barang", $header->id_pindah_barang)->get();
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
                        // Revert post closing
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Jurnal Closing Transfer Cabang Gagal. Error when store Jurnal data on table header",
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
                            // Revert post closing
                            $check = Closing::where("month", $month)->where("year", $year)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->delete();
                            }
                            return response()->json([
                                "result" => false,
                                "message" => "Jurnal Closing Transfer Cabang Gagal. Error when store Jurnal data on table detail, barang not found",
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
                            // Revert post closing
                            $check = Closing::where("month", $month)->where("year", $year)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->delete();
                            }
                            return response()->json([
                                "result" => false,
                                "message" => "Jurnal Closing Transfer Cabang Gagal. Error when store Jurnal data on table detail",
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
                        // Revert post closing
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Jurnal Closing Transfer Cabang Gagal. Error when store Jurnal data on table detail",
                        ]);
                    }
                    // Log::info(json_encode($grouped_out));
                    // dd(json_encode($grouped_out));
                }
                else {
                    // Get header in detail
                    $data_detail = InventoryTransferDetail::select("pindah_barang_detail.id_barang", "pindah_barang_detail.qr_code", "master_qr_code.beli_master_qr_code", "master_qr_code.biaya_beli_master_qr_code", "master_qr_code.jumlah_master_qr_code", "master_qr_code.produksi_master_qr_code", "master_qr_code.listrik_master_qr_code", "master_qr_code.pegawai_master_qr_code")->join("master_qr_code", "kode_batang_master_qr_code", "pindah_barang_detail.qr_code")->where("pindah_barang_detail.id_pindah_barang", $header->id_pindah_barang)->get();
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
                        // Revert post closing
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Jurnal Closing Transfer Cabang Gagal. Error when store Jurnal data on table header",
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
                            // Revert post closing
                            $check = Closing::where("month", $month)->where("year", $year)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->delete();
                            }
                            return response()->json([
                                "result" => false,
                                "message" => "Jurnal Closing Transfer Cabang Gagal. Error when store Jurnal data on table detail, barang not found",
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
                            // Revert post closing
                            $check = Closing::where("month", $month)->where("year", $year)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->delete();
                            }
                            return response()->json([
                                "result" => false,
                                "message" => "Jurnal Closing Transfer Cabang Gagal. Error when store Jurnal data on table detail",
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
                        // Revert post closing
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Jurnal Closing Transfer Cabang Gagal. Error when store Jurnal data on table detail",
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
            DB::rollback();
            // Revert post closing
            $month = $request->month;
            $year = $request->year;
            $check = Closing::where("month", $month)->where("year", $year)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->delete();
            }
            $message = "Jurnal Closing Transfer Cabang Gagal. Error when inventory transfer";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
    }

    public function stockCorrection(Request $request)
    {
        try {
            // Init data
            $id_cabang = $request->id_cabang;
            $journal_type = "ME";
            $month = $request->month;
            $year = $request->year;
            $start_date = date("Y-m-d", strtotime("$year-$month-1"));
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));
            $status = 1;
            $hpp_account = Setting::where("id_cabang", $id_cabang)->where("code", "Koreksi Stok")->first();
            // dd($hpp_account);
            if (!$hpp_account) {
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->delete();
                }
                return response()->json([
                    "result" => FALSE,
                    "message" => "Jurnal Closing Koreksi Stok Gagal. Akun Koreksi Stok tidak ditemukan"
                ]);
            }

            // Get data koreksi stok
            $data_header = StockCorrectionHeader::where("status_koreksi_stok", $status)->where("id_cabang", $cabang)->whereBetween("tanggal_koreksi_stok", [$start_date, $end_date])->get();
            // dd(json_encode($data_header));
            $details = [];
            DB::beginTransaction();
            foreach ($data_header as $key => $header) {
                $id_transaksi = $header->nama_koreksi_stok;
                // Delete detail and header existing first
                JurnalDetail::where("id_transaksi", $id_transaksi)->where("keterangan", "Koreksi Stok ".$id_transaksi)->delete();
                JurnalHeader::where("id_transaksi", $id_transaksi)->where("catatan", "Koreksi Stok")->delete();
                // get koreksi stok detail
                // $data_detail = StockCorrectionDetail::select("id_koreksi_stok_detail", "id_barang", DB::raw("SUM(debit_koreksi_stok_detail) as debet"), DB::raw("SUM(kredit_koreksi_stok_detail) as kredit"))->where("id_koreksi_stok", $header->id_koreksi_stok)->groupBy("id_barang")->get();
                $data_detail = StockCorrectionDetail::selectRaw("koreksi_stok_detail.id_koreksi_stok, koreksi_stok_detail.id_barang, koreksi_stok_detail.debit_koreksi_stok_detail as debet, koreksi_stok_detail.kredit_koreksi_stok_detail as kredit, koreksi_stok_detail.kode_batang_koreksi_stok_detail, koreksi_stok_detail.kode_batang_lama_koreksi_stok_detail,
                ks.beli_master_qr_code as debet_beli, ks.biaya_beli_master_qr_code as debet_biaya_beli, ks.produksi_master_qr_code as debet_produksi, ks.listrik_master_qr_code as debet_listrik, ks.pegawai_master_qr_code as debet_pegawai,
                ksl.beli_master_qr_code as kredit_beli, ksl.biaya_beli_master_qr_code as kredit_biaya_beli, ksl.produksi_master_qr_code as kredit_produksi, ksl.listrik_master_qr_code as kredit_listrik, ksl.pegawai_master_qr_code as kredit_pegawai")
                ->leftJoin("master_qr_code as ks", "ks.kode_batang_master_qr_code", "koreksi_stok_detail.kode_batang_koreksi_stok_detail")
                ->leftJoin("master_qr_code as ksl", "ksl.kode_batang_lama_master_qr_code", "koreksi_stok_detail.kode_batang_lama_koreksi_stok_detail")
                ->where("koreksi_stok_detail.id_koreksi_stok", $header->id_koreksi_stok)->get();
                // dd(json_encode($data_detail));
                $i = 0;
                foreach ($data_detail as $key => $detail) {
                    // Get master qr code
                    $debet_value = ($detail->debet*$detail->debet_beli)+($detail->debet*$detail->debet_biaya_beli)+($detail->debet*$detail->debet_produksi)+($detail->debet*$detail->debet_listrik)+($detail->debet*$detail->debet_pegawai);
                    $kredit_value = ($detail->kredit*$detail->kredit_beli)+($detail->kredit*$detail->kredit_biaya_beli)+($detail->kredit*$detail->kredit_produksi)+($detail->kredit*$detail->kredit_listrik)+($detail->kredit*$detail->kredit_pegawai);
                    $sum = $debet_value + $kredit_value;
                    $details[] = [
                        "barang"=>$detail->id_barang,
                        "debet"=>$detail->debet,
                        "kredit"=>$detail->kredit,
                        "sum"=>$sum
                    ];
                }
                // dd(json_encode($details));
                // Grouping and sum the same barang
                $grouped = array_reduce($details, function($result, $in) {
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
                // dd(count($grouped));
                // Create journal memorial
                // Store Header
                $header = new JurnalHeader();
                $header->id_cabang = $id_cabang;
                $header->jenis_jurnal = $journal_type;
                $header->id_transaksi = $id_transaksi;
                $header->catatan = "Koreksi Stok";
                $header->void = 0;
                $header->tanggal_jurnal = $end_date;
                $header->user_created = NULL;
                $header->user_modified = NULL;
                $header->dt_created = $end_date;
                $header->dt_modified = $end_date;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
                // dd($header);
                if (!$header->save()) {
                    // Revert post closing
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Jurnal Closing Koreksi Stok Gagal. Error when store Jurnal data on table header",
                    ]);
                }
                // Store detail
                $i = 0;
                $sum_val = 0;
                // Log::info(json_encode($grouped_out));
                // Log::info(count($grouped_out));
                foreach ($grouped as $key => $out) {
                    // Get akun barang
                    $barang = Barang::find($key);
                    if (!$barang) {
                        DB::rollback();
                        // Revert post closing
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Jurnal Closing Koreksi Stok Gagal. Error when store Jurnal data on table detail, barang not found",
                        ]);
                    }
                    // Log::info(json_encode($barang->id_barang));
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $barang->id_akun;
                    $detail->keterangan = "Koreksi Stok ".$id_transaksi;
                    $detail->id_transaksi = $id_transaksi;
                    $detail->debet = ($out > 0)?$out:0;
                    $detail->credit = ($out > 0)?0:$out;
                    $detail->user_created = NULL;
                    $detail->user_modified = NULL;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // Log::info(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        // Revert post closing
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Jurnal Closing Koreksi Stok Gagal. Error when store Jurnal data on table detail",
                        ]);
                    }
                    $sum_val += $out;
                    $i++;
                }
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $i + 1;
                $detail->id_akun = $hpp_account->value2;
                $detail->keterangan = "Koreksi Stok ".$id_transaksi;
                $detail->id_transaksi = $id_transaksi;
                $detail->debet = ($sum_val > 0)?$sum_val:0;
                $detail->credit = ($sum_val > 0)?0:$sum_val;
                $detail->user_created = NULL;
                $detail->user_modified = NULL;
                $detail->dt_created = $end_date;
                $detail->dt_modified = $end_date;
                // dd(json_encode($detail));
                if (!$detail->save()) {
                    DB::rollback();
                    // Revert post closing
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Jurnal Closing Koreksi Stok Gagal. Error when store Jurnal data on table detail",
                    ]);
                }

            }
            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing journal stock correction"
            ]);
        }
        catch (\Exception $e) {
            DB::rollback();
            // Revert post closing
            $month = $request->month;
            $year = $request->year;
            $check = Closing::where("month", $month)->where("year", $year)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->delete();
            }
            $message = "Jurnal Closing Koreksi Stok Gagal. Error when stock correction";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
    }

    // step 4
    public function sellingReturn(Request $request)
    {
        try {
            $id_cabang = $request->id_cabang;
            $month = $request->month;
            $year = $request->year;
            $start_date = date("Y-m-d", strtotime("$year-$month-1"));
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));

            $data_retur_jual = DB::table("retur_penjualan as head")
            ->join('retur_penjualan_detail as det', 'head.id_retur_penjualan', 'det.id_retur_penjualan')
            ->join('barang as good', 'good.id_barang', 'det.id_barang')
            ->join('master_qr_code as qr', 'qr.kode_batang_master_qr_code', 'det.kode_batang_retur_penjualan_detail')
            ->selectRaw('det.nama_retur_penjualan_detail,
                det.id_barang,
                good.id_akun,
                ROUND(SUM(jumlah_retur_penjualan_detail),2) as jumlah_retur_penjualan_detail,
                ROUND(SUM(ROUND(qr.listrik_master_qr_code * det.jumlah_retur_penjualan_detail, 2) + ROUND(qr.pegawai_master_qr_code * det.jumlah_retur_penjualan_detail, 2) + ROUND(qr.produksi_master_qr_code * det.jumlah_retur_penjualan_detail, 2) + ROUND(qr.beli_master_qr_code * det.jumlah_retur_penjualan_detail, 2) + ROUND(qr.biaya_beli_master_qr_code * det.jumlah_retur_penjualan_detail, 2)), 2) as total')
            ->whereBetween('head.tanggal_retur_penjualan', [$start_date, $end_date])
            ->orderBy('head.id_retur_penjualan', 'ASC')
            ->groupBy('det.id_retur_penjualan', 'det.id_barang')
            ->get();

            $data_results = [];

            foreach($data_retur_jual as $retur_jual){
                array_push($data_results, [
                    'nama_transaksi' => $retur_jual->nama_retur_penjualan_detail,
                    'akun' => $retur_jual->id_akun,
                    'notes' => $retur_jual->id_barang,
                    'debet' => round($retur_jual->total, 2),
                    'kredit' => 0
                ]);
            }
            // Init Data
            $hasil = $data_results; // Diisi dengan data hasil
            $journalDate = date('Y-m-d');
            $journalDate = date("Y-m-t", strtotime($journalDate));
            $journalType = "ME";
            $cabangID = $id_cabang;
            $noteHeader = "";
            $dateRecord = date('Y-m-d');

            $get_akun_hpp_retur_jual = Setting::where("id_cabang", $cabangID)->where("code", "HPP Retur Penjualan")->first();
            if (!$get_akun_hpp_retur_jual) {
                DB::rollback();
                $check = Closing::where("month", $month)->where("year", $year)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->delete();
                }
                return response()->json([
                    "result" => FALSE,
                    "message" => "Jurnal Closing retur jual gagal. Akun HPP Retur Jual tidak ditemukan"
                ]);
            }

            $header = new JurnalHeader();
            $header->id_cabang = $cabangID;
            $header->jenis_jurnal = $journalType;
            $header->id_transaksi = NULL;
            $header->catatan = $noteHeader;
            $header->void = 0;
            $header->tanggal_jurnal = $journalDate;
            $header->user_created = NULL;
            $header->user_modified = NULL;
            $header->dt_created = $end_date;
            $header->dt_modified = $end_date;
            $header->kode_jurnal = $this->generateJournalCode($cabangID, $journalType);
            if (!$header->save()) {
                DB::rollback();
                $check = Closing::where("month", $month)->where("year", $year)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->delete();
                }
                Log::error("Error when storing journal header.");
                return response()->json([
                    "result" => false,
                    "message" => "Error when store Jurnal data on table header. In jurnal closing retur jual.",
                ]);
            }

            // Detail
            $index = 1;
            $total_debet = 0;
            $total_credit = 0;
            $list_transaksi = '';

            foreach ($hasil as $key => $value) {
                //Store Detail
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $index;
                $detail->id_akun = $value['akun'];
                $detail->keterangan = "Persediaan jurnal penjualan " . $value['nama_transaksi'];
                $detail->id_transaksi = $value['nama_transaksi'];
                $detail->debet = floatval($value['debet']);
                $detail->credit = floatval($value['kredit']);
                $detail->user_created = NULL;
                $detail->user_modified = NULL;
                $detail->dt_created = $end_date;
                $detail->dt_modified = $end_date;
                if (!$detail->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail. In jurnal closing retur jual",
                    ]);
                }

                $total_debet += $detail->debet;
                $total_credit += $detail->credit;
                $list_transaksi .= $value['nama_transaksi'] . ';';
                $index++;
            }

            $list_transaksi = substr_replace($list_transaksi,"",-1);
            $list_transaksi = explode(';', $list_transaksi);
            $list_transaksi = array_unique($list_transaksi);

            $transaksi = '';
            foreach ($list_transaksi as $key => $value) {
                $transaksi .= $value;
                if($key < count($list_transaksi) - 1){
                    $transaksi .= ', ';
                }
            }

            // pembulatan
            if($total_debet != $total_credit){
                $selisih = $total_credit - $total_debet;

                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $index;
                $detail->id_akun = $get_akun_hpp_retur_jual->value2;
                $detail->keterangan = "Pembulatan Persediaan jurnal penjualan " . $transaksi;
                $detail->id_transaksi = "Pembulatan";
                if($selisih > 0){
                    $detail->debet = floatval($selisih);
                    $detail->credit = 0;
                }else{
                    $detail->debet = 0;
                    $detail->credit = floatval(abs($selisih));
                }
                $detail->user_created = NULL;
                $detail->user_modified = NULL;
                $detail->dt_created = $end_date;
                $detail->dt_modified = $end_date;

                if (!$detail->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail. In jurnal closing retur jual.",
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing journal retur jual"
            ]);
        } catch (\Exception $e) {
            $message = "Error when closing journal retur jual";
            DB::rollback();
            $check = Closing::where("month", $month)->where("year", $year)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->delete();
            }
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
    }

    // step 5
    public function usage(Request $request)
    {
        try { 
            DB::beginTransaction();

            $id_cabang = $request->id_cabang;
            $month = $request->month;
            $year = $request->year;
            $start_date = date("Y-m-d", strtotime("$year-$month-1"));
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));

            $data_pemakaian = DB::table("pemakaian_header as head")
                ->join('pemakaian_detail as det', 'head.id_pemakaian', 'det.id_pemakaian')
                ->join('barang as good', 'good.id_barang', 'det.id_barang')
                ->join('master_qr_code as qr', 'qr.kode_batang_master_qr_code', 'det.kode_batang')
                ->selectRaw('head.kode_pemakaian,
                    det.id_barang,
                    good.id_akun,
                    ROUND(SUM(jumlah),2) as jumlah,
                    ROUND(SUM(ROUND(qr.listrik_master_qr_code * det.jumlah, 2) + ROUND(qr.pegawai_master_qr_code * det.jumlah, 2) + ROUND(qr.produksi_master_qr_code * det.jumlah, 2) + ROUND(qr.beli_master_qr_code * det.jumlah, 2) + ROUND(qr.biaya_beli_master_qr_code * det.jumlah, 2)), 2) as total')
                ->whereBetween('head.tanggal', [$start_date, $end_date])
                ->orderBy('head.id_pemakaian', 'ASC')
                ->groupBy('det.id_pemakaian', 'det.id_barang')
                ->get();

            $data_results = [];

            foreach($data_pemakaian as $pemakaian){
                array_push($data_results, [
                    'nama_transaksi' => $pemakaian->kode_pemakaian,
                    'akun' => $pemakaian->id_akun,
                    'notes' => $pemakaian->id_barang,
                    'debet' => 0,
                    'kredit' => round($pemakaian->total, 2),
                ]);
            }

            $hasil = $data_results;
            $journalDate = date('Y-m-d');
            $journalDate = date("Y-m-t", strtotime($journalDate));
            $journalType = "ME";
            $cabangID = $id_cabang;
            $noteHeader = "";
            $dateRecord = date('Y-m-d');

            $get_akun_hpp_pemakaian = Setting::where("id_cabang", $cabangID)->where("code", "HPP Pemakaian")->first();
            if (!$get_akun_hpp_pemakaian) {
                DB::rollback();
                $check = Closing::where("month", $month)->where("year", $year)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->delete();
                }
                return response()->json([
                    "result" => FALSE,
                    "message" => "Jurnal Closing pemakaian gagal. Akun HPP Pemakaian tidak ditemukan"
                ]);
            }

            $header = new JurnalHeader();
            $header->id_cabang = $cabangID;
            $header->jenis_jurnal = $journalType;
            $header->id_transaksi = NULL;
            $header->catatan = $noteHeader;
            $header->void = 0;
            $header->tanggal_jurnal = $journalDate;
            $header->user_created = NULL;
            $header->user_modified = NULL;
            $header->dt_created = $end_date;
            $header->dt_modified = $end_date;
            $header->kode_jurnal = $this->generateJournalCode($cabangID, $journalType);
            if (!$header->save()) {
                DB::rollback();
                $check = Closing::where("month", $month)->where("year", $year)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->delete();
                }
                Log::error("Error when storing journal header in journal closing pemakaian.");
                return response()->json([
                    "result" => false,
                    "message" => "Error when store Jurnal data on table header. In jurnal closing pemakaian",
                ]);
            }

            // Detail
            $index = 1;
            $total_debet = 0;
            $total_credit = 0;
            $list_transaksi = '';

            foreach ($hasil as $key => $value) {
                //Store Detail
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $index;
                $detail->id_akun = $value['akun'];
                $detail->keterangan = "Pemakaian barang " . $value['nama_transaksi'];
                $detail->id_transaksi = $value['nama_transaksi'];
                $detail->debet = floatval($value['debet']);
                $detail->credit = floatval($value['kredit']);
                $detail->user_created = NULL;
                $detail->user_modified = NULL;
                $detail->dt_created = $end_date;
                $detail->dt_modified = $end_date;
                if (!$detail->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    Log::error("Error when storing journal detail.");
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail. In jurnal closing pemakaian",
                    ]);
                }
                Log::debug($detail);

                $total_debet += $detail->debet;
                $total_credit += $detail->credit;
                $list_transaksi .= $value['nama_transaksi'] . ';';
                $index++;
            }

            $list_transaksi = substr_replace($list_transaksi,"",-1);
            $list_transaksi = explode(';', $list_transaksi);
            $list_transaksi = array_unique($list_transaksi);

            $transaksi = '';
            foreach ($list_transaksi as $key => $value) {
                $transaksi .= $value;
                if($key < count($list_transaksi) - 1){
                    $transaksi .= ', ';
                }
            }

            // pembulatan
            if($total_debet != $total_credit){
                $selisih = $total_credit - $total_debet;

                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $index;
                $detail->id_akun = $get_akun_hpp_pemakaian->value2;
                $detail->keterangan = "Pembulatan Pemakaian Barang " . $transaksi;
                $detail->id_transaksi = "Pembulatan";
                if($selisih > 0){
                    $detail->debet = floatval($selisih);
                    $detail->credit = 0;
                }else{
                    $detail->debet = 0;
                    $detail->credit = floatval(abs($selisih));
                }
                $detail->user_created = NULL;
                $detail->user_modified = NULL;
                $detail->dt_created = $end_date;
                $detail->dt_modified = $end_date;

                if (!$detail->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    Log::error("Error when storing journal detail pembulatan. In journal closing pemakaian");
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal pembulatan data on table detail. In jurnal closing pemakaian",
                    ]);
                }
                Log::debug($detail);
            }

            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing journal pemakaian"
            ]);
        } catch (\Exception $e) {
            $message = "Error when closing journal pemakaian";
            DB::rollback();
            $check = Closing::where("month", $month)->where("year", $year)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->delete();
            }
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
    }

    // step 6
    public function sales(Request $request){
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
                $data_detail = SalesDetail::select("penjualan_detail.id_barang", "penjualan_detail.kode_batang_lama_penjualan_detail", "master_qr_code.beli_master_qr_code", "master_qr_code.biaya_beli_master_qr_code", "master_qr_code.jumlah_master_qr_code", "master_qr_code.produksi_master_qr_code", "master_qr_code.listrik_master_qr_code", "master_qr_code.pegawai_master_qr_code")
                            ->join("master_qr_code", "kode_batang_master_qr_code", "penjualan_detail.kode_batang_lama_penjualan_detail")
                            ->where("id_penjualan", $header->id_penjualan)
                            ->get();

                $details = [];
                foreach ($data_detail as $key => $detail) {
                    $qty = $detail->jumlah_master_qr_code;
                    $sum = ($qty*$detail->beli_master_qr_code)+($qty*$detail->biaya_beli_master_qr_code)+($qty*$detail->produksi_master_qr_code)+($qty*$detail->listrik_master_qr_code)+($qty*$detail->pegawai_master_qr_code);
                    $details[] = [
                        "qr_code"=>$detail->kode_batang_lama_penjualan_detail,
                        "barang"=>$detail->id_barang,
                        "qty"=>$qty,
                        "sum"=>$sum
                    ];
                }
                // Log::info(json_encode($details));
                // Grouping and sum the same barang
                $grouped_out = array_reduce($details, function($result, $out) {
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
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
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
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
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
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
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
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
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

    // Step 7
    public function depreciation(Request $request){
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
            $cabang = Cabang::find($id_cabang);
            // dd($hpp_account);
            if (!$asset_account) {
                return response()->json([
                    "result" => FALSE,
                    "message" => "Akun Kategori Asset tidak ditemukan"
                ]);
            }

            // Get data pindah barang
            $data_asset = Barang::join('master_qr_code', 'master_qr_code.id_barang', 'barang.id_barang')
                            ->join('master_qr_code_detail', 'master_qr_code_detail.kode_batang_master_qr_code', 'master_qr_code.kode_batang_master_qr_code')
                            ->join('gudang', 'gudang.id_gudang', 'master_qr_code.id_gudang')
                            ->where("status_barang", 1)
                            ->where('gudang.id_cabang', $id_cabang)
                            ->where('id_kategori_barang', $asset_account->value2)
                            ->where('sisa_master_qr_code', '>', 0)
                            ->where('master_qr_code_detail.bulan', $month)
                            ->where('master_qr_code_detail.tahun', $year)
                            ->groupBy('barang.id_barang')
                            ->select(DB::raw('SUM(master_qr_code_detail.value) as susut'), 'barang.id_barang', 'barang.nama_barang', 'barang.id_akun', 'barang.id_akun2', 'barang.id_akun_biaya', 'barang.id_akun_biaya2')
                            ->get();

            DB::beginTransaction();
            $jurnal_header = JurnalHeader::where('id_transaksi', "Jurnal Penyusutan")->where('tanggal_jurnal', $end_date)->get();

            foreach($jurnal_header as $jurnal){
                JurnalDetail::where('id_jurnal', $jurnal->id_jurnal)->delete();
            }
            JurnalHeader::where('id_transaksi', "Jurnal Penyusutan")->where('tanggal_jurnal', $end_date)->delete();

            // dd($data_header);
            if(count($data_asset) > 0){
                // Create journal memorial
                // Store Header
                $header = new JurnalHeader();
                $header->id_cabang = $id_cabang;
                $header->jenis_jurnal = $journal_type;
                $header->id_transaksi = "Jurnal Penyusutan";
                // $header->catatan = "Closing Penjualan";
                $header->void = 0;
                $header->tanggal_jurnal = $end_date;
                $header->dt_created = $end_date;
                $header->dt_modified = $end_date;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
                // dd($header);
                if (!$header->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table header",
                    ]);
                }
                foreach ($data_asset as $asset) {
                    // Store detail
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = 1;
                    if(strtoupper($cabang->nama_cabang) == 'GEDANGAN'){
                        $detail->id_akun = $asset->id_akun;
                    }else if(strtoupper($cabang->nama_cabang) == 'JAKARTA'){
                        $detail->id_akun = $asset->id_akun2;
                    }
                    $detail->keterangan = "Biaya Penyusutan " . $asset->nama_barang;
                    // $detail->id_transaksi = $id_transaksi;
                    $detail->debet = $asset->susut;
                    $detail->credit = 0;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // Log::info(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail",
                        ]);
                    }

                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = 2;
                    if(strtoupper($cabang->nama_cabang) == 'GEDANGAN'){
                        $detail->id_akun = $asset->id_akun_biaya;
                    }else if(strtoupper($cabang->nama_cabang) == 'JAKARTA'){
                        $detail->id_akun = $asset->id_akun_biaya2;
                    }
                    $detail->keterangan = "Penyusutan ". $asset->nama_barang;
                    // $detail->id_transaksi = $id_transaksi;
                    $detail->debet = 0;
                    $detail->credit = $asset->susut;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // dd(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
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
            }else{
                return response()->json([
                    "result"=>TRUE,
                    "message"=>"Successfully proceed closing journal penyusutan, with status empty data"
                ]);
            }
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

    public function dummyAjax(Request $request)
    {
        sleep(5);
        return response()->json([
            "result"=>TRUE,
            "message"=>"Ajax function succeed"
        ]);
    }

    public function saldoTransfer(Request $request) {
        try {
            // Init Data
            $id_cabang = $request->id_cabang;
            $month = $request->month;
            $year = $request->year;
            $start_date = date("Y-m-d", strtotime("$year-$month-1"));
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));
            $nextMonth = date("n", strtotime("+1 month $start_date"));
            $nextYear = date("Y", strtotime("+1 month $start_date"));

            DB::beginTransaction();
            // Get all account that is shown 1
            $dataAkun = Akun::where("id_cabang". $id_cabang)->where("isshown", 1)->get();
            foreach ($dataAkun as $key => $akun) {
                // Get sum debet dan sum kredit
                $data_ledgers = JurnalDetail::join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                ->where("jurnal_header.void", "0")
                ->where("master_akun.id_cabang", $id_cabang)
                ->whereBetween("jurnal_header.tanggal_jurnal", [$start_date, $end_date])
                ->selectRaw("jurnal_header.id_jurnal, master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")->groupBy("jurnal_detail.id_akun");
                $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $value->id_akun)->where("id_cabang", $value->id_cabang)->where("bulan", $month)->where("tahun", $year)->first();
                $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                ->where("jurnal_detail.id_akun", $akun->id_akun)
                ->where("jurnal_header.id_cabang", $akun->id_cabang)
                ->where("jurnal_header.tanggal_jurnal", ">=", $start_date)
                ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                ->groupBy("jurnal_detail.id_akun")->first();
                $saldo_debet = ($saldo)?$saldo->saldo_debet:0;
                $saldo_kredit = ($saldo)?$saldo->saldo_kredit:0;
                $debet = ($data_saldo_ledgers)?$data_saldo_ledgers->debet:0;
                $kredit = ($data_saldo_ledgers)?$data_saldo_ledgers->kredit:0;
                // $saldo_awal = ($saldo_debet - $saldo_kredit) + ($debet - $kredit);
                // $saldo_akhir = $saldo_awal + $data_ledgers->debet - $data_ledgers->kredit;
                $saldo_debet = $saldo_debet + $debet + $data_ledgers->debet;
                $saldo_kredit = $saldo_kredit + $kredit + $data_ledgers->kredit;
                
                // Insert into saldo balance
                $saldo_balance = new SaldoBalance;
                $saldo_balance->id_cabang = $akun->id_cabang;
                $saldo_balance->id_akun = $akun->id_akun;
                $saldo_balance->bulan = $nextMonth;
                $saldo_balance->tahun = $nextYear;
                $saldo_balance->debet = $saldo_debet;
                $saldo_balance->credit = $saldo_kredit;
                if (!$saldo_balance->save()) {
                    // Revert post closing
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Jurnal Closing Transfer Saldo Gagal.",
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing journal stock correction"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            $message = "Error when transfer saldo";
            // Revert post closing
            $month = $request->month;
            $year = $request->year;
            $check = Closing::where("month", $month)->where("year", $year)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->delete();
            }
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
    }
}
