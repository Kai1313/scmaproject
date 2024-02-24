<?php

namespace App\Http\Controllers;

use App\Barang;
use App\MasterQrCode;
use App\Models\Accounting\Closing;
use App\Models\Accounting\InventoryTransferDetail;
use App\Models\Accounting\InventoryTransferHeader;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
use App\Models\Accounting\SaldoBalance;
use App\Models\Accounting\StockCorrectionDetail;
use App\Models\Accounting\StockCorrectionHeader;
use App\Models\Master\Akun;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClosingJournalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (checkUserSession($request, 'transaction/closing_journal', 'show') == false) {
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
                    "result" => false,
                    "message" => "Closing sudah pernah dilakukan",
                ]);
            }

            $jurnal_header = JurnalHeader::whereRaw("id_transaksi LIKE '%Closing%'")->whereYear('tanggal_jurnal', $year)->whereMonth("tanggal_jurnal", $month)->where("id_cabang", $id_cabang)->get();
            // dd(count($jurnal_header));
            foreach ($jurnal_header as $jurnal) {
                JurnalDetail::where("id_jurnal", $jurnal->id_jurnal)->delete();
                JurnalHeader::where("id_jurnal", $jurnal->id_jurnal)->delete();
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
                "result" => true,
                "message" => "Successfully proceed closing journal data",
            ]);
        } catch (\Exception $e) {
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
                "result" => false,
                "message" => $message,
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

            if (checkAccessMenu('transaction/closing_journal', 'delete') == false) {
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
    public function getProductionCost($date, $id_cabang)
    {
        $param_bulan = date('m', strtotime($date));
        $param_tahun = date('Y', strtotime($date));

        $get_akun_biaya_listrik = Setting::where("id_cabang", $id_cabang)->where("code", "Biaya Listrik")->first();
        $get_akun_biaya_operator = Setting::where("id_cabang", $id_cabang)->where("code", "Biaya Operator")->first();
        $get_akun_pembulatan = Setting::where("id_cabang", $id_cabang)->where("code", "Pembulatan")->first();

        $biaya_listrik = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
            ->whereYear('tanggal_jurnal', $param_tahun)
            ->whereMonth('tanggal_jurnal', $param_bulan)
            ->where('jurnal_header.id_cabang', $id_cabang)
            ->where('jurnal_header.void', 0)
            ->whereNull('jurnal_header.id_transaksi')
            ->where('jurnal_detail.id_akun', $get_akun_biaya_listrik->value2)
            ->select(DB::raw('SUM(jurnal_detail.debet - jurnal_detail.credit) as total_listrik'))
            ->first();

        $biaya_operator = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
            ->whereYear('tanggal_jurnal', $param_tahun)
            ->whereMonth('tanggal_jurnal', $param_bulan)
            ->where('jurnal_header.id_cabang', $id_cabang)
            ->where('jurnal_header.void', 0)
            ->whereNull('jurnal_header.id_transaksi')
            ->where('jurnal_detail.id_akun', $get_akun_biaya_operator->value2)
            ->select(DB::raw('SUM(jurnal_detail.debet - jurnal_detail.credit) as total_gaji'))
            ->first();

        $data_beban_produksi = ProductionCost::join('produksi', 'produksi.id_produksi', 'beban_produksi.id_produksi')
            ->whereYear('tanggal_produksi', $param_tahun)
            ->whereMonth('tanggal_produksi', $param_bulan)
            ->where('id_cabang', $id_cabang)
            ->selectRaw('SUM(beban_produksi.tenaga_kerja_beban_produksi * beban_produksi.listrik_beban_produksi) as tenaga,
                SUM(beban_produksi.kwh_beban_produksi) as listrik')
            ->first();

        $data_beban_produksi->listrik = ((int) $data_beban_produksi->listrik > 0) ? $data_beban_produksi->listrik : 0;
        $data_beban_produksi->tenaga = ((int) $data_beban_produksi->tenaga > 0) ? $data_beban_produksi->tenaga : 0;
        $avg_listrik = ($biaya_listrik->total_listrik && $data_beban_produksi->listrik) ? $biaya_listrik->total_listrik / $data_beban_produksi->listrik : 0;
        $avg_gaji = ($biaya_operator->total_gaji && $data_beban_produksi->tenaga) ? $biaya_operator->total_gaji / $data_beban_produksi->tenaga : 0;

        $data = [
            'listrik' => $avg_listrik,
            'gaji' => $avg_gaji,
        ];

        return $data;
    }

    public function updateProductionCredit($id_produksi, $data_biaya)
    {
        $beban_produksi = ProductionCost::where('id_produksi', $id_produksi)->first();

        $tenaga = ($beban_produksi->tenaga_kerja_beban_produksi * $beban_produksi->listrik_beban_produksi) * $data_biaya['gaji'];
        $listrik = $beban_produksi->kwh_beban_produksi * $data_biaya['listrik'];

        $produksi_detail = ProductionDetail::join('barang', 'barang.id_barang', 'produksi_detail.id_barang')->where('id_produksi', $id_produksi)->groupBy('barang.id_barang')->get();

        $kredit_produksi = [];

        foreach ($produksi_detail as $detail) {
            $qr_barang = ProductionDetail::join('master_qr_code', 'master_qr_code.kode_batang_master_qr_code', 'produksi_detail.kode_batang_produksi_detail')->where('produksi_detail.id_produksi', $id_produksi)->where('produksi_detail.id_barang', $detail->id_barang)->get();
            $sum_jumlah_master_qr_code = 0;

            foreach ($qr_barang as $data) {
                $sum_jumlah_master_qr_code += $data->jumlah_master_qr_code;
            }

            if ($sum_jumlah_master_qr_code > 0) {
                foreach ($qr_barang as $data) {
                    $checkQR = MasterQrCode::where('id_barang', $data->id_barang)->where('kode_batang_master_qr_code', $data->kode_batang_produksi_detail)->first();

                    if (($checkQR->listrik2_master_qr_code != null && $checkQR->listrik2_master_qr_code != 0) || ($checkQR->pegawai2_master_qr_code != null && $checkQR->pegawai2_master_qr_code != 0)) {
                        MasterQrCode::where('id_barang', $data->id_barang)
                            ->where('kode_batang_master_qr_code', $data->kode_batang_produksi_detail)
                            ->update([
                                'listrik_master_qr_code' => DB::raw('listrik2_master_qr_code'),
                                'pegawai_master_qr_code' => DB::raw('pegawai2_master_qr_code'),
                            ]);
                    }

                    MasterQrCode::where('id_barang', $data->id_barang)
                        ->where('kode_batang_master_qr_code', $data->kode_batang_produksi_detail)
                        ->update([
                            'listrik2_master_qr_code' => DB::raw('listrik_master_qr_code'),
                            'pegawai2_master_qr_code' => DB::raw('pegawai_master_qr_code'),
                            'listrik_master_qr_code' => round($listrik / $sum_jumlah_master_qr_code, 2),
                            'pegawai_master_qr_code' => round($tenaga / $sum_jumlah_master_qr_code, 2),
                        ]);
                }
            }

            $qr_barang_updated = ProductionDetail::join('master_qr_code', 'master_qr_code.kode_batang_master_qr_code', 'produksi_detail.kode_batang_produksi_detail')->where('produksi_detail.id_produksi', $id_produksi)->where('produksi_detail.id_barang', $detail->id_barang)
                ->selectRaw('ROUND(
                                (jumlah_master_qr_code * produksi_master_qr_code) +
                                (jumlah_master_qr_code * listrik_master_qr_code) +
                                (jumlah_master_qr_code * pegawai_master_qr_code),
                            2) as jumlah, kode_batang_master_qr_code')
                ->groupBy('kode_batang_master_qr_code')
                ->get();

            $sum_kredit_detail = 0;

            foreach ($qr_barang_updated as $data_qr) {
                $sum_kredit_detail += $data_qr->jumlah;
            }

            array_push($kredit_produksi, [
                'id_barang' => $detail->id_barang,
                'value' => $sum_kredit_detail,
                'id_akun' => $detail->id_akun,
            ]);
        }

        $data = [
            'biaya' => [
                'tenaga' => $tenaga,
                'listrik' => $listrik,
            ],
            'kredit_produksi' => $kredit_produksi,
        ];

        return $data;
    }

    public function productionSupplies($production_id, $cabang_id)
    {
        // cari data produksi input
        $data_production_supplies = DB::table("produksi_detail")
            ->join('produksi', 'produksi.id_produksi', 'produksi_detail.id_produksi')
            ->join('barang', 'barang.id_barang', 'produksi_detail.id_barang')
            ->join('master_qr_code', 'master_qr_code.kode_batang_master_qr_code', 'produksi_detail.kode_batang_lama_produksi_detail')
            ->leftJoin('satuan_barang', 'satuan_barang.id_satuan_barang', 'produksi_detail.id_satuan_barang')
            ->selectRaw('produksi_detail.id_barang,
                                    barang.nama_barang,
                                    produksi.nama_produksi,
                                    IFNULL(satuan_barang.nama_satuan_barang, "") as nama_satuan,
                                    ROUND(IFNULL(SUM(produksi_detail.kredit_produksi_detail), 0), 2) as kredit_produksi,
                                    ROUND(IFNULL(SUM(produksi_detail.kredit_produksi_detail * master_qr_code.beli_master_qr_code), 0), 2) as beli,
                                    ROUND(IFNULL(SUM(produksi_detail.kredit_produksi_detail * master_qr_code.biaya_beli_master_qr_code), 0), 2) as biaya,
                                    ROUND(IFNULL(SUM(produksi_detail.kredit_produksi_detail * master_qr_code.produksi_master_qr_code), 0), 2) as produksi,
                                    ROUND(IFNULL(SUM(produksi_detail.kredit_produksi_detail * master_qr_code.listrik_master_qr_code), 0), 2) as listrik,
                                    ROUND(IFNULL(SUM(produksi_detail.kredit_produksi_detail * master_qr_code.pegawai_master_qr_code), 0), 2) as pegawai,
                                    barang.id_akun as id_akun, barang.id_akun2')
            ->where('produksi_detail.id_produksi', $production_id)
            ->groupBy('produksi_detail.id_barang')
            ->orderBy('produksi_detail.id_barang', 'ASC')
            ->get();

        if (count($data_production_supplies) < 1) {
            return false;
        }

        // init array kosong untuk memasukkan data persediaan dan total persediaan
        $data_supplies = [];
        $total_supplies = 0;

        // input persediaan dan jumlahkan total persediaan
        foreach ($data_production_supplies as $production) {
            $total = ($production->beli + $production->biaya + $production->produksi + $production->listrik + $production->pegawai);
            $total_supplies += $total;

            array_push($data_supplies, [
                'akun' => $cabang_id == 2 ? $production->id_akun2 : $production->id_akun,
                'notes' => $production->nama_barang . ' - ' . $production->kredit_produksi . ' ' . $production->nama_satuan,
                'debet' => 0,
                'kredit' => round($total, 2),
            ]);
        }

        // data yang direturn
        $data = [
            'data_supplies' => $data_supplies,
            'total_supplies' => $total_supplies,
        ];
        return $data;
    }

    public function productionCost($production_id, $data_biaya)
    {
        $hasil_produksi = DB::table('produksi')->where('nomor_referensi_produksi', $production_id)->first();
        if (empty($hasil_produksi)) {
            return false;
        }

        $id_hasil_produksi = $hasil_produksi->id_produksi;

        // cari beban produksi dari produksi yang diinput
        $data_production_cost = DB::table("beban_produksi")
            ->join('produksi', 'produksi.id_produksi', 'beban_produksi.id_produksi')
            ->join('master_mesin', 'master_mesin.id_mesin', 'produksi.id_mesin')
            ->where('beban_produksi.id_produksi', $id_hasil_produksi)
            ->select('beban_produksi.id_produksi', 'beban_produksi.kwh_beban_produksi', 'beban_produksi.tenaga_kerja_beban_produksi', 'beban_produksi.listrik_beban_produksi', 'master_mesin.daya')
            ->first();

        if (empty($data_production_cost)) {
            return false;
        }

        $tenaga = ($data_production_cost->tenaga_kerja_beban_produksi * $data_production_cost->listrik_beban_produksi) * $data_biaya['gaji'];
        $listrik = $data_production_cost->kwh_beban_produksi * $data_biaya['listrik'];

        // init beban listrik dan pegawai
        $beban_listrik = round($data_production_cost->kwh_beban_produksi, 2);
        $beban_pegawai = round(($data_production_cost->tenaga_kerja_beban_produksi * $data_production_cost->listrik_beban_produksi), 2);
        $jumlah_pegawai = round(($data_production_cost->tenaga_kerja_beban_produksi), 2);
        $listrik_pegawai = round($data_production_cost->listrik_beban_produksi, 2);
        $daya_mesin = round($data_production_cost->daya, 2);

        // data return biaya listrik dan pegawai
        $data = [
            'kwh_listrik' => $beban_listrik,
            'daya_mesin' => $daya_mesin,
            'tenaga_kerja' => $listrik_pegawai,
            'jumlah_pegawai' => $jumlah_pegawai,
            'nominal_listrik' => $listrik,
            'nominal_gaji' => $tenaga
        ];

        return $data;
    }

    public function productionResults($production_id, $total_supplies, $id_cabang)
    {
        // cari hasil produksi dari input produksi yang berlangsung
        $data_production_results = DB::table("produksi_detail")
            ->join('produksi', 'produksi.id_produksi', 'produksi_detail.id_produksi')
            ->join('barang', 'barang.id_barang', 'produksi_detail.id_barang')
            ->join('master_qr_code', 'master_qr_code.kode_batang_master_qr_code', 'produksi_detail.kode_batang_produksi_detail')
            ->select('produksi_detail.*', 'produksi.nama_produksi', 'produksi.tanggal_produksi')
            ->where('produksi.nomor_referensi_produksi', $production_id)
            ->orderBy('produksi_detail.id_barang', 'ASC')
            ->get();

        // hitung total kredit hasil produksi
        $total_kredit_produksi = 0;

        foreach ($data_production_results as $production) {
            $total_kredit_produksi += $production->debit_produksi_detail;
        }

        // hitung harga produksi, listrik dan pegawai
        $harga_produksi = round(($total_supplies / $total_kredit_produksi), 2);

        // update beban biaya dari tiap produksi detail
        foreach ($data_production_results as $production) {
            DB::table("master_qr_code")
                ->where('id_barang', $production->id_barang)
                ->where('kode_batang_master_qr_code', $production->kode_batang_produksi_detail)
                ->update([
                    'produksi_master_qr_code' => $harga_produksi,
                ]);
        }

        // cari total hasil produksi detail
        $data_production_results_groupby_barang = DB::table("produksi_detail")
            ->join('produksi', 'produksi.id_produksi', 'produksi_detail.id_produksi')
            ->join('barang', 'barang.id_barang', 'produksi_detail.id_barang')
            ->join('master_qr_code', 'master_qr_code.kode_batang_master_qr_code', 'produksi_detail.kode_batang_produksi_detail')
            ->leftJoin('satuan_barang', 'satuan_barang.id_satuan_barang', 'produksi_detail.id_satuan_barang')
            ->selectRaw('produksi_detail.id_barang,
                                                produksi.nama_produksi,
                                                barang.nama_barang,
                                                IFNULL(satuan_barang.nama_satuan_barang, "") as nama_satuan,
                                                ROUND(SUM(debit_produksi_detail),2) as debit_produksi,
                                                CASE WHEN ' . $id_cabang . ' = 1 THEN barang.id_akun ELSE barang.id_akun2 END as id_akun,
                                                ROUND(SUM(ROUND(master_qr_code.listrik_master_qr_code * produksi_detail.debit_produksi_detail, 2) + ROUND(master_qr_code.pegawai_master_qr_code * produksi_detail.debit_produksi_detail, 2) + ROUND(master_qr_code.produksi_master_qr_code * produksi_detail.debit_produksi_detail, 2)), 2) as total')
            ->where('produksi.nomor_referensi_produksi', $production_id)
            ->groupBy('produksi_detail.id_barang')
            ->orderBy('produksi_detail.id_barang', 'ASC')
            ->get();

        $data_results = [];

        foreach ($data_production_results_groupby_barang as $production) {
            array_push($data_results, [
                'akun' => $production->id_akun,
                'notes' => $production->nama_barang . ' - ' . $production->debit_produksi . ' ' . $production->nama_satuan,
                'id_barang' => $production->id_barang,
                'debet' => round($production->total, 2),
                'kredit' => 0,
            ]);
        }

        // data yang direturn
        $data = [
            'data_results' => $data_results,
            'nama_hasil_produksi' => $data_production_results[0]->nama_produksi,
            'tanggal_hasil_produksi' => $data_production_results[0]->tanggal_produksi,
        ];

        return $data;
    }

    public function journalHpp($id_produksi, $month, $year, $biaya_produksi)
    {
        DB::beginTransaction();
        try{
            $data_produksi = DB::table('produksi')->where('id_produksi', $id_produksi)->first();
            $id_produksi = $data_produksi->id_produksi;
            $nama_produksi = $data_produksi->nama_produksi;
            $cabang = $data_produksi->id_cabang;

            if (empty($data_produksi)) {
                DB::rollBack();
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $cabang)->delete();
                }
                Log::error("Error can not find Produksi " . $nama_produksi . " while closing production");
                return response()->json([
                    "result" => false,
                    "message" => "Error can not find Produksi " . $nama_produksi . " while closing production",
                ]);
            }

            // tahap 1
            $data_production_supplies = $this->productionSupplies($id_produksi, $cabang);

            if ($data_production_supplies == false) {
                return;
            }

            // tahap 2 dan 3
            $data_production_cost = $this->productionCost($id_produksi, $biaya_produksi);

            if ($data_production_cost == false) {
                DB::rollBack();

                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $cabang)->delete();
                }

                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when rejournal hpp store Jurnal Hpp data. Data Beban Produksi " . $nama_produksi . " not found ",
                ], 400);
            }

            $total_supplies = $data_production_supplies['total_supplies'];
            $kwh_listrik = $data_production_cost['kwh_listrik'];
            $daya_mesin = $data_production_cost['daya_mesin'];
            $tenaga_kerja = $data_production_cost['tenaga_kerja'];
            $jumlah_pegawai = $data_production_cost['jumlah_pegawai'];
            $biaya_listrik = $biaya_produksi['listrik'];
            $biaya_operator = $biaya_produksi['gaji'];
            $nominal_listrik = round($data_production_cost['nominal_listrik'], 2);
            $nominal_gaji =  round($data_production_cost['nominal_gaji'], 2);

            // tahap 4
            $data_production_results = $this->productionResults($id_produksi, $total_supplies, $cabang);

            // init data jurnal
            $data_production = DB::table('produksi')->where('id_produksi', $id_produksi)->first();

            $id_transaksi = $data_production->nama_produksi;
            $data_pemakaian = $data_production_supplies['data_supplies'];
            $data_hasil = $data_production_results['data_results'];
            $id_transaksi_hasil_produksi = $data_production_results['nama_hasil_produksi'];
            $tanggal_hasil_produksi = $data_production_results['tanggal_hasil_produksi'];
            $user_data = Auth::guard('api')->user();

            if (count($data_hasil) < 1) {
                DB::rollBack();
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $cabang)->delete();
                }

                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when rejournal hpp store Jurnal Hpp data. Data Hasil Produksi empty",
                ], 400);
            }

            $data = [
                'id_transaksi' => $id_transaksi,
                'cabang' => $cabang,
                'data_pemakaian' => $data_pemakaian,
                'biaya_listrik' => $biaya_listrik,
                'biaya_operator' => $biaya_operator,
                'kwh_listrik' => $kwh_listrik,
                'daya_mesin' => $daya_mesin,
                'tenaga_kerja' => $tenaga_kerja,
                'jumlah_pegawai' => $jumlah_pegawai,
                'nominal_listrik' => $nominal_listrik,
                'nominal_gaji' => $nominal_gaji,
                'data_hasil' => $data_hasil,
                'user_data' => $user_data,
                'void' => 0,
                'note' => $id_transaksi . ' ==> ' . $id_transaksi_hasil_produksi,
                'tanggal_hasil_produksi' => $tanggal_hasil_produksi,
            ];

            // tahap 5
            $store_data = $this->storeHppJournal($data);

            if ($store_data == false)  {
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $cabang)->delete();
                }
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when rejournal hpp store Jurnal Hpp data",
                ], 400);
            }
        }catch (\Exception $e) {
            DB::rollback();
            $message = "Error when storing HPP Journal";
            Log::error($message);
            Log::error($e);
            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $cabang)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $cabang)->delete();
            }
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => $message,
            ], 400);
        }
    }

    public function storeHppJournal($data)
    {
        try {
            // Init Data
            $id_transaksi = $data['id_transaksi']; // Diisi dengan ID/Nomor transaksi produksi
            $pemakaian = $data['data_pemakaian']; // Diisi dengan data pemakaian
            $hasil_produksi = $data['data_hasil']; // Diisi dengan data hasil produksi
            $biaya_listrik = $data['biaya_listrik']; // Diisi dengan data biaya listrik
            $biaya_operator = $data['biaya_operator']; // Diisi dengan data biaya operator
            $kwh_listrik = $data['kwh_listrik']; // Diisi dengan data biaya listrik
            $daya_mesin = $data['daya_mesin']; // Diisi dengan data daya mesin
            $tenaga_kerja = $data['tenaga_kerja']; // Diisi dengan data biaya operator
            $jumlah_pegawai = $data['jumlah_pegawai']; // Diisi dengan data biaya operator
            $nominal_listrik = $data['nominal_listrik']; // Diisi dengan data nominal listrik rata rata
            $nominal_gaji = $data['nominal_gaji']; // Diisi dengan data nominal gaji rata rata
            $journalDate = date('Y-m-d', strtotime($data['tanggal_hasil_produksi']));
            $journalType = "ME";
            $cabangID = $data['cabang'];
            $void = $data['void'];
            $noteHeader = $data['note'];
            $userData = $data['user_data'];
            $userRecord = $userData->id_pengguna;
            $userModified = $userData->id_pengguna;
            $dateRecord = date('Y-m-d H:i:s');

            // Get akun biaya listrik, biaya operator, pembulatan
            // $cabang = Cabang::find(1); // Diganti sesuai auth atau user session
            $get_akun_biaya_listrik = Setting::where("id_cabang", $cabangID)->where("code", "Biaya Listrik")->first();
            $get_akun_biaya_operator = Setting::where("id_cabang", $cabangID)->where("code", "Biaya Operator")->first();
            $get_akun_pembulatan = Setting::where("id_cabang", $cabangID)->where("code", "Pembulatan")->first();

            $jurnal_header = JurnalHeader::where("id_transaksi", $id_transaksi)->first();

            if (!empty($jurnal_header) && $void == 1) {
                $jurnal_header->void = $void;
                $jurnal_header->user_void = $userRecord;
                $jurnal_header->dt_void = date('Y-m-d h:i:s');

                if (!$jurnal_header->save()) {
                    DB::rollback();
                    Log::error("Error when update journal header on storeHppJournal");
                    return false;
                }
            } else {
                // Posting jurnal
                // Header
                $header = ($jurnal_header) ? $jurnal_header : new JurnalHeader;
                $header->id_cabang = $cabangID;
                $header->jenis_jurnal = $journalType;
                $header->id_transaksi = $id_transaksi;
                $header->catatan = $noteHeader;
                $header->void = 0;
                $header->tanggal_jurnal = $journalDate;
                $header->user_modified = $userModified;
                if (empty($jurnal_header)) {
                    $header->user_created = $userRecord;
                    $header->dt_created = $dateRecord;
                }
                $header->dt_modified = $dateRecord;
                $header->kode_jurnal = $this->generateJournalCode($cabangID, $journalType);
                if (!$header->save()) {
                    DB::rollback();
                    Log::error("Error when storing journal header on storeHppJournal");
                    return false;
                }

                if (!empty($jurnal_header)) {
                    JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->delete();
                }

                // Detail
                $index = 1;
                $total_debet = 0;
                $total_credit = 0;
                foreach ($pemakaian as $key => $val) {
                    //Store Detail
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $index;
                    $detail->id_akun = $val['akun'];
                    $detail->keterangan = "PBH - " . $val['notes'];
                    $detail->id_transaksi = null;
                    $detail->debet = floatval($val['debet']);
                    $detail->credit = floatval($val['kredit']);
                    $detail->user_created = $userRecord;
                    $detail->user_modified = $userModified;
                    $detail->dt_created = $dateRecord;
                    $detail->dt_modified = $dateRecord;
                    // dd(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        Log::error("Error when storing journal detail on storeHppJournal");
                        return false;
                    }
                    $total_debet += $detail->debet;
                    $total_credit += $detail->credit;
                    $index++;
                }

                // Detail Biaya Listrik
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $index;
                $detail->id_akun = $get_akun_biaya_listrik->value2;
                $detail->keterangan = "Biaya Listrik - " . $daya_mesin . ' Watt - ' . $kwh_listrik . ' kWh - WPH ' . round($biaya_listrik, 2);
                $detail->id_transaksi = "Biaya Listrik";
                $detail->debet = 0;
                $detail->credit = floatval($nominal_listrik);
                $detail->user_created = $userRecord;
                $detail->user_modified = $userModified;
                $detail->dt_created = $dateRecord;
                $detail->dt_modified = $dateRecord;

                if (!$detail->save()) {
                    DB::rollback();
                    Log::error("Error when storing journal detail on storeHppJournal");
                    return false;
                }
                $total_debet += $detail->debet;
                $total_credit += $detail->credit;
                $index++;

                // Detail Biaya Operator
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $index;
                $detail->id_akun = $get_akun_biaya_operator->value2;
                $detail->keterangan = "Biaya Operator Produksi - " . $jumlah_pegawai . ' Orang - ' . $tenaga_kerja . ' Menit - GPM ' . round($biaya_operator, 2);
                $detail->id_transaksi = "Biaya Operator";
                $detail->debet = 0;
                $detail->credit = floatval($nominal_gaji);
                $detail->user_created = $userRecord;
                $detail->user_modified = $userModified;
                $detail->dt_created = $dateRecord;
                $detail->dt_modified = $dateRecord;

                if (!$detail->save()) {
                    DB::rollback();
                    Log::error("Error when storing journal detail on storeHppJournal");
                    return false;
                }
                $total_debet += $detail->debet;
                $total_credit += $detail->credit;
                $index++;

                foreach ($hasil_produksi as $key => $val) {
                    //Store Detail
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $index;
                    $detail->id_akun = $val['akun'];
                    $detail->keterangan = "HP - " . $val['notes'];
                    $detail->id_transaksi = $val['id_barang'];
                    $detail->debet = floatval($val['debet']);
                    $detail->credit = floatval($val['kredit']);
                    $detail->user_created = $userRecord;
                    $detail->user_modified = $userModified;
                    $detail->dt_created = $dateRecord;
                    $detail->dt_modified = $dateRecord;
                    // dd(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        Log::error("Error when storing journal detail on storeHppJournal");
                        return false;
                    }
                    $total_debet += round($detail->debet, 2);
                    $total_credit += round($detail->credit, 2);
                    $index++;
                }

                // pembulatan
                if (round($total_debet, 2) != round($total_credit, 2)) {
                    $selisih = round($total_credit - $total_debet, 2);
                    // Detail Biaya Listrik
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $index;
                    $detail->id_akun = $get_akun_pembulatan->value2;
                    $detail->keterangan = "Pembulatan Produksi";
                    $detail->id_transaksi = "Pembulatan";
                    if ($selisih > 0) {
                        $detail->debet = floatval($selisih);
                        $detail->credit = 0;
                    } else {
                        $detail->debet = 0;
                        $detail->credit = floatval(abs($selisih));
                    }
                    $detail->user_created = $userRecord;
                    $detail->user_modified = $userModified;
                    $detail->dt_created = $dateRecord;
                    $detail->dt_modified = $dateRecord;

                    if (!$detail->save()) {
                        DB::rollback();
                        Log::error("Error when storing journal detail on storeHppJournal");
                        return false;
                    }
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            $message = "Error when storing HPP Journal";
            Log::error($message);
            Log::error($e);
            return false;
        }
    }

    public function production(Request $request)
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

            $biaya_produksi = $this->getProductionCost($end_date, $id_cabang);

            $data_produksi = Production::whereMonth("tanggal_produksi", $month)->whereYear('tanggal_produksi', $year)->where('id_jenis_transaksi', 17)->where('id_cabang', $id_cabang)->get();

            DB::beginTransaction();
            foreach ($data_produksi as $produksi) {
                $data_hpp = $this->updateProductionCredit($produksi->id_produksi, $biaya_produksi);
                $data_hpp_biaya = $data_hpp['biaya'];
                $data_hpp_kredit_hasil = $data_hpp['kredit_produksi'];

                $sumber_produksi = Production::where('id_produksi', $produksi->nomor_referensi_produksi)->first();

                $jurnal_header = JurnalHeader::where('id_transaksi', $sumber_produksi->nama_produksi)->where('jenis_jurnal', 'ME')->where('void', 0)->first();
                // $jurnal_biaya_listrik = JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->where('id_transaksi', 'Biaya Listrik')->first();
                $jurnal_biaya_listrik = JurnalDetail::select("jurnal_detail.keterangan", "jurnal_detail.id_jurnal")
                    ->where('jurnal_header.id_transaksi', $sumber_produksi->nama_produksi)
                    ->where('jurnal_header.jenis_jurnal', 'ME')
                    ->where('jurnal_header.void', 0)
                    ->where('jurnal_detail.id_transaksi', 'Biaya Listrik')
                    ->join('jurnal_header', 'jurnal_header.id_jurnal', 'jurnal_detail.id_jurnal')
                    ->first();
                if ($jurnal_biaya_listrik) {
                    $keterangan_listrik = substr($jurnal_biaya_listrik->keterangan, 0, strpos($jurnal_biaya_listrik->keterangan, "WPH"));
                    // update jurnal detail biaya
                    $update_jurnal_listrik = JurnalDetail::where('id_jurnal', $jurnal_biaya_listrik->id_jurnal)->where('id_transaksi', 'Biaya Listrik')->update([
                        'credit' => $data_hpp_biaya['listrik'],
                        'keterangan' => $keterangan_listrik . 'WPH ' . round($biaya_produksi['listrik'], 2),
                    ]);
                    if ($update_jurnal_listrik == 0) {
                        DB::rollback();
                        // Revert post closing
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        Log::error("Error when updating journal detail on update jurnal biaya listrik hpp produksi");
                        return response()->json([
                            "result" => false,
                            "message" => "Error when updating journal detail on update jurnal biaya listrik hpp produksi. Kode Produksi " . $sumber_produksi->nama_produksi,
                        ]);
                    }
                } else {
                    DB::rollback();
                    // Revert post closing
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Error when get journal detail on update jurnal biaya listrik hpp produksi. Kode Produksi " . $sumber_produksi->nama_produksi,
                    ]);
                }

                // $jurnal_biaya_operator = JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->where('id_transaksi', 'Biaya Operator')->first();
                $jurnal_biaya_operator = JurnalDetail::select("jurnal_detail.keterangan", "jurnal_detail.id_jurnal")
                    ->where('jurnal_header.id_transaksi', $sumber_produksi->nama_produksi)
                    ->where('jurnal_header.jenis_jurnal', 'ME')
                    ->where('jurnal_header.void', 0)
                    ->where('jurnal_detail.id_transaksi', 'Biaya Operator')
                    ->join('jurnal_header', 'jurnal_header.id_jurnal', 'jurnal_detail.id_jurnal')
                    ->first();
                if ($jurnal_biaya_operator) {
                    $keterangan_operator = substr($jurnal_biaya_operator->keterangan, 0, strpos($jurnal_biaya_operator->keterangan, "GPM"));
                    $update_jurnal_operator = JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->where('id_transaksi', 'Biaya Operator')->update([
                        'credit' => $data_hpp_biaya['tenaga'],
                        'keterangan' => $keterangan_operator . 'GPM ' . round($biaya_produksi['gaji'], 2),
                    ]);
                    if ($update_jurnal_operator == 0) {
                        DB::rollback();
                        // Revert post closing
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        Log::error("Error when updating journal detail on update jurnal biaya operator hpp produksi");
                        return response()->json([
                            "result" => false,
                            "message" => "Error when updating journal detail on update jurnal biaya operator hpp produksi. Kode Produksi " . $sumber_produksi->nama_produksi,
                        ]);
                    }
                } else {
                    DB::rollback();
                    // Revert post closing
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Error when get journal detail on update jurnal biaya operator hpp produksi. Kode Produksi " . $sumber_produksi->nama_produksi,
                    ]);
                }

                foreach ($data_hpp_kredit_hasil as $kredit_hasil) {
                    $update_jurnal_hasil = JurnalDetail::select("jurnal_detail.*")
                        ->where('jurnal_header.id_transaksi', $sumber_produksi->nama_produksi)
                        ->where('jurnal_header.jenis_jurnal', 'ME')
                        ->where('jurnal_header.void', 0)
                        ->where('jurnal_detail.id_transaksi', $kredit_hasil['id_barang'])
                        ->join('jurnal_header', 'jurnal_header.id_jurnal', 'jurnal_detail.id_jurnal')
                        ->update([
                            'debet' => $kredit_hasil['value'],
                        ]);
                    if ($update_jurnal_hasil == 0) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        Log::error("Error when updating journal detail on update jurnal hasil produksi " . $sumber_produksi->nama_produksi . " barang " . $kredit_hasil['id_barang'] . " hpp produksi");
                        return response()->json([
                            "result" => false,
                            "message" => "Error when updating journal detail on update jurnal hasil produksi " . $sumber_produksi->nama_produksi . " barang " . $kredit_hasil['id_barang'] . " hpp produksi",
                        ]);
                    }
                }

                $jurnal_detail = JurnalDetail::select("jurnal_detail.*")
                    ->where('jurnal_header.id_transaksi', $sumber_produksi->nama_produksi)
                    ->where('jurnal_header.jenis_jurnal', 'ME')
                    ->where('jurnal_header.void', 0)
                    ->join('jurnal_header', 'jurnal_header.id_jurnal', 'jurnal_detail.id_jurnal')
                    ->orderBy('index', 'ASC')
                    ->get();
                $jurnal_pembulatan = JurnalDetail::select("jurnal_detail.*")
                    ->where('jurnal_header.id_transaksi', $sumber_produksi->nama_produksi)
                    ->where('jurnal_header.jenis_jurnal', 'ME')
                    ->where('jurnal_header.void', 0)
                    ->where('jurnal_detail.id_transaksi', 'Pembulatan')
                    ->join('jurnal_header', 'jurnal_header.id_jurnal', 'jurnal_detail.id_jurnal')
                    ->first();

                $sum_credit_jurnal = 0;
                $sum_debet_jurnal = 0;
                $index = 0;
                foreach ($jurnal_detail as $detail) {
                    if ($detail->id_transaksi != 'Pembulatan') {
                        $sum_credit_jurnal += round($detail->credit, 2);
                        $sum_debet_jurnal += round($detail->debet, 2);
                        $index = $detail->index;
                    }
                    $index++;
                }

                if (round($sum_credit_jurnal, 2) != round($sum_debet_jurnal, 2)) {
                    $selisih = round($sum_credit_jurnal - $sum_debet_jurnal, 2);

                    if (empty($jurnal_pembulatan)) {
                        $get_akun_pembulatan = Setting::where("id_cabang", $id_cabang)->where("code", "Pembulatan")->first();

                        // Detail Biaya Listrik
                        $detail = new JurnalDetail();
                        $detail->id_jurnal = $jurnal_header->id_jurnal;
                        $detail->index = $index++;
                        $detail->id_akun = $get_akun_pembulatan->value2;
                        $detail->keterangan = "Pembulatan Produksi " . $sumber_produksi->nama_produksi;
                        $detail->id_transaksi = "Pembulatan";
                        if ($selisih > 0) {
                            $detail->debet = floatval($selisih);
                            $detail->credit = 0;
                        } else {
                            $detail->debet = 0;
                            $detail->credit = floatval(abs($selisih));
                        }
                        $detail->dt_created = $end_date;
                        $detail->dt_modified = $end_date;

                        if (!$detail->save()) {
                            DB::rollback();
                            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                            }
                            Log::error("Error when storing journal detail on store jurnal pembulatan hpp produksi");
                            return response()->json([
                                "result" => false,
                                "message" => "Error when storing journal detail on store jurnal pembulatan hpp produksi. Kode Produksi " . $sumber_produksi->nama_produksi,
                            ]);
                        }
                    } else {
                        if ($selisih > 0) {
                            $update_jurnal_pembulatan = JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->where('id_transaksi', 'Pembulatan')->update([
                                'debet' => floatval($selisih),
                                'credit' => 0,
                            ]);
                        } else {
                            $update_jurnal_pembulatan = JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->where('id_transaksi', 'Pembulatan')->update([
                                'debet' => 0,
                                'credit' => floatval(abs($selisih)),
                            ]);
                        }

                        if ($update_jurnal_pembulatan == 0) {
                            DB::rollback();
                            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                            }
                            Log::error("Error when update journal detail on update jurnal pembulatan hpp produksi");
                            return response()->json([
                                "result" => false,
                                "message" => "Error when update journal detail on update jurnal pembulatan hpp produksi " . $sumber_produksi->nama_produksi,
                            ]);
                        }
                    }
                }

                foreach ($data_produksi as $produksi_rejournal) {
                    if($produksi_rejournal->id_produksi != $produksi->id_produksi){
                        $this->journalHpp($sumber_produksi->id_produksi, $month, $year, $biaya_produksi);
                    }
                }
            }

            $jurnal_header = JurnalHeader::where('id_transaksi', "Selisih HPP Produksi " . date('Y m', strtotime($end_date)))->first();
            if (!empty($jurnal_header)) {
                JurnalDetail::where('id_jurnal', $jurnal_header->id_jurnal)->delete();
                JurnalHeader::where('id_jurnal', $jurnal_header->id_jurnal)->delete();
            }

            $get_akun_biaya_listrik = Setting::where("id_cabang", $id_cabang)->where("code", "Biaya Listrik")->first();
            $get_akun_biaya_operator = Setting::where("id_cabang", $id_cabang)->where("code", "Biaya Operator")->first();

            $sum_biaya_listrik_manual = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
                ->whereMonth('tanggal_jurnal', $month)
                ->whereYear('tanggal_jurnal', $year)
                ->where('id_cabang', $id_cabang)
                ->where('void', 0)
                ->whereNull('jurnal_header.id_transaksi')
                ->where('jurnal_detail.id_akun', $get_akun_biaya_listrik->value2)
                ->selectRaw('ROUND(SUM(debet-credit), 2) as value')
                ->first();

            $sum_biaya_operator_manual = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
                ->whereMonth('tanggal_jurnal', $month)
                ->whereYear('tanggal_jurnal', $year)
                ->where('id_cabang', $id_cabang)
                ->where('void', 0)
                ->whereNull('jurnal_header.id_transaksi')
                ->where('jurnal_detail.id_akun', $get_akun_biaya_operator->value2)
                ->selectRaw('ROUND(SUM(debet-credit), 2) as value')
                ->first();

            $sum_biaya_listrik_otomatis = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
                ->whereMonth('tanggal_jurnal', $month)
                ->whereYear('tanggal_jurnal', $year)
                ->where('id_cabang', $id_cabang)
                ->where('void', 0)
                ->whereNotNull('jurnal_header.id_transaksi')
                ->whereRaw('jurnal_header.id_transaksi NOT LIKE "%Closing%"')
                ->whereRaw('jurnal_header.id_transaksi NOT LIKE "%Selisih HPP Produksi%"')
                ->where('jurnal_detail.id_akun', $get_akun_biaya_listrik->value2)
                ->selectRaw('ROUND(SUM(credit-debet), 2) as value')
                ->first();

            $sum_biaya_operator_otomatis = JurnalHeader::join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
                ->whereMonth('tanggal_jurnal', $month)
                ->whereYear('tanggal_jurnal', $year)
                ->where('id_cabang', $id_cabang)
                ->where('void', 0)
                ->whereNotNull('jurnal_header.id_transaksi')
                ->whereRaw('jurnal_header.id_transaksi NOT LIKE "%Closing%"')
                ->whereRaw('jurnal_header.id_transaksi NOT LIKE "%Selisih HPP Produksi%"')
                ->where('jurnal_detail.id_akun', $get_akun_biaya_operator->value2)
                ->selectRaw('ROUND(SUM(credit-debet), 2) as value')
                ->first();

            Log::debug('testing selisih');
            Log::debug(json_encode($sum_biaya_listrik_manual));
            Log::debug(json_encode($sum_biaya_operator_manual));
            Log::debug(json_encode($sum_biaya_listrik_otomatis));
            Log::debug(json_encode($sum_biaya_operator_otomatis));
            Log::debug('-----------------------------------');

            $selisih_listrik = round($sum_biaya_listrik_otomatis->value, 2) - round($sum_biaya_listrik_manual->value, 2);
            $selisih_tenaga = round($sum_biaya_operator_otomatis->value, 2) - round($sum_biaya_operator_manual->value, 2);

            Log::debug('selisih----');
            Log::debug('listrik : ' . $selisih_listrik);
            Log::debug('tenaga : ' . $selisih_tenaga);

            if ($selisih_listrik != 0 || $selisih_tenaga != 0) {
                Log::debug('mulai buat header selisih');
                Log::debug('listrik : ' . $selisih_listrik);
                Log::debug('tenaga : ' . $selisih_tenaga);
                Log::debug('-----');
                // Create journal memorial
                // Store Header
                $header = new JurnalHeader();
                $header->id_cabang = $id_cabang;
                $header->jenis_jurnal = $journal_type;
                $header->id_transaksi = "Selisih HPP Produksi " . date('Y m', strtotime($end_date));
                $header->void = 0;
                $header->tanggal_jurnal = $end_date;
                $header->user_created = null;
                $header->user_modified = null;
                $header->dt_created = $end_date;
                $header->dt_modified = $end_date;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
                // dd($header);
                if (!$header->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table header",
                    ]);
                }

                $sum_selisih_debet = 0;
                $sum_selisih_credit = 0;
                $index = 1;

                if ($selisih_listrik != 0) {
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $index;
                    $detail->id_akun = $get_akun_biaya_listrik->value2;
                    $detail->keterangan = "Selisih Produksi Biaya Listrik " . date('Y m', strtotime($end_date));
                    if ($selisih_listrik > 0) {
                        $detail->debet = floatval(round($selisih_listrik, 2));
                        $detail->credit = 0;
                    } else {
                        $detail->debet = 0;
                        $detail->credit = floatval(abs(round($selisih_listrik, 2)));
                    }
                    $detail->user_created = null;
                    $detail->user_modified = null;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // Log::info(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail",
                        ]);
                    }

                    $sum_selisih_debet += $detail->debet;
                    $sum_selisih_credit += $detail->credit;
                    $index++;
                }

                if ($selisih_tenaga != 0) {
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $index;
                    $detail->id_akun = $get_akun_biaya_operator->value2;
                    $detail->keterangan = "Selisih Produksi Biaya Pegawai " . date('Y m', strtotime($end_date));
                    if ($selisih_tenaga > 0) {
                        $detail->debet = floatval(round($selisih_tenaga, 2));
                        $detail->credit = 0;
                    } else {
                        $detail->debet = 0;
                        $detail->credit = floatval(abs(round($selisih_tenaga, 2)));
                    }
                    $detail->user_created = null;
                    $detail->user_modified = null;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // Log::info(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail",
                        ]);
                    }

                    $sum_selisih_debet += $detail->debet;
                    $sum_selisih_credit += $detail->credit;
                    $index++;
                }

                if ($sum_selisih_debet != $sum_selisih_credit) {
                    $get_akun_pembulatan = Setting::where("id_cabang", $id_cabang)->where("code", "Pembulatan")->first();

                    $selisih_pembulatan = $sum_selisih_credit - $sum_selisih_debet;
                    // Detail Biaya Listrik
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $index;
                    $detail->id_akun = $get_akun_pembulatan->value2;
                    $detail->keterangan = "Pembulatan Produksi " . date('Y m', strtotime($end_date));
                    if ($selisih_pembulatan > 0) {
                        $detail->debet = floatval($selisih_pembulatan);
                        $detail->credit = 0;
                    } else {
                        $detail->debet = 0;
                        $detail->credit = floatval(abs($selisih_pembulatan));
                    }
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;

                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        Log::error("Error when storing journal detail on table detail");
                        return response()->json([
                            "result" => false,
                            "message" => "Error when storing journal detail on table detail",
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully proceed closing journal Hpp Production",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            $month = $request->month;
            $year = $request->year;
            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
            }
            $message = "Error when closing journal Hpp Production";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
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
                    "result" => false,
                    "message" => "Jurnal Closing Transfer Cabang Gagal. Akun HPP Transfer Cabang tidak ditemukan",
                ]);
            }

            // Get data pindah barang
            $data_header = InventoryTransferHeader::where("id_cabang2", "<>", $id_cabang)->whereBetween("tanggal_pindah_barang", [$start_date, $end_date])->where("void", 0)->where("status_pindah_barang", 1)->whereIn('id_jenis_transaksi', [21, 22])->get();
            $details_out = [];
            $details_in = [];
            // Log::info("jumlah data header");
            // Log::info(count($data_header));
            DB::beginTransaction();
            foreach ($data_header as $key => $header) {
                // Log::info($header->kode_pindah_barang);
                $id_transaksi = $header->kode_pindah_barang;
                $transaction_date = $header->tanggal_pindah_barang;
                // Delete detail and header existing first
                JurnalDetail::where("id_transaksi", $id_transaksi)->where("keterangan", "HPP Transfer Cabang Keluar " . $id_transaksi)->delete();
                JurnalHeader::where("id_transaksi", "Closing " . $id_transaksi)->where("catatan", "Closing Transfer Barang Keluar")->delete();
                JurnalDetail::where("id_transaksi", $id_transaksi)->where("keterangan", "HPP Transfer Cabang Masuk " . $id_transaksi)->delete();
                JurnalHeader::where("id_transaksi", "Closing " . $id_transaksi)->where("catatan", "Closing Transfer Barang Masuk")->delete();
                if ($header->type == 0) {
                    // Get header out detail
                    $data_detail = InventoryTransferDetail::select("pindah_barang_detail.id_barang", "pindah_barang_detail.qr_code", "master_qr_code.beli_master_qr_code", "master_qr_code.biaya_beli_master_qr_code", "master_qr_code.jumlah_master_qr_code", "master_qr_code.produksi_master_qr_code", "master_qr_code.listrik_master_qr_code", "master_qr_code.pegawai_master_qr_code")->join("master_qr_code", "kode_batang_master_qr_code", "pindah_barang_detail.qr_code")->where("pindah_barang_detail.id_pindah_barang", $header->id_pindah_barang)->get();
                    foreach ($data_detail as $key => $detail) {
                        $qty = $detail->jumlah_master_qr_code;
                        $sum = ($qty * $detail->beli_master_qr_code) + ($qty * $detail->biaya_beli_master_qr_code) + ($qty * $detail->produksi_master_qr_code) + ($qty * $detail->listrik_master_qr_code) + ($qty * $detail->pegawai_master_qr_code);
                        $details_out[] = [
                            "qr_code" => $detail->qr_code,
                            "barang" => $detail->id_barang,
                            "qty" => $qty,
                            "sum" => $sum,
                        ];
                    }
                    // Log::info(json_encode($details_out));
                    // Grouping and sum the same barang
                    $grouped_out = array_reduce($details_out, function ($result, $out) {
                        $product = $out['barang'];
                        $sum = $out['sum'];
                        if (isset($result[$product])) {
                            $result[$product] += $sum;
                        } else {
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
                    $header->tanggal_jurnal = $transaction_date;
                    $header->user_created = null;
                    $header->user_modified = null;
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

                        if ($id_cabang == 1) {
                            $akun_persediaan = $barang->id_akun;
                        } else {
                            $format_akun = 'id_akun' . $id_cabang;
                            $akun_persediaan = $barang->$format_akun;
                        }

                        if($id_transaksi == 'TG/SCA/23/10/0009'){
                            \Log::debug("========== CHeck ========");
                            \Log::debug($id_cabang);
                            \Log::debug(json_encode($barang));
                        }

                        // Log::info(json_encode($barang->id_barang));
                        $detail = new JurnalDetail();
                        $detail->id_jurnal = $header->id_jurnal;
                        $detail->index = $i + 1;
                        $detail->id_akun = $akun_persediaan;
                        $detail->keterangan = "HPP Transfer Cabang Keluar " . $id_transaksi;
                        $detail->id_transaksi = $id_transaksi;
                        $detail->debet = 0;
                        $detail->credit = $out;
                        $detail->user_created = null;
                        $detail->user_modified = null;
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
                    $detail->keterangan = "HPP Transfer Cabang Keluar " . $id_transaksi;
                    $detail->id_transaksi = $id_transaksi;
                    $detail->debet = $sum_debet;
                    $detail->credit = 0;
                    $detail->user_created = null;
                    $detail->user_modified = null;
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
                } else {
                    // Get header in detail
                    $data_detail = InventoryTransferDetail::select("pindah_barang_detail.id_barang", "pindah_barang_detail.qr_code", "master_qr_code.beli_master_qr_code", "master_qr_code.biaya_beli_master_qr_code", "master_qr_code.jumlah_master_qr_code", "master_qr_code.produksi_master_qr_code", "master_qr_code.listrik_master_qr_code", "master_qr_code.pegawai_master_qr_code")->join("master_qr_code", "kode_batang_master_qr_code", "pindah_barang_detail.qr_code")->where("pindah_barang_detail.id_pindah_barang", $header->id_pindah_barang)->get();
                    foreach ($data_detail as $key => $detail) {
                        $qty = $detail->jumlah_master_qr_code;
                        $sum = ($qty * $detail->beli_master_qr_code) + ($qty * $detail->biaya_beli_master_qr_code) + ($qty * $detail->produksi_master_qr_code) + ($qty * $detail->listrik_master_qr_code) + ($qty * $detail->pegawai_master_qr_code);
                        $details_in[] = [
                            "qr_code" => $detail->qr_code,
                            "barang" => $detail->id_barang,
                            "qty" => $qty,
                            "sum" => $sum,
                        ];
                    }
                    // Log::info(json_encode($details_out));
                    // Grouping and sum the same barang
                    $grouped_in = array_reduce($details_in, function ($result, $in) {
                        $product = $in['barang'];
                        $sum = $in['sum'];
                        if (isset($result[$product])) {
                            $result[$product] += $sum;
                        } else {
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
                    $header->tanggal_jurnal = $transaction_date;
                    $header->user_created = null;
                    $header->user_modified = null;
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

                        if ($id_cabang == 1) {
                            $akun_persediaan = $barang->id_akun;
                        } else {
                            $format_akun = 'id_akun' . $id_cabang;
                            $akun_persediaan = $barang->$format_akun;
                        }

                        // Log::info(json_encode($barang->id_barang));
                        $detail = new JurnalDetail();
                        $detail->id_jurnal = $header->id_jurnal;
                        $detail->index = $i + 1;
                        $detail->id_akun = $akun_persediaan;
                        $detail->keterangan = "HPP Transfer Cabang Masuk " . $id_transaksi;
                        $detail->id_transaksi = $id_transaksi;
                        $detail->debet = $in;
                        $detail->credit = 0;
                        $detail->user_created = null;
                        $detail->user_modified = null;
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
                    $detail->keterangan = "HPP Transfer Cabang Masuk " . $id_transaksi;
                    $detail->id_transaksi = $id_transaksi;
                    $detail->debet = 0;
                    $detail->credit = $sum_kredit;
                    $detail->user_created = null;
                    $detail->user_modified = null;
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
                "result" => true,
                "message" => "Successfully proceed closing journal inventory transfer",
            ]);
        } catch (\Exception $e) {
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
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    public function stockCorrection(Request $request)
    {
        try {
            // dd("disini");
            // Init data
            $id_cabang = 1;//$request->id_cabang;
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
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                }
                return response()->json([
                    "result" => false,
                    "message" => "Jurnal Closing Koreksi Stok Gagal. Akun Koreksi Stok tidak ditemukan",
                ]);
            }

            // Get data koreksi stok
            $data_header = StockCorrectionHeader::where("status_koreksi_stok", $status)->where("id_cabang", $id_cabang)->whereBetween("tanggal_koreksi_stok", [$start_date, $end_date])->get();
            // $data_header = StockCorrectionHeader::where("status_koreksi_stok", $status)->where("id_koreksi_stok", 306)->get();
            // dd(json_encode($data_header));
            // dd(json_encode($data_header));
            DB::beginTransaction();
            foreach ($data_header as $key => $header) {
                $id_transaksi = $header->nama_koreksi_stok;
                $transaction_date = $header->tanggal_koreksi_stok;
                $details = [];
                // Delete detail and header existing first
                $getHeaderDelete = JurnalDetail::join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                    ->where("jurnal_header.id_transaksi", "Closing " . $id_transaksi)
                    ->delete();
                // get koreksi stok detail
                $data_detail = StockCorrectionDetail::selectRaw("koreksi_stok_detail.id_koreksi_stok_detail, koreksi_stok_detail.id_koreksi_stok, koreksi_stok_detail.id_barang, koreksi_stok_detail.debit_koreksi_stok_detail as debet, koreksi_stok_detail.kredit_koreksi_stok_detail as kredit, koreksi_stok_detail.kode_batang_koreksi_stok_detail, koreksi_stok_detail.kode_batang_lama_koreksi_stok_detail,
                ks.beli_master_qr_code as debet_beli, ks.biaya_beli_master_qr_code as debet_biaya_beli, ks.produksi_master_qr_code as debet_produksi, ks.listrik_master_qr_code as debet_listrik, ks.pegawai_master_qr_code as debet_pegawai,
                ks.beli_master_qr_code as kredit_beli, ks.biaya_beli_master_qr_code as kredit_biaya_beli, ks.produksi_master_qr_code as kredit_produksi, ks.listrik_master_qr_code as kredit_listrik, ks.pegawai_master_qr_code as kredit_pegawai")
                    ->join('master_qr_code as ks', function ($join) {
                        $join->on('ks.kode_batang_master_qr_code', '=', DB::raw('CASE WHEN kode_batang_lama_koreksi_stok_detail = "" THEN kode_batang_koreksi_stok_detail ELSE kode_batang_lama_koreksi_stok_detail END'));
                    })
                    ->where("koreksi_stok_detail.id_koreksi_stok", $header->id_koreksi_stok)
                // ->where("koreksi_stok_detail.id_koreksi_stok", "296")
                    ->groupBy("koreksi_stok_detail.id_koreksi_stok_detail")->get();
                // dd(count($data_detail));
                $i = 0;
                foreach ($data_detail as $key => $detail) {
                    // Get master qr code
                    $debet_value = ($detail->debet * $detail->debet_beli) + ($detail->debet * $detail->debet_biaya_beli) + ($detail->debet * $detail->debet_produksi) + ($detail->debet * $detail->debet_listrik) + ($detail->debet * $detail->debet_pegawai);
                    $kredit_value = ($detail->kredit * $detail->kredit_beli) + ($detail->kredit * $detail->kredit_biaya_beli) + ($detail->kredit * $detail->kredit_produksi) + ($detail->kredit * $detail->kredit_listrik) + ($detail->kredit * $detail->kredit_pegawai);
                    $sum = $debet_value + $kredit_value;
                    $details[] = [
                        "barang" => $detail->id_barang,
                        "debet" => $detail->debet,
                        "kredit" => $detail->kredit,
                        "sum" => $sum,
                    ];
                }
                // dd(json_encode($details));
                // Grouping and sum the same barang
                $grouped = [];
                $grouped = array_reduce($details, function ($result, $in) {
                    $product = $in['barang'];
                    $sum = $in['sum'];
                    if (isset($result[$product])) {
                        $result[$product] += $sum;
                    } else {
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
                $header->catatan = "Koreksi Stok " . $id_transaksi;
                $header->void = 0;
                $header->tanggal_jurnal = $transaction_date;
                $header->user_created = null;
                $header->user_modified = null;
                $header->dt_created = $end_date;
                $header->dt_modified = $end_date;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
                // dd($header);
                if (!$header->save()) {
                    // Revert post closing
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Jurnal Closing Koreksi Stok Gagal. Error when store Jurnal data on table detail, barang not found",
                        ]);
                    }

                    if ($id_cabang == 1) {
                        $akun_persediaan = $barang->id_akun;
                    } else {
                        $format_akun = 'id_akun' . $id_cabang;
                        $akun_persediaan = $barang->$format_akun;
                    }
                    Log::info("out foreach : ".round($out, 2));
                    // Log::info(json_encode($barang->id_barang));
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $akun_persediaan;
                    $detail->keterangan = "Koreksi Stok " . $id_transaksi . " " . $barang->nama_barang;
                    $detail->id_transaksi = $id_transaksi;
                    $detail->debet = ($out > 0) ? 0 : abs($out);
                    $detail->credit = ($out > 0) ? $out : 0;
                    $detail->user_created = null;
                    $detail->user_modified = null;
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
                            "message" => "Jurnal Closing Koreksi Stok Gagal. Error when store Jurnal data on table detail",
                        ]);
                    }
                    $sum_val += round($out, 2);
                    $i++;
                }
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $i + 1;
                $detail->id_akun = $hpp_account->value2;
                $detail->keterangan = "Koreksi Stok " . $id_transaksi;
                $detail->id_transaksi = $id_transaksi;
                $detail->debet = ($sum_val > 0) ? $sum_val : 0;
                $detail->credit = ($sum_val > 0) ? 0 : abs($sum_val);
                $detail->user_created = null;
                $detail->user_modified = null;
                $detail->dt_created = $end_date;
                $detail->dt_modified = $end_date;
                Log::info("sum val akhir : ".$sum_val);
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
                        "message" => "Jurnal Closing Koreksi Stok Gagal. Error when store Jurnal data on table detail",
                    ]);
                }

            }
            // DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully proceed closing journal stock correction",
            ]);
        } catch (\Exception $e) {
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
                "result" => false,
                "message" => $message,
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

            // Get data retur jual
            $data_header = DB::table('retur_penjualan')->where("id_cabang", $id_cabang)->whereBetween("tanggal_retur_penjualan", [$start_date, $end_date])->get();
            // dd($data_header);
            DB::beginTransaction();

            foreach ($data_header as $key => $header) {
                // dd($header);
                $id_transaksi = $header->nama_retur_penjualan;
                $transaction_date = $header->tanggal_retur_penjualan;

                // Delete detail and header existing first
                $jurnal_header = JurnalHeader::where("id_transaksi", 'Closing ' . $id_transaksi)->where("catatan", "Closing Retur Penjualan")->get();
                // dd($jurnal_header);

                foreach ($jurnal_header as $jurnal) {
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
                    $sum = ($qty * $detail->beli_master_qr_code) + ($qty * $detail->biaya_beli_master_qr_code) + ($qty * $detail->produksi_master_qr_code) + ($qty * $detail->listrik_master_qr_code) + ($qty * $detail->pegawai_master_qr_code);
                    $details[] = [
                        "qr_code" => $detail->kode_batang_retur_penjualan_detail,
                        "barang" => $detail->id_barang,
                        "qty" => $qty,
                        "sum" => $sum,
                        "note" => $detail->nama_barang . ' - ' . $detail->jumlah_retur_penjualan_detail . ' ' . $detail->nama_satuan,
                    ];
                }

                // dd($details);

                // Log::info(json_encode($details));
                // Grouping and sum the same barang
                $grouped_out = array_reduce($details, function ($result, $out) {
                    $product = $out['barang'];
                    $sum = $out['sum'];
                    if (isset($result[$product])) {
                        $result[$product]['sum'] += $sum;
                    } else {
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
                $header->catatan = "Closing Retur Penjualan " . $id_transaksi;
                $header->void = 0;
                $header->tanggal_jurnal = $transaction_date;
                $header->user_created = null;
                $header->user_modified = null;
                $header->dt_created = $end_date;
                $header->dt_modified = $end_date;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
                // dd($header);
                if (!$header->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Store Closing retur penjualan failed, Error when store Jurnal data on table detail, barang not found",
                        ]);
                    }

                    if ($id_cabang == 1) {
                        $akun_persediaan = $barang->id_akun;
                    } else {
                        $format_akun = 'id_akun' . $id_cabang;
                        $akun_persediaan = $barang->$format_akun;
                    }

                    // akun persediaan barang
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $akun_persediaan;
                    $detail->keterangan = "Persediaan Jurnal Retur Penjualan " . $id_transaksi . ' - ' . $out['note'];
                    $detail->id_transaksi = $id_transaksi;
                    $detail->debet = $out['sum'];
                    $detail->credit = 0;
                    $detail->user_created = null;
                    $detail->user_modified = null;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;

                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Store Closing retur penjualan failed, Error when store Jurnal data on table detail",
                        ]);
                    }
                    // $sum_val += $out['sum'];
                    $i++;
                }

                // dd($detail);

                foreach ($grouped_out as $key => $out) {
                    // Get akun barang
                    $barang = Barang::find($key);

                    if (!$barang) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Store Closing retur penjualan failed, Error when store Jurnal data on table detail, barang not found",
                        ]);
                    }

                    if ($id_cabang == 1) {
                        $akun_retur_penjualan_barang = $barang->id_akun_retur_penjualan;
                    } else {
                        $format_akun = 'id_akun_retur_penjualan' . $id_cabang;
                        $akun_retur_penjualan_barang = $barang->$format_akun;
                    }

                    if ($akun_retur_penjualan_barang == null) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail. Akun Retur Penjualan Barang " . $barang->kode_barang . ' - ' . $barang->nama_barang . ' can not null.',
                        ]);
                    } else {
                        $data_akun_penjualan_barang = Akun::find($akun_retur_penjualan_barang);
                        if (empty($data_akun_penjualan_barang)) {
                            DB::rollback();
                            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                            }
                            return response()->json([
                                "result" => false,
                                "message" => "Error when store Jurnal data on table detail. Akun Retur Penjualan Barang " . $barang->kode_barang . ' - ' . $barang->nama_barang . ' not found.',
                            ]);
                        }
                    }

                    // akun hpp retur penjualan
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $akun_retur_penjualan_barang;
                    $detail->keterangan = "Persediaan Jurnal Retur Penjualan " . $id_transaksi . ' - ' . $out['note'];
                    $detail->id_transaksi = $id_transaksi;
                    $detail->debet = 0;
                    $detail->credit = $out['sum'];
                    $detail->user_created = null;
                    $detail->user_modified = null;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // dd(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Store Closing retur penjualan failed, Error when store Jurnal data on table detail",
                        ]);
                    }

                    $i++;
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully proceed closing journal retur penjualan",
            ]);
        } catch (\Exception $e) {
            $message = "Error when closing journal retur penjualan";
            DB::rollback();
            $month = $request->month;
            $year = $request->year;
            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
            }
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
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

            // $hpp_account = Setting::where("id_cabang", $id_cabang)->where("code", "HPP Pemakaian")->first();
            // // dd($hpp_account);
            // if (!$hpp_account) {
            //     // Revert post closing
            //     $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
            //     if ($check) {
            //         $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
            //     }

            //     return response()->json([
            //         "result" => FALSE,
            //         "message" => "Jurnal Closing Pemakaian Gagal. Akun Pemakaian tidak ditemukan"
            //     ]);
            // }

            DB::beginTransaction();

            // Get data pemakaian
            $data_header = DB::table('pemakaian_header')->where("id_cabang", $id_cabang)->whereBetween("tanggal", [$start_date, $end_date])->get();
            // dd($data_header);

            foreach ($data_header as $key => $header) {
                $hpp_account_pemakaian = Setting::where("id_cabang", $id_cabang)->where("code", "HPP Pemakaian " . $header->jenis_pemakaian)->first();
                $hpp_account = ($hpp_account_pemakaian) ? $hpp_account_pemakaian : Setting::where("id_cabang", $id_cabang)->where("code", "HPP Pemakaian")->first();
                Log::info("account pemakaian");
                Log::info($hpp_account);
                if (!$hpp_account) {
                    // Revert post closing
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }

                    return response()->json([
                        "result" => false,
                        "message" => "Jurnal Closing Pemakaian Gagal. Akun Pemakaian tidak ditemukan",
                    ]);
                }
                $id_transaksi = $header->kode_pemakaian;
                $transaction_date = $header->tanggal;

                // Delete detail and header existing first
                $jurnal_header = JurnalHeader::where("id_transaksi", 'Closing ' . $id_transaksi)->where("catatan", "Closing Pemakaian")->get();
                // dd($jurnal_header);

                foreach ($jurnal_header as $jurnal) {
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
                    $sum = ($qty * $detail->beli_master_qr_code) + ($qty * $detail->biaya_beli_master_qr_code) + ($qty * $detail->produksi_master_qr_code) + ($qty * $detail->listrik_master_qr_code) + ($qty * $detail->pegawai_master_qr_code);
                    $details[] = [
                        "qr_code" => $detail->kode_batang,
                        "barang" => $detail->id_barang,
                        "qty" => $qty,
                        "sum" => $sum,
                        "note" => $detail->nama_barang . ' - ' . $detail->jumlah . ' ' . $detail->nama_satuan,
                    ];
                }

                // Log::info(json_encode($details));
                // Grouping and sum the same barang
                $grouped_out = array_reduce($details, function ($result, $out) {
                    $product = $out['barang'];
                    $sum = $out['sum'];
                    if (isset($result[$product])) {
                        $result[$product]['sum'] += $sum;
                    } else {
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
                $header->tanggal_jurnal = $transaction_date;
                $header->user_created = null;
                $header->user_modified = null;
                $header->dt_created = $end_date;
                $header->dt_modified = $end_date;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);

                if (!$header->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Store Closing pemakaian failed, Error when store Jurnal data on table detail, barang not found",
                        ]);
                    }

                    if ($id_cabang == 1) {
                        $akun_persediaan = $barang->id_akun;
                    } else {
                        $format_akun = 'id_akun' . $id_cabang;
                        $akun_persediaan = $barang->$format_akun;
                    }

                    // akun persediaan barang
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $akun_persediaan;
                    $detail->keterangan = "Pemakaian Barang " . $id_transaksi . ' - ' . $out['note'];
                    $detail->id_transaksi = $id_transaksi;
                    $detail->debet = 0;
                    $detail->credit = round($out['sum'], 2);
                    $detail->user_created = null;
                    $detail->user_modified = null;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // dd($detail);

                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Store Closing pemakaian failed, Error when store Jurnal data on table detail",
                        ]);
                    }
                    $sum_val += round($out['sum'], 2);
                    $i++;
                }

                // akun hpp pemakaian
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $i + 1;
                $detail->id_akun = $hpp_account->value2;
                $detail->keterangan = "Pemakaian barang " . $id_transaksi;
                $detail->id_transaksi = $id_transaksi;
                $detail->debet = round($sum_val, 2);
                $detail->credit = 0;
                $detail->user_created = null;
                $detail->user_modified = null;
                $detail->dt_created = $end_date;
                $detail->dt_modified = $end_date;
                // dd(json_encode($detail));
                if (!$detail->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Store Closing pemakaian failed, Error when store Jurnal data on table detail",
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                "result" => true,
                "message" => "Successfully proceed closing journal pemakaian",
            ]);
        } catch (\Exception $e) {
            $message = "Error when closing journal pemakaian";
            DB::rollback();
            $month = $request->month;
            $year = $request->year;
            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
            }
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    // step 6
    public function sales(Request $request)
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
            $hpp_account = Setting::where("id_cabang", $id_cabang)->where("code", "HPP Penjualan")->first();
            // dd($hpp_account);
            if (!$hpp_account) {
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                }
                return response()->json([
                    "result" => false,
                    "message" => "Akun HPP Penjualan tidak ditemukan",
                ]);
            }

            // Get data pindah barang
            $data_header = SalesHeader::where("id_cabang", $id_cabang)->whereBetween("tanggal_penjualan", [$start_date, $end_date])->get();
            // dd($data_header);
            DB::beginTransaction();
            foreach ($data_header as $key => $header) {
                // Log::info($header->kode_pindah_barang);
                $id_transaksi = $header->nama_penjualan;
                $transaction_date = $header->tanggal_penjualan;
                // Delete detail and header existing first
                $jurnal_header = JurnalHeader::where("id_transaksi", 'Closing ' . $id_transaksi)->where("catatan", "Closing Penjualan")->get();

                foreach ($jurnal_header as $jurnal) {
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
                    $sum = ($qty * $detail->beli_master_qr_code) + ($qty * $detail->biaya_beli_master_qr_code) + ($qty * $detail->produksi_master_qr_code) + ($qty * $detail->listrik_master_qr_code) + ($qty * $detail->pegawai_master_qr_code);
                    $details[] = [
                        "qr_code" => $detail->kode_batang_lama_penjualan_detail,
                        "barang" => $detail->id_barang,
                        "qty" => $qty,
                        "sum" => $sum,
                        "note" => $detail->nama_barang . ' - ' . $detail->jumlah_penjualan_detail . ' ' . $detail->nama_satuan,
                    ];
                }
                // Log::info(json_encode($details));
                // Grouping and sum the same barang
                $grouped_out = array_reduce($details, function ($result, $out) {
                    $product = $out['barang'];
                    $sum = $out['sum'];
                    if (isset($result[$product])) {
                        $result[$product]['sum'] += $sum;
                    } else {
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
                $header->tanggal_jurnal = $transaction_date;
                $header->user_created = null;
                $header->user_modified = null;
                $header->dt_created = $end_date;
                $header->dt_modified = $end_date;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
                // dd($header);
                if (!$header->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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

                    if ($id_cabang == 1) {
                        $akun_persediaan = $barang->id_akun;
                    } else {
                        $format_akun = 'id_akun' . $id_cabang;
                        $akun_persediaan = $barang->$format_akun;
                    }

                    // akun persediaan barang
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $i + 1;
                    $detail->id_akun = $akun_persediaan;
                    $detail->keterangan = "Harga Produksi Penjualan " . $id_transaksi . ' - ' . $out['note'];
                    // $detail->id_transaksi = $id_transaksi;
                    $detail->debet = 0;
                    $detail->credit = $out['sum'];
                    $detail->user_created = null;
                    $detail->user_modified = null;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // Log::info(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail",
                        ]);
                    }
                    $i++;

                    if ($id_cabang == 1) {
                        $akun_hpp_penjualan = $barang->id_akun_hpp_penjualan;
                    } else {
                        $format_akun = 'id_akun_hpp_penjualan' . $id_cabang;
                        $akun_hpp_penjualan = $barang->$format_akun;
                    }

                    if ($akun_hpp_penjualan == null) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail. Akun HPP Penjualan Barang " . $barang->kode_barang . ' - ' . $barang->nama_barang . ' can not null.',
                        ]);
                    } else {
                        $data_akun_penjualan_barang = Akun::find($akun_hpp_penjualan);
                        if (empty($data_akun_penjualan_barang)) {
                            DB::rollback();
                            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                            if ($check) {
                                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
                    $detail->keterangan = "Harga Produksi Penjualan " . $id_transaksi;
                    // $detail->id_transaksi = $id_transaksi;
                    $detail->debet = $out['sum'];
                    $detail->credit = 0;
                    $detail->user_created = null;
                    $detail->user_modified = null;
                    $detail->dt_created = $end_date;
                    $detail->dt_modified = $end_date;
                    // dd(json_encode($detail));
                    if (!$detail->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
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
                "result" => true,
                "message" => "Successfully proceed closing journal penjualan",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            $month = $request->month;
            $year = $request->year;
            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
            }
            $message = "Error when closing journal penjualan";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    // Step 7
    public function depreciation(Request $request)
    {
        try {
            // Init data
            $id_cabang = $request->id_cabang;
            $journal_type = "ME";
            $month = $request->month;
            $year = $request->year;
            $end_date = date("Y-m-t", strtotime("$year-$month-1"));
            $asset_account = Setting::where("id_cabang", $id_cabang)->where("code", "Kategori Asset")->first();
            $cabang = Cabang::find($id_cabang);
            // Log::info("akun penyusutan");
            // Log::info($hpp_account);
            if (!$asset_account) {
                return response()->json([
                    "result" => false,
                    "message" => "Akun Kategori Asset tidak ditemukan",
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
            Log::info(json_encode($data_asset));
            DB::beginTransaction();
            $jurnal_header = JurnalHeader::where('id_transaksi', "Jurnal Penyusutan")->where('tanggal_jurnal', $end_date)->where('id_cabang', $id_cabang)->get();

            foreach ($jurnal_header as $jurnal) {
                JurnalDetail::where('id_jurnal', $jurnal->id_jurnal)->delete();
            }
            JurnalHeader::where('id_transaksi', "Jurnal Penyusutan")->where('tanggal_jurnal', $end_date)->where('id_cabang', $id_cabang)->delete();
            Log::info("data asset");
            Log::info(count($data_asset));

            // dd($data_header);
            if (count($data_asset) > 0) {
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
                Log::info("jurnaling");
                Log::info($header->kode_jurnal);
                if (!$header->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table header",
                    ]);
                }
                $index = 1;
                $total_asset = count($data_asset);
                $keterangan = "Penyusutan ";
                foreach ($data_asset as $asset) {
                    if ($index == $total_asset) {
                        $keterangan .= $asset->nama_barang;
                    } else {
                        $keterangan .= $asset->nama_barang . ', ';
                    }

                    // Store detail
                    $detail = new JurnalDetail();
                    $detail->id_jurnal = $header->id_jurnal;
                    $detail->index = $index;
                    if (strtoupper($cabang->nama_cabang) == 'SURABAYA') {
                        $detail->id_akun = $asset->id_akun_biaya;
                    } else if (strtoupper($cabang->nama_cabang) == 'JAKARTA') {
                        $detail->id_akun = $asset->id_akun_biaya2;
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
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail",
                        ]);
                    }

                    $index++;

                    $detail2 = new JurnalDetail();
                    $detail2->id_jurnal = $header->id_jurnal;
                    $detail2->index = $index;
                    if (strtoupper($cabang->nama_cabang) == 'SURABAYA') {
                        $detail2->id_akun = $asset->id_akun;
                    } else if (strtoupper($cabang->nama_cabang) == 'JAKARTA') {
                        $detail2->id_akun = $asset->id_akun2;
                    }
                    $detail2->keterangan = "Penyusutan " . $asset->nama_barang;
                    // $detail->id_transaksi = $id_transaksi;
                    $detail2->debet = 0;
                    $detail2->credit = $asset->susut;
                    $detail2->dt_created = $end_date;
                    $detail2->dt_modified = $end_date;
                    // dd(json_encode($detail));
                    if (!$detail2->save()) {
                        DB::rollback();
                        $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                        if ($check) {
                            $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                        }
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail",
                        ]);
                    }
                    $index++;
                    // Log::info(json_encode($grouped_out));
                    // dd(json_encode($grouped_out));

                }

                $header->catatan = $keterangan;

                if (!$header->save()) {
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table header",
                    ]);
                }
                DB::commit();
                return response()->json([
                    "result" => true,
                    "message" => "Successfully proceed closing journal penyusutan",
                ]);
            } else {
                return response()->json([
                    "result" => true,
                    "message" => "Successfully proceed closing journal penyusutan, with status empty data",
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            // Revert post closing
            $month = $request->month;
            $year = $request->year;
            $id_cabang = $request->id_cabang;
            $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
            if ($check) {
                $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
            }
            $message = "Error when closing journal penyusutan";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    // Step 8
    public function closingJournal(Request $request)
    {
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
            $profitlosshold_account = Setting::where("id_cabang", $id_cabang)->where("code", "LR Ditahan")->first();
            // Log::info("akun closing");
            // Log::info(json_encode($closing_account));
            // Log::info("akun laba rugi");
            // Log::info(json_encode($profitloss_account));
            if (!$closing_account || !$profitloss_account) {
                // Revert post closing
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                }
                return response()->json([
                    "result" => false,
                    "message" => "Jurnal Closing Closing Jurnal Gagal. Akun Closing atau Laba Rugi tidak ditemukan",
                ]);
            }

            if($month == 12 && !$profitlosshold_account){
                $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                if ($check) {
                    $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                }
                return response()->json([
                    "result" => false,
                    "message" => "Jurnal Closing Closing Jurnal Gagal. Akun Laba Rugi Ditahan tidak ditemukan",
                ]);
            }

            DB::beginTransaction();
            // Delete all journal before transaction
            $jurnal_header = JurnalHeader::where("id_transaksi", "Closing 1 $noteDate")->where('tanggal_jurnal', $end_date)->where("catatan", "Closing 1 $noteDate")->where("id_cabang", $id_cabang)->get();
            // dd(count($jurnal_header));
            foreach ($jurnal_header as $jurnal) {
                JurnalDetail::where("id_jurnal", $jurnal->id_jurnal)->delete();
                JurnalHeader::where("id_jurnal", $jurnal->id_jurnal)->delete();
            }
            $jurnal_header2 = JurnalHeader::where("id_transaksi", "Closing 2 $noteDate")->where('tanggal_jurnal', $end_date)->where("catatan", "Closing 2 $noteDate")->where("id_cabang", $id_cabang)->get();
            // dd(count($jurnal_header2));
            foreach ($jurnal_header2 as $jurnal2) {
                JurnalDetail::where("id_jurnal", $jurnal2->id_jurnal)->delete();
                JurnalHeader::where("id_jurnal", $jurnal2->id_jurnal)->delete();
            }

            if($month == 12){
                $jurnal_header3 = JurnalHeader::where("id_transaksi", "Closing 3 $noteDate")->where('tanggal_jurnal', $end_date)->where("catatan", "Closing 3 $noteDate")->where("id_cabang", $id_cabang)->get();
                // dd(count($jurnal_header2));
                foreach ($jurnal_header3 as $jurnal3) {
                    JurnalDetail::where("id_jurnal", $jurnal3->id_jurnal)->delete();
                    JurnalHeader::where("id_jurnal", $jurnal3->id_jurnal)->delete();
                }
            }

            // Get all journal based on tipe laba rugi, void 0, between startdate - enddate
            $data_ledgers = JurnalDetail::join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                ->where("jurnal_header.void", "0")
                ->where("master_akun.tipe_akun", "1")
                ->where("master_akun.id_cabang", $id_cabang)
                ->whereRaw("((jurnal_header.id_transaksi <> 'Closing 1 $noteDate' AND jurnal_header.id_transaksi <> 'Closing 2 $noteDate' AND jurnal_header.id_transaksi <> 'Closing 3 $noteDate') OR jurnal_header.id_transaksi IS NULL)")
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
            $header->user_created = null;
            $header->user_modified = null;
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
                // Get saldo balance if exist
                $saldoBalance = SaldoBalance::where("id_cabang", $id_cabang)->where("bulan", $month)->where("tahun", $year)->where("id_akun", $value->id_akun)->first();
                $balanceDebet = ($saldoBalance) ? $saldoBalance->debet : 0;
                $balanceKredit = ($saldoBalance) ? $saldoBalance->credit : 0;
                $sumBalance = $balanceDebet - $balanceKredit;

                // Calculate sum
                $sum = $sumBalance + $value->debet - $value->kredit;
                // Log::info("closing sum ".$closingSum." debet ".$value->debet." kredit ".$value->kredit);
                $closingSum = round((float) $closingSum, 2) + round((float) $sum, 2);
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $i + 1;
                $detail->id_akun = $value->id_akun;
                $detail->keterangan = "Jurnal Closing 1 $noteDate";
                $detail->id_transaksi = null;
                $detail->debet = ($sum < 0) ? abs($sum) : 0;
                $detail->credit = ($sum < 0) ? 0 : $sum;
                $detail->user_created = null;
                $detail->user_modified = null;
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
            $detailClosing1->id_transaksi = null;
            $detailClosing1->debet = ($closingSum < 0) ? abs($closingSum) : 0;
            $detailClosing1->credit = ($closingSum < 0) ? 0 : $closingSum;
            $detailClosing1->user_created = null;
            $detailClosing1->user_modified = null;
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
            $header2->user_created = null;
            $header2->user_modified = null;
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
            $detailClosing21->id_transaksi = null;
            $detailClosing21->debet = ($closingSum < 0) ? 0 : abs($closingSum);
            $detailClosing21->credit = ($closingSum < 0) ? $closingSum : 0;
            $detailClosing21->user_created = null;
            $detailClosing21->user_modified = null;
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
            $detailClosing22->id_transaksi = null;
            $detailClosing22->debet = ($closingSum < 0) ? abs($closingSum) : 0;
            $detailClosing22->credit = ($closingSum < 0) ? 0 : $closingSum;
            $detailClosing22->user_created = null;
            $detailClosing22->user_modified = null;
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

            // create closing 3
            if($month == 12){
                $header3 = new JurnalHeader();
                $header3->id_cabang = $id_cabang;
                $header3->jenis_jurnal = $journal_type;
                $header3->id_transaksi = "Closing 3 $noteDate";
                $header3->catatan = "Closing 3 $noteDate";
                $header3->void = 0;
                $header3->tanggal_jurnal = $end_date;
                $header3->user_created = null;
                $header3->user_modified = null;
                $header3->dt_created = $end_date;
                $header3->dt_modified = $end_date;
                $header3->kode_jurnal = $this->generateJournalCode($id_cabang, $journal_type);
                // Log::info(json_encode($header3));
                if (!$header3->save()) {
                    DB::rollback();
                    // Revert post closing
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Jurnal Closing Closing Journal Gagal. Error when store Jurnal data on table header 3",
                    ]);
                }
                // Detail closing 3.1
                $detailClosing31 = new JurnalDetail();
                $detailClosing31->id_jurnal = $header3->id_jurnal;
                $detailClosing31->index = 1;
                $detailClosing31->id_akun = $profitloss_account->value2;
                $detailClosing31->keterangan = "Jurnal Closing 3 $noteDate";
                $detailClosing31->id_transaksi = null;
                $detailClosing31->debet = ($closingSum < 0) ? abs($closingSum) : 0;
                $detailClosing31->credit = ($closingSum < 0) ? 0 : $closingSum;
                $detailClosing31->user_created = null;
                $detailClosing31->user_modified = null;
                $detailClosing31->dt_created = $end_date;
                $detailClosing31->dt_modified = $end_date;
                // Log::info(json_encode($detailClosing31));
                // Log::info($closingSum);
                if (!$detailClosing31->save()) {
                    DB::rollback();
                    // Revert post closing
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Jurnal Closing Closing Journal Gagal. Error when store Jurnal data on table detail 3.1",
                    ]);
                }
                // Detail closing 3.2
                $detailClosing32 = new JurnalDetail();
                $detailClosing32->id_jurnal = $header3->id_jurnal;
                $detailClosing32->index = 2;
                $detailClosing32->id_akun = $profitlosshold_account->value2;
                $detailClosing32->keterangan = "Jurnal Closing 3 $noteDate";
                $detailClosing32->id_transaksi = null;
                $detailClosing32->debet = ($closingSum < 0) ? 0 : abs($closingSum);
                $detailClosing32->credit = ($closingSum < 0) ? $closingSum : 0;
                $detailClosing32->user_created = null;
                $detailClosing32->user_modified = null;
                $detailClosing32->dt_created = $end_date;
                $detailClosing32->dt_modified = $end_date;
                // Log::info(json_encode($detailClosing32));
                // Log::info($closingSum);
                if (!$detailClosing32->save()) {
                    DB::rollback();
                    // Revert post closing
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Jurnal Closing Closing Journal Gagal. Error when store Jurnal data on table detail 3.2",
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully proceed closing closing journal",
            ]);
        } catch (\Exception $e) {
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
                "result" => false,
                "message" => $message,
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
            "result" => true,
            "message" => "Ajax function succeed",
        ]);
    }

    public function saldoTransfer(Request $request)
    {
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
                $saldo_debet = ($saldo) ? $saldo->saldo_debet : 0;
                $saldo_kredit = ($saldo) ? $saldo->saldo_kredit : 0;
                // Log::info("saldo debet ".$saldo_debet." saldo kredit ".$saldo_kredit);
                $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                // Log::info("saldo debet ".$debet." saldo kredit ".$kredit);
                $saldo_debet = $saldo_debet + $debet + (isset($data_ledgers->debet) ? $data_ledgers->debet : 0);
                $saldo_kredit = $saldo_kredit + $kredit + (isset($data_ledgers->kredit) ? $data_ledgers->kredit : 0);
                $saldoAkhir = (float) $saldo_debet - (float) $saldo_kredit;

                // Insert into saldo balance
                $saldo_balance = new SaldoBalance;
                $saldo_balance->id_cabang = $akun->id_cabang;
                $saldo_balance->id_akun = $akun->id_akun;
                $saldo_balance->bulan = $nextMonth;
                $saldo_balance->tahun = $nextYear;
                $saldo_balance->debet = ($saldoAkhir > 0) ? $saldoAkhir : 0; //$saldo_debet;
                $saldo_balance->credit = ($saldoAkhir > 0) ? 0 : floatval(abs($saldoAkhir)); //$saldo_kredit;
                if (!$saldo_balance->save()) {
                    // Revert post closing
                    DB::rollback();
                    $check = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->first();
                    if ($check) {
                        $delete = Closing::where("month", $month)->where("year", $year)->where("id_cabang", $id_cabang)->delete();
                    }
                    return response()->json([
                        "result" => false,
                        "message" => "Jurnal Closing Transfer Saldo Gagal.",
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully proceed closing transfer saldo",
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
                "result" => false,
                "message" => $message,
            ]);
        }
    }
}
