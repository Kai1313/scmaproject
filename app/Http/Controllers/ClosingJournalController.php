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
            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
            if ($check) {
                return response()->json([
                    "result" => FALSE,
                    "message" => "Closing sudah pernah dilakukan"
                ]);
            }
            $closing = new Closing;
            $closing->month = $month;
            $closing->year = $year;
            $closing->id_cabang = $id_cabang;
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
            $month = $request->month;
            $year = $request->year;
            $check = Closing::where("month", $month)->where("year", $year)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->delete();
            }
            $message = "Error when store closing";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
    }

    public function populate(Request $request)
    {
        $cabang = $request->cabang;
        $void = $request->void;
        $offset = $request->start;
        $limit = $request->length;
        $keyword = $request->search['value'];
        $sort = [];

        foreach ($request->order as $key => $order) {
            $columnIdx = $order['column'];
            $sortDir = $order['dir'];
            $sort[] = [
                'column' => $request->columns[$columnIdx]['name'],
                'dir' => $sortDir,
            ];
        }

        $draw = $request->draw;
        $current_page = $offset / $limit + 1;

        $data_closing = DB::table('closing')
            ->where('id_cabang', $cabang);

        if (isset($keyword)) {
            $data_closing->where(function ($query) use ($keyword) {
                $query->orWhere('month', 'LIKE', "%$keyword%")
                    ->orWhere('year', 'LIKE', "%$keyword%");
            });
        }

        $filtered_data = $data_closing->get();

        if ($sort[0]['column']) {
            if (!is_array($sort)) {
                $message = "Invalid array for parameter sort";
                $data = [
                    'result' => false,
                    'message' => $message,
                ];
                return response()->json($data);
            }

            foreach ($sort as $key => $s) {
                $column = $s['column'];
                $directon = $s['dir'];

                if ($column != '') {
                    $data_closing->orderBy($column, $directon);
                }
            }
        } else {
            $data_closing->orderBy('jurnal_header.id_jurnal', 'DESC');
        }

        // pagination
        if ($current_page) {
            $page = $current_page;
            $limit_data = $data_closing->count();

            if ($limit) {
                $limit_data = $limit;
            }

            $offset = ($page - 1) * $limit_data;
            if ($offset < 0) {
                $offset = 0;
            }

            $data_closing->skip($offset)->take($limit_data);
        }

        $table['draw'] = $draw;
        $table['recordsTotal'] = $data_closing->count();
        $table['recordsFiltered'] = $filtered_data->count();
        $table['data'] = $data_closing->get();

        return json_encode($table);
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
        try {
            Log::info("Void Jurnal Data");
            // exit();

            DB::beginTransaction();

            $closing = Closing::where('id_closing', $id)->first();
            $year = $closing->year;
            $month = $closing->month;
            $id_cabang = $closing->id_cabang;

            $start_date = date("Y-m-d", strtotime("$year-$month-1"));
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));
            $nextMonth = date("n", strtotime("+1 month $start_date"));
            $nextYear = date("Y", strtotime("+1 month $start_date"));

            if (checkAccessMenu('closing_journal', 'delete') == false) {
                return response()->json([
                    "result" => false,
                    "message" => "Maaf, tidak bisa delete jurnal dengan id " . $id . ", anda tidak punya akses!",
                ]);
            }

            // Delete saldo transfer if exist
            $delete = SaldoBalance::where("bulan", $nextMonth)->where("tahun", $nextYear)->where("id_cabang", $id_cabang)->delete();

            $closing = Closing::where('id_closing', $id)->delete();

            if (!$closing) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when delete Jurnal data",
                ]);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully delete Jurnal data",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when delete Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "message" => "Error when delete Jurnal data",
                "exception" => $e,
            ]);
        }
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
        $data_beban_produksi->listrik = ((int)$data_beban_produksi->listrik > 0)?$data_beban_produksi->listrik:0;
        $data_beban_produksi->tenaga = ((int)$data_beban_produksi->tenaga > 0)?$data_beban_produksi->tenaga:0;
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
                                            ->whereRaw('jurnal_header.id_transaksi NOT LIKE "%Selisih HPP Produksi%"')
                                            ->where('jurnal_detail.id_akun', $get_akun_biaya_listrik->value2)
                                            ->selectRaw('ROUND(SUM(credit-debet), 2) as value')
                                            ->first();

            $sum_biaya_operator_otomatis = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
                                            ->whereRaw('MONTH(tanggal_jurnal)', $month)
                                            ->whereRaw('YEAR(tanggal_jurnal)', $year)
                                            ->where('void', 0)
                                            ->whereNotNull('jurnal_header.id_transaksi')
                                            ->whereRaw('jurnal_header.id_transaksi NOT LIKE "%Closing%"')
                                            ->whereRaw('jurnal_header.id_transaksi NOT LIKE "%Selisih HPP Produksi%"')
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
            $month = $request->month;
            $year = $request->year;
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
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
                JurnalHeader::where("id_transaksi", "Closing ".$id_transaksi)->where("catatan", "Closing Transfer Barang Keluar")->delete();
                JurnalDetail::where("id_transaksi", $id_transaksi)->where("keterangan", "HPP Transfer Cabang Masuk ".$id_transaksi)->delete();
                JurnalHeader::where("id_transaksi", "Closing ".$id_transaksi)->where("catatan", "Closing Transfer Barang Masuk")->delete();
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
                    $header->id_transaksi = 'Closing ' . $id_transaksi;
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
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
                    $header->id_transaksi = 'Closing ' . $id_transaksi;
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
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
                            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
                            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
            $id_cabang = $request->id_cabang;
            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
            // dd('aaaa');
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
            $data_header = StockCorrectionHeader::where("status_koreksi_stok", $status)->where("id_cabang", $id_cabang)->whereBetween("tanggal_koreksi_stok", [$start_date, $end_date])->get();
            // dd(count($data_header));
            // dd(json_encode($data_header));
            $details = [];
            DB::beginTransaction();
            foreach ($data_header as $key => $header) {
                $id_transaksi = $header->nama_koreksi_stok;
                // Delete detail and header existing first
                JurnalDetail::where("id_transaksi", $id_transaksi)->where("keterangan", "Koreksi Stok ".$id_transaksi)->delete();
                JurnalHeader::where("id_transaksi", "Closing ".$id_transaksi)->where("catatan", "Koreksi Stok")->delete();
                // get koreksi stok detail
                // $data_detail = StockCorrectionDetail::select("id_koreksi_stok_detail", "id_barang", DB::raw("SUM(debit_koreksi_stok_detail) as debet"), DB::raw("SUM(kredit_koreksi_stok_detail) as kredit"))->where("id_koreksi_stok", $header->id_koreksi_stok)->groupBy("id_barang")->get();
                $data_detail = StockCorrectionDetail::selectRaw("koreksi_stok_detail.id_koreksi_stok_detail, koreksi_stok_detail.id_koreksi_stok, koreksi_stok_detail.id_barang, koreksi_stok_detail.debit_koreksi_stok_detail as debet, koreksi_stok_detail.kredit_koreksi_stok_detail as kredit, koreksi_stok_detail.kode_batang_koreksi_stok_detail, koreksi_stok_detail.kode_batang_lama_koreksi_stok_detail,
                ks.beli_master_qr_code as debet_beli, ks.biaya_beli_master_qr_code as debet_biaya_beli, ks.produksi_master_qr_code as debet_produksi, ks.listrik_master_qr_code as debet_listrik, ks.pegawai_master_qr_code as debet_pegawai,
                ksl.beli_master_qr_code as kredit_beli, ksl.biaya_beli_master_qr_code as kredit_biaya_beli, ksl.produksi_master_qr_code as kredit_produksi, ksl.listrik_master_qr_code as kredit_listrik, ksl.pegawai_master_qr_code as kredit_pegawai")
                ->leftJoin("master_qr_code as ks", "ks.kode_batang_master_qr_code", "koreksi_stok_detail.kode_batang_koreksi_stok_detail")
                ->leftJoin("master_qr_code as ksl", "ksl.kode_batang_lama_master_qr_code", "koreksi_stok_detail.kode_batang_lama_koreksi_stok_detail")
                ->where("koreksi_stok_detail.id_koreksi_stok", $header->id_koreksi_stok)
                // ->where("koreksi_stok_detail.id_koreksi_stok", "296")
                ->groupBy("koreksi_stok_detail.id_koreksi_stok_detail")->get();
                // dd(count($data_detail));
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
                $header->id_transaksi = 'Closing ' . $id_transaksi;
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
            $id_cabang = $request->id_cabang;
            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
            $journal_type = "ME";
            $void = 0;
            $status = 1;

            $hpp_account = Setting::where("id_cabang", $id_cabang)->where("code", "HPP Retur Penjualan")->first();
            // dd($hpp_account);
            if (!$hpp_account) {
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->delete();
                }
                
                return response()->json([
                    "result" => FALSE,
                    "message" => "Jurnal Closing Retur Penjualan Gagal. Akun Retur Penjualan tidak ditemukan"
                ]);
            }
            
            // Get data retur jual
            $data_header = DB::table('retur_penjualan')->where("id_cabang", $id_cabang)->whereBetween("tanggal_retur_penjualan", [$start_date, $end_date])->get();
            // dd($data_header);
            DB::beginTransaction();
            
            foreach ($data_header as $key => $header) {
                // dd($header);
                $id_transaksi = $header->nama_retur_penjualan;

                // Delete detail and header existing first
                $jurnal_header = JurnalHeader::where("id_transaksi", 'Closing ' . $id_transaksi)->where('tanggal_jurnal', $end_date)->where("catatan", "Closing Retur Penjualan")->get();
                // dd($jurnal_header);

                foreach($jurnal_header as $jurnal){
                    JurnalDetail::where("id_jurnal", $jurnal->id_jurnal)->delete();
                    JurnalHeader::where("id_jurnal", $jurnal->id_jurnal)->delete();
                }

                // Get header out detail
                $data_detail = DB::table('retur_penjualan_detail as det')
                    ->select("det.id_barang", "det.kode_batang_retur_penjualan_detail", "master_qr_code.beli_master_qr_code", "master_qr_code.biaya_beli_master_qr_code", "master_qr_code.jumlah_master_qr_code", "master_qr_code.produksi_master_qr_code", "master_qr_code.listrik_master_qr_code", "master_qr_code.pegawai_master_qr_code", "barang.nama_barang", DB::raw("IFNULL(satuan_barang.nama_satuan_barang, '') as nama_satuan"), "det.jumlah_retur_penjualan_detail")
                    ->join("master_qr_code", "kode_batang_master_qr_code", "det.kode_batang_retur_penjualan_detail")
                    ->join("barang", "barang.id_barang", "det.id_barang")
                    ->leftJoin("satuan_barang", "satuan_barang.id_satuan_barang", "det.id_satuan_barang")
                    ->where("id_retur_penjualan", $header->id_retur_penjualan)
                    ->get();

                // dd($data_detail);

                $details = [];
                foreach ($data_detail as $key => $detail) {
                    $qty = $detail->jumlah_master_qr_code;
                    $sum = ($qty*$detail->beli_master_qr_code)+($qty*$detail->biaya_beli_master_qr_code)+($qty*$detail->produksi_master_qr_code)+($qty*$detail->listrik_master_qr_code)+($qty*$detail->pegawai_master_qr_code);
                    $details[] = [
                        "qr_code"=>$detail->kode_batang_retur_penjualan_detail,
                        "barang"=>$detail->id_barang,
                        "qty"=>$qty,
                        "sum"=>$sum,
                        "note"=>$detail->nama_barang . ' - ' . $detail->jumlah_retur_penjualan_detail . ' ' . $detail->nama_satuan
                    ];
                }

                // Log::info(json_encode($details));
                // Grouping and sum the same barang
                $grouped_out = array_reduce($details, function($result, $out) {
                    $product = $out['barang'];
                    $sum = $out['sum'];
                    if (isset($result[$product])) {
                        $result[$product]['sum'] += $sum;
                    }
                    else {
                        // $result[$product] = $sum;
                        $result[$product]['sum'] = $sum;
                        $result[$product]['note'] = $out['note'];
                    }
                    return $result;
                }, []);

                // Create journal memorial
                // Store Header
                $header = new JurnalHeader();
                $header->id_cabang = $id_cabang;
                $header->jenis_jurnal = $journal_type;
                $header->id_transaksi = 'Closing ' . $id_transaksi;
                $header->catatan = "Closing Retur Penjualan";
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
                        "message" => "Store Closing retur penjualan failed, Error when store Jurnal data on table header",
                    ]);
                }

                // Store detail
                $i = 0;
                $sum_val = 0;
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
                            "message" => "Store Closing retur penjualan failed, Error when store Jurnal data on table detail, barang not found",
                        ]);
                    }

                    // akun persediaan barang
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $barang->id_akun;
                    $detail->keterangan = "Persediaan Jurnal Penjualan ". $id_transaksi . ' - ' . $out['note'];
                    $detail->id_transaksi = $id_transaksi;
                    $detail->debet = $out['sum'];
                    $detail->credit = 0;
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
                            "message" => "Store Closing retur penjualan failed, Error when store Jurnal data on table detail",
                        ]);
                    }
                    $sum_val += $out['sum'];
                    $i++;
                }

                // dd($detail);

                // akun hpp retur penjualan
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $i + 1;
                $detail->id_akun = $hpp_account->value2;
                $detail->keterangan = "Persediaan Jurnal Penjualan ".$id_transaksi;
                $detail->id_transaksi = $id_transaksi;
                $detail->debet = 0;
                $detail->credit = $sum_val;
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
                        "message" => "Store Closing retur penjualan failed, Error when store Jurnal data on table detail",
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing journal retur penjualan"
            ]);
        } catch (\Exception $e) {
            $message = "Error when closing journal retur penjualan";
            DB::rollback();
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

    // step 5
    public function usage(Request $request)
    {
        try {
            $id_cabang = $request->id_cabang;
            $month = $request->month;
            $year = $request->year;
            $start_date = date("Y-m-d", strtotime("$year-$month-1"));
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));
            $journal_type = "ME";
            $void = 0;
            $status = 1;

            $hpp_account = Setting::where("id_cabang", $id_cabang)->where("code", "HPP Pemakaian")->first();
            // dd($hpp_account);
            if (!$hpp_account) {
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->delete();
                }
                
                return response()->json([
                    "result" => FALSE,
                    "message" => "Jurnal Closing Pemakaian Gagal. Akun Pemakaian tidak ditemukan"
                ]);
            }

            DB::beginTransaction();

            // Get data pemakaian
            $data_header = DB::table('pemakaian_header')->where("id_cabang", $id_cabang)->whereBetween("tanggal", [$start_date, $end_date])->get();
            // dd($data_header);
            
            foreach ($data_header as $key => $header) {
                $id_transaksi = $header->kode_pemakaian;

                // Delete detail and header existing first
                $jurnal_header = JurnalHeader::where("id_transaksi", 'Closing ' . $id_transaksi)->where('tanggal_jurnal', $end_date)->where("catatan", "Closing Pemakaian")->get();
                // dd($jurnal_header);

                foreach($jurnal_header as $jurnal){
                    JurnalDetail::where("id_jurnal", $jurnal->id_jurnal)->delete();
                    JurnalHeader::where("id_jurnal", $jurnal->id_jurnal)->delete();
                }

                // Get header out detail
                $data_detail = DB::table('pemakaian_detail as det')
                    ->select("det.id_barang", "det.kode_batang", "master_qr_code.beli_master_qr_code", "master_qr_code.biaya_beli_master_qr_code", "master_qr_code.jumlah_master_qr_code", "master_qr_code.produksi_master_qr_code", "master_qr_code.listrik_master_qr_code", "master_qr_code.pegawai_master_qr_code", "barang.nama_barang", DB::raw("IFNULL(satuan_barang.nama_satuan_barang, '') as nama_satuan"), "det.jumlah")
                    ->join("master_qr_code", "kode_batang_master_qr_code", "det.kode_batang")
                    ->join("barang", "barang.id_barang", "det.id_barang")
                    ->leftJoin("satuan_barang", "satuan_barang.id_satuan_barang", "det.id_satuan_barang")
                    ->where("id_pemakaian", $header->id_pemakaian)
                    ->get();

                // dd($data_detail);

                $details = [];
                foreach ($data_detail as $key => $detail) {
                    $qty = $detail->jumlah_master_qr_code;
                    $sum = ($qty*$detail->beli_master_qr_code)+($qty*$detail->biaya_beli_master_qr_code)+($qty*$detail->produksi_master_qr_code)+($qty*$detail->listrik_master_qr_code)+($qty*$detail->pegawai_master_qr_code);
                    $details[] = [
                        "qr_code"=>$detail->kode_batang,
                        "barang"=>$detail->id_barang,
                        "qty"=>$qty,
                        "sum"=>$sum,
                        "note"=>$detail->nama_barang . ' - ' . $detail->jumlah . ' ' . $detail->nama_satuan
                    ];
                }

                // Log::info(json_encode($details));
                // Grouping and sum the same barang
                $grouped_out = array_reduce($details, function($result, $out) {
                    $product = $out['barang'];
                    $sum = $out['sum'];
                    if (isset($result[$product])) {
                        $result[$product]['sum'] += $sum;
                    }
                    else {
                        // $result[$product] = $sum;
                        $result[$product]['sum'] = $sum;
                        $result[$product]['note'] = $out['note'];
                    }
                    return $result;
                }, []);

                // dd($grouped_out);

                // Create journal memorial
                // Store Header
                $header = new JurnalHeader();
                $header->id_cabang = $id_cabang;
                $header->jenis_jurnal = $journal_type;
                $header->id_transaksi = 'Closing ' . $id_transaksi;
                $header->catatan = "Closing Pemakaian";
                $header->void = 0;
                $header->tanggal_jurnal = $end_date;
                $header->user_created = NULL;
                $header->user_modified = NULL;
                $header->dt_created = $end_date;
                $header->dt_modified = $end_date;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);

                if (!$header->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Store Closing pemakaian failed, Error when store Jurnal data on table header",
                    ]);
                }

                // Store detail
                $i = 0;
                $sum_val = 0;
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
                            "message" => "Store Closing pemakaian failed, Error when store Jurnal data on table detail, barang not found",
                        ]);
                    }

                    // akun persediaan barang
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $barang->id_akun;
                    $detail->keterangan = "Pemakaian Barang ". $id_transaksi . ' - ' . $out['note'];
                    $detail->id_transaksi = $id_transaksi;
                    $detail->debet = 0;
                    $detail->credit = $out['sum'];
                    $detail->user_created = NULL;
                    $detail->user_modified = NULL;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // dd($detail);

                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Store Closing pemakaian failed, Error when store Jurnal data on table detail",
                        ]);
                    }
                    $sum_val += $out['sum'];
                    $i++;
                }

                // akun hpp pemakaian
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $i + 1;
                $detail->id_akun = $hpp_account->value2;
                $detail->keterangan = "Pemakaian barang ".$id_transaksi;
                $detail->id_transaksi = $id_transaksi;
                $detail->debet = $sum_val;
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
                        "message" => "Store Closing pemakaian failed, Error when store Jurnal data on table detail",
                    ]);
                }
            }
            
            DB::commit();

            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing journal pemakaian"
            ]);
        } catch (\Exception $e) {
            $message = "Error when closing journal pemakaian";
            DB::rollback();
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
                $jurnal_header = JurnalHeader::where("id_transaksi", 'Closing ' . $id_transaksi)->where('tanggal_jurnal', $end_date)->where("catatan", "Closing Penjualan")->get();

                foreach($jurnal_header as $jurnal){
                    JurnalDetail::where("id_jurnal", $jurnal->id_jurnal)->delete();
                    JurnalHeader::where("id_jurnal", $jurnal->id_jurnal)->delete();
                }


                // Get header out detail
                $data_detail = SalesDetail::select("penjualan_detail.id_barang", "penjualan_detail.kode_batang_lama_penjualan_detail", "master_qr_code.beli_master_qr_code", "master_qr_code.biaya_beli_master_qr_code", "master_qr_code.jumlah_master_qr_code", "master_qr_code.produksi_master_qr_code", "master_qr_code.listrik_master_qr_code", "master_qr_code.pegawai_master_qr_code", "barang.nama_barang", DB::raw("IFNULL(satuan_barang.nama_satuan_barang, '') as nama_satuan"), "penjualan_detail.jumlah_penjualan_detail")
                            ->join("master_qr_code", "kode_batang_master_qr_code", "penjualan_detail.kode_batang_lama_penjualan_detail")
                            ->join("barang", "barang.id_barang", "penjualan_detail.id_barang")
                            ->leftJoin("satuan_barang", "satuan_barang.id_satuan_barang", "penjualan_detail.id_satuan_barang")
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
                        "sum"=>$sum,
                        "note"=>$detail->nama_barang . ' - ' . $detail->jumlah_penjualan_detail . ' ' . $detail->nama_satuan
                    ];
                }
                // Log::info(json_encode($details));
                // Grouping and sum the same barang
                $grouped_out = array_reduce($details, function($result, $out) {
                    $product = $out['barang'];
                    $sum = $out['sum'];
                    if (isset($result[$product])) {
                        $result[$product]['sum'] += $sum;
                    }
                    else {
                        // $result[$product] = $sum;
                        $result[$product]['sum'] = $sum;
                        $result[$product]['note'] = $out['note'];
                    }
                    return $result;
                }, []);

                // Create journal memorial
                // Store Header
                $header = new JurnalHeader();
                $header->id_cabang = $id_cabang;
                $header->jenis_jurnal = $journal_type;
                $header->id_transaksi = 'Closing ' . $id_transaksi;
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

                    // akun persediaan barang
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $barang->id_akun;
                    $detail->keterangan = "Harga Produksi Penjualan ".$id_transaksi . ' - ' . $out['note'];
                    // $detail->id_transaksi = $id_transaksi;
                    $detail->debet = 0;
                    $detail->credit = $out['sum'];
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
                    $i++;

                    if($id_cabang == 1){
                        $akun_hpp_penjualan = $barang->id_akun_hpp_penjualan;
                    }else{
                        $format_akun = 'id_akun_hpp_penjualan' . $id_cabang;
                        $akun_hpp_penjualan = $barang->$format_akun;
                    }

                    if($akun_hpp_penjualan == null){
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail. Akun HPP Penjualan Barang " . $barang->kode_barang . ' - ' . $barang->nama_barang . ' can not null.',
                        ]);
                    }else{
                        $data_akun_penjualan_barang = Akun::find($akun_hpp_penjualan);
                        if(empty($data_akun_penjualan_barang)){
                            DB::rollback();
                            $check = Closing::where("month", $month)->where("year", $year)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->delete();
                            }
                            return response()->json([
                                "result" => false,
                                "message" => "Error when store Jurnal data on table detail. Akun HPP Penjualan Barang " . $barang->kode_barang . ' - ' . $barang->nama_barang . ' not found.',
                            ]);
                        }
                    }

                    // akun hpp penjualan
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $akun_hpp_penjualan;
                    $detail->keterangan = "Harga Produksi Penjualan ".$id_transaksi;
                    // $detail->id_transaksi = $id_transaksi;
                    $detail->debet = $out['sum'];
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
                    $i++;
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
            DB::rollback();
            $month = $request->month;
            $year = $request->year;
            $check = Closing::where("month", $month)->where("year", $year)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->delete();
            }
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
            }
            else{
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

    // Step 8
    public function closingJournal(Request $request) {
        try {
            // Init data
            $id_cabang = $request->id_cabang;
            $journal_type = "ME";
            $month = $request->month;
            $year = $request->year;
            $start_date = date("Y-m-d", strtotime("$year-$month-1"));
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));
            $noteDate = date("M Y", strtotime($start_date));
            $void = 0;
            $status = 1;
            $closing_account = Setting::where("id_cabang", $id_cabang)->where("code", "Closing")->first();
            $profitloss_account = Setting::where("id_cabang", $id_cabang)->where("code", "LR Berjalan")->first();
            // Log::info("akun closing");
            // Log::info(json_encode($closing_account));
            // Log::info("akun laba rugi");
            // Log::info(json_encode($profitloss_account));
            if (!$closing_account || !$profitloss_account) {
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->delete();
                }
                return response()->json([
                    "result" => FALSE,
                    "message" => "Jurnal Closing Closing Jurnal Gagal. Akun Closing atau Laba Rugi tidak ditemukan"
                ]);
            }

            DB::beginTransaction();
            // Delete all journal before transaction
            $jurnal_header = JurnalHeader::where("id_transaksi", "Closing 1 $noteDate")->where('tanggal_jurnal', $end_date)->where("catatan", "Closing 1 $noteDate")->get();
            // dd(count($jurnal_header));
            foreach($jurnal_header as $jurnal){
                JurnalDetail::where("id_jurnal", $jurnal->id_jurnal)->delete();
                JurnalHeader::where("id_jurnal", $jurnal->id_jurnal)->delete();
            }
            $jurnal_header2 = JurnalHeader::where("id_transaksi", "Closing 2 $noteDate")->where('tanggal_jurnal', $end_date)->where("catatan", "Closing 2 $noteDate")->get();
            // dd(count($jurnal_header2));
            foreach($jurnal_header2 as $jurnal2){
                JurnalDetail::where("id_jurnal", $jurnal2->id_jurnal)->delete();
                JurnalHeader::where("id_jurnal", $jurnal2->id_jurnal)->delete();
            }

            // Get all journal based on tipe laba rugi, void 0, between startdate - enddate
            $data_ledgers = JurnalDetail::join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                ->where("jurnal_header.void", "0")
                ->where("master_akun.tipe_akun", "1")
                ->where("master_akun.id_cabang", $id_cabang)
                ->whereRaw("((jurnal_header.id_transaksi <> 'Closing 1 $noteDate' AND jurnal_header.id_transaksi <> 'Closing 2 $noteDate') OR jurnal_header.id_transaksi IS NULL)")
                ->whereBetween("jurnal_header.tanggal_jurnal", [$start_date, $end_date])
                ->selectRaw("jurnal_header.id_jurnal, master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")->groupBy("jurnal_detail.id_akun")->get();
            // Log::info(count($data_ledgers));
            // Create closing step 1
            $header = new JurnalHeader();
            $header->id_cabang = $id_cabang;
            $header->jenis_jurnal = $journal_type;
            $header->id_transaksi = "Closing 1 $noteDate";
            $header->catatan = "Closing 1 $noteDate";
            $header->void = 0;
            $header->tanggal_jurnal = $end_date;
            $header->user_created = NULL;
            $header->user_modified = NULL;
            $header->dt_created = $end_date;
            $header->dt_modified = $end_date;
            $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
            // Log::info(json_encode($header));
            if (!$header->save()) {
                DB::rollback();
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                }
                return response()->json([
                    "result" => false,
                    "message" => "Jurnal Closing Closing Journal Gagal. Error when store Jurnal data on table header 1",
                ]);
            }
            $closingSum = 0;
            $i = 0;
            foreach ($data_ledgers as $key => $value) {
                // Calculate sum
                $sum = $value->debet - $value->kredit;
                // Log::info("closing sum ".$closingSum." debet ".$value->debet." kredit ".$value->kredit);
                $closingSum = (float)$closingSum + (float)$sum;
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $i + 1;
                $detail->id_akun = $value->id_akun;
                $detail->keterangan = "Jurnal Closing 1 $noteDate";
                $detail->id_transaksi = NULL;
                $detail->debet = ($sum < 0)?abs($sum):0;
                $detail->credit = ($sum < 0)?0:$sum;
                $detail->user_created = NULL;
                $detail->user_modified = NULL;
                $detail->dt_created = $end_date;
                $detail->dt_modified = $end_date;
                // Log::info(json_encode($detail));
                // Log::info($closingSum);
                if (!$detail->save()) {
                    DB::rollback();
                    // Revert post closing
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Jurnal Closing Closing Journal Gagal. Error when store Jurnal data on table detail 1.1",
                    ]);
                }
                $i++;
            }
            // Detail end closing 1
            $detailClosing1 = new JurnalDetail();
            $detailClosing1->id_jurnal = $header->id_jurnal;
            $detailClosing1->index = $i + 1;
            $detailClosing1->id_akun = $closing_account->value2;
            $detailClosing1->keterangan = "Jurnal Closing 1 $noteDate";
            $detailClosing1->id_transaksi = NULL;
            $detailClosing1->debet = ($closingSum < 0)?abs($closingSum):0;
            $detailClosing1->credit = ($closingSum < 0)?0:$closingSum;
            $detailClosing1->user_created = NULL;
            $detailClosing1->user_modified = NULL;
            $detailClosing1->dt_created = $end_date;
            $detailClosing1->dt_modified = $end_date;
            // Log::info(json_encode($detailClosing1));
            // Log::info($closingSum);
            if (!$detailClosing1->save()) {
                DB::rollback();
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                }
                return response()->json([
                    "result" => false,
                    "message" => "Jurnal Closing Closing Journal Gagal. Error when store Jurnal data on table detail 1.2",
                ]);
            }

            // Create closing step 2
            $header2 = new JurnalHeader();
            $header2->id_cabang = $id_cabang;
            $header2->jenis_jurnal = $journal_type;
            $header2->id_transaksi = "Closing 2 $noteDate";
            $header2->catatan = "Closing 2 $noteDate";
            $header2->void = 0;
            $header2->tanggal_jurnal = $end_date;
            $header2->user_created = NULL;
            $header2->user_modified = NULL;
            $header2->dt_created = $end_date;
            $header2->dt_modified = $end_date;
            $header2->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
            // Log::info(json_encode($header2));
            if (!$header2->save()) {
                DB::rollback();
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                }
                return response()->json([
                    "result" => false,
                    "message" => "Jurnal Closing Closing Journal Gagal. Error when store Jurnal data on table header 2",
                ]);
            }
            // Detail closing 2.1
            $detailClosing21 = new JurnalDetail();
            $detailClosing21->id_jurnal = $header2->id_jurnal;
            $detailClosing21->index = 1;
            $detailClosing21->id_akun = $closing_account->value2;
            $detailClosing21->keterangan = "Jurnal Closing 2 $noteDate";
            $detailClosing21->id_transaksi = NULL;
            $detailClosing21->debet = ($closingSum < 0)?0:abs($closingSum);
            $detailClosing21->credit = ($closingSum < 0)?$closingSum:0;
            $detailClosing21->user_created = NULL;
            $detailClosing21->user_modified = NULL;
            $detailClosing21->dt_created = $end_date;
            $detailClosing21->dt_modified = $end_date;
            // Log::info(json_encode($detailClosing21));
            // Log::info($closingSum);
            if (!$detailClosing21->save()) {
                DB::rollback();
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                }
                return response()->json([
                    "result" => false,
                    "message" => "Jurnal Closing Closing Journal Gagal. Error when store Jurnal data on table detail 2.1",
                ]);
            }
            // Detail closing 2.2
            $detailClosing22 = new JurnalDetail();
            $detailClosing22->id_jurnal = $header2->id_jurnal;
            $detailClosing22->index = 2;
            $detailClosing22->id_akun = $profitloss_account->value2;
            $detailClosing22->keterangan = "Jurnal Closing 2 $noteDate";
            $detailClosing22->id_transaksi = NULL;
            $detailClosing22->debet = ($closingSum < 0)?abs($closingSum):0;
            $detailClosing22->credit = ($closingSum < 0)?0:$closingSum;
            $detailClosing22->user_created = NULL;
            $detailClosing22->user_modified = NULL;
            $detailClosing22->dt_created = $end_date;
            $detailClosing22->dt_modified = $end_date;
            // Log::info(json_encode($detailClosing22));
            // Log::info($closingSum);
            if (!$detailClosing22->save()) {
                DB::rollback();
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                }
                return response()->json([
                    "result" => false,
                    "message" => "Jurnal Closing Closing Journal Gagal. Error when store Jurnal data on table detail 2.2",
                ]);
            }
            DB::commit();
            return response()->json([
                "result"=>TRUE,
                "message"=>"Successfully proceed closing closing journal"
            ]);
        } 
        catch (\Exception $e) {
            DB::rollback();
            // Revert post closing
            $month = $request->month;
            $year = $request->year;
            $id_cabang = $request->id_cabang;
            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
            }
            $message = "Jurnal Closing Closing Jurnal Gagal. Error when step 8";
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
            // Delete saldo transfer if exist
            $delete = SaldoBalance::where("bulan", $nextMonth)->where("tahun", $nextYear)->where("id_cabang", $id_cabang)->delete();

            // Get all account that is shown 1
            $dataAkun = Akun::where("id_cabang", $id_cabang)->where("isshown", 1)->get();
            $debet = 0;
            $kredit = 0;
            // dd(count($dataAkun));
            foreach ($dataAkun as $key => $akun) {
                // Get sum debet dan sum kredit
                // $data_ledgers = JurnalDetail::join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                // ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                // ->where("jurnal_header.void", "0")
                // ->where("master_akun.id_cabang", $id_cabang)
                // ->whereBetween("jurnal_header.tanggal_jurnal", [$start_date, $end_date])
                // ->selectRaw("jurnal_header.id_jurnal, master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")->groupBy("jurnal_detail.id_akun")->first();
                $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $akun->id_akun)->where("id_cabang", $akun->id_cabang)->where("bulan", $month)->where("tahun", $year)->first();
                $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                ->where("jurnal_detail.id_akun", $akun->id_akun)
                ->where("jurnal_header.id_cabang", $akun->id_cabang)
                ->where("jurnal_header.void", "0")
                ->where("jurnal_header.tanggal_jurnal", ">=", $start_date)
                ->where("jurnal_header.tanggal_jurnal", "<=", $end_date)
                ->groupBy("jurnal_detail.id_akun")->first();
                $saldo_debet = ($saldo)?$saldo->saldo_debet:0;
                $saldo_kredit = ($saldo)?$saldo->saldo_kredit:0;
                // Log::info("saldo debet ".$saldo_debet." saldo kredit ".$saldo_kredit);
                $debet = ($data_saldo_ledgers)?$data_saldo_ledgers->debet:0;
                $kredit = ($data_saldo_ledgers)?$data_saldo_ledgers->kredit:0;
                // Log::info("saldo debet ".$debet." saldo kredit ".$kredit);
                $saldo_debet = $saldo_debet + $debet + (isset($data_ledgers->debet) ? $data_ledgers->debet : 0) ;
                $saldo_kredit = $saldo_kredit + $kredit + (isset($data_ledgers->kredit) ? $data_ledgers->kredit : 0);
                $saldoAkhir = (float) $saldo_debet - (float) $saldo_kredit;

                // Insert into saldo balance
                $saldo_balance = new SaldoBalance;
                $saldo_balance->id_cabang = $akun->id_cabang;
                $saldo_balance->id_akun = $akun->id_akun;
                $saldo_balance->bulan = $nextMonth;
                $saldo_balance->tahun = $nextYear;
                $saldo_balance->debet = ($saldoAkhir > 0)?$saldoAkhir:0;//$saldo_debet;
                $saldo_balance->credit = ($saldoAkhir > 0)?0:$saldoAkhir;//$saldo_kredit;
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
                "message"=>"Successfully proceed closing transfer saldo"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            $message = "Error when transfer saldo";
            // Revert post closing
            $month = $request->month;
            $year = $request->year;
            $id_cabang = $request->id_cabang;
            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
