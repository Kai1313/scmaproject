<?php

namespace App\Http\Controllers;

use App\Exports\ReportGeneralLedgerExport;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\SaldoBalance;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;

class ReportGeneralLedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (checkUserSession($request, 'report/general_ledger', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        // Check get parameter
        $cabang = ($request->has("cabang")) ? $request->cabang : null;
        if ($request->has("kode_akun")) {
            $kode_akun = $request->kode_akun;
            $getAkun = Akun::where("kode_akun", $kode_akun)->where("id_cabang", $cabang)->first();
            $id_akun = ($getAkun) ? $getAkun->id_akun : null;
        } else {
            $id_akun = ($request->has("id_akun")) ? $request->id_akun : null;
        }
        $startdate = ($request->has("startdate")) ? $request->startdate : null;
        $enddate = ($request->has("enddate")) ? $request->enddate : null;
        $type = ($request->has("type")) ? $request->type : null;
        $akun = ($id_akun) ? Akun::find($id_akun) : null;

        $data_cabang = getCabang();

        $data_cabang = $data_cabang->toArray();

        if (count($data_cabang) > 1) {
            $all = (object) [
                "id_cabang" => "",
                "nama_cabang" => "ALL",
                "kode_cabang" => "ALL",
            ];
            array_unshift($data_cabang, $all);
        }

        $data = [
            "pageTitle" => "SCA Accounting | Report Buku Besar",
            "data_cabang" => $data_cabang,
            "id_akun" => $id_akun,
            "akun" => $akun,
            "startdate" => $startdate,
            "enddate" => $enddate,
            "cabang" => $cabang,
            "type" => $type,
        ];
        // dd($data);

        return view('accounting.report.general_ledger.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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

    public function populate(Request $request)
    {
        try {
            Log::info($request->all());
            // Init Data
            $id_cabang = $request->id_cabang;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $type = $request->type;
            $coa = $request->coa;
            $month = date("m", strtotime("$start_date"));
            $year = date("Y", strtotime($start_date));
            $start_of_the_month = date("Y-m-01", strtotime($start_date));
            $saldo_date = date("Y-m-d", strtotime($start_date . " -1 day"));

            // Init Datatable
            $offset = $request->start;
            $limit = $request->length;
            $keyword = $request->search['value'];
            $sort = [];
            $order = ($request->order) ? $request->order : [];
            foreach ($order as $key => $order) {
                $columnIdx = $order['column'];
                $sortDir = $order['dir'];
                $sort[] = [
                    'column' => $request->columns[$columnIdx]['name'],
                    'dir' => $sortDir,
                ];
            }
            $draw = $request->draw;
            $current_page = $offset / $limit + 1;
            $data_ledgers = JurnalDetail::join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                ->where("jurnal_header.void", "0")
                ->whereRaw('((jurnal_header.id_transaksi NOT LIKE "Closing 1%" AND jurnal_header.id_transaksi NOT LIKE "Closing 2%") OR jurnal_header.id_transaksi IS NULL)')
                // ->where("master_akun.id_cabang", $id_cabang)
                ->whereBetween("jurnal_header.tanggal_jurnal", [$start_date, $end_date]);
            if ($type == "recap") {
                $data_ledgers = $data_ledgers->selectRaw("jurnal_header.id_jurnal, master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, SUM(jurnal_detail.debet) as debet, SUM(jurnal_detail.credit) as kredit")->groupBy("jurnal_detail.id_akun");
            } else {
                $data_ledgers = $data_ledgers->selectRaw("jurnal_header.id_jurnal, jurnal_header.jenis_jurnal, master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, master_akun.posisi_debet, jurnal_header.kode_jurnal, jurnal_detail.keterangan, jurnal_detail.id_transaksi, jurnal_detail.debet as debet, jurnal_detail.credit as kredit, jurnal_header.tanggal_jurnal");
            }
            if ($id_cabang != "all" && $id_cabang != "") {
                $data_ledgers = $data_ledgers->where("jurnal_header.id_cabang", $id_cabang)->where("master_akun.id_cabang", $id_cabang);
            }
            if ($coa != "" && $coa != "all" && $coa != "recap") {
                $data_ledgers = $data_ledgers->where("jurnal_detail.id_akun", $coa);
            }
            if (isset($keyword)) {
                $data_ledgers->where(function ($query) use ($keyword, $type) {
                    if ($type == "recap") {
                        $query->orWhere("master_akun.kode_akun", "LIKE", "%$keyword%")
                            ->orWhere("master_akun.nama_akun", "LIKE", "%$keyword%");
                    } else {
                        $query->orWhere("master_akun.kode_akun", "LIKE", "%$keyword%")
                            ->orWhere("master_akun.nama_akun", "LIKE", "%$keyword%")
                            ->orWhere("jurnal_header.kode_jurnal", "LIKE", "%$keyword%")
                            ->orWhere("jurnal_detail.id_transaksi", "LIKE", "%$keyword%")
                            ->orWhere("jurnal_detail.keterangan", "LIKE", "%$keyword%");

                    }
                });
            }
            $filtered_data = $data_ledgers->get();
            if ($sort && $sort[0]["column"]) {
                if (!is_array($sort)) {
                    $message = "Invalid array for parameter sort";
                    $data = [
                        "result" => false,
                        "message" => $message,
                    ];
                    return response()->json($data);
                }

                foreach ($sort as $key => $s) {
                    $column = $s["column"];
                    $directon = $s["dir"];

                    if ($column != "") {
                        $data_ledgers->orderBy($column, $directon);
                    }
                }
            } else {
                if ($type == "recap") {
                    $data_ledgers->orderBy("master_akun.kode_akun", "ASC");
                } else {
                    $data_ledgers->orderBy("jurnal_header.tanggal_jurnal", "ASC");
                    $data_ledgers->orderBy("master_akun.kode_akun", "ASC");
                }
            }
            // pagination
            if ($current_page) {
                $page = $current_page;
                $limit_data = $data_ledgers->count();
                if ($limit) {
                    $limit_data = $limit;
                }
                $offset = ($page - 1) * $limit_data;
                if ($offset < 0) {
                    $offset = 0;
                }
                if ($limit != -1) {
                    $data_ledgers->skip($offset)->take($limit_data);
                }
            }

            // Get saldo awal dan saldo akhir
            $result = $data_ledgers->get();
            $result_detail = [];
            $resultNon = [];
            $saldo_awal_current = '';
            if ($type == "recap") {
                // Log::info($id_cabang);
                // // Get all master akun
                // $akuns = Akun::where("isshown", 1);
                // if ($id_cabang != "all" && $id_cabang != "") {
                //     $akuns = $akuns->where("master_akun.id_cabang", $id_cabang);
                // }
                // $akuns = $akuns->get();
                // foreach ($akuns as $key => $value) {
                //     $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $value->id_akun)->where("id_cabang", $value->id_cabang)->where("bulan", $month)->where("tahun", $year)->first();
                    
                //     $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                //         ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                //         ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                //         ->where("jurnal_detail.id_akun", $value->id_akun)
                //         ->where("jurnal_header.id_cabang", $value->id_cabang)
                //         ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                //         ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                //         ->groupBy("jurnal_detail.id_akun")->first();
                //     $saldo_debet = ($saldo) ? $saldo->saldo_debet : 0;
                //     $saldo_kredit = ($saldo) ? $saldo->saldo_kredit : 0;
                //     $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                //     $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                //     $saldo_awal = ($saldo_debet - $saldo_kredit) + ($debet - $kredit);
                //     $saldo_akhir = $saldo_awal + $value->debet - $value->kredit;
                //     $resultNon[] = (object) [
                //         "id_jurnal" => "-",
                //         "id_cabang" => $value->id_cabang,
                //         "id_akun" => $value->id_akun,
                //         "kode_akun" => $value->kode_akun,
                //         "nama_akun" => $value->nama_akun,
                //         "debet" => 0,
                //         "kredit" => 0,
                //         "saldo_awal" => round($saldo_awal, 2),
                //         "saldo_akhir" => round($saldo_akhir, 2),
                //     ];
                // }
            }
            else {
                $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $coa)->where("id_cabang", $id_cabang)->where("bulan", (int) $month)->where("tahun", (int) $year)->first();
                // Log::info($saldo);
                $dataCoa = DB::table('master_akun')->where('id_akun', $coa)->first();
                if ($saldo) {
                    $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                        ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                        ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                        ->where("jurnal_detail.id_akun", $coa)
                        ->where("jurnal_header.id_cabang", $id_cabang)
                        ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                        ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                        ->groupBy("jurnal_detail.id_akun")->first();
                    $saldo_debet = $saldo->saldo_debet;
                    $saldo_kredit = $saldo->saldo_kredit;
                    $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                    $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                    $saldo_awal_debet = $saldo_debet + $debet;
                    $saldo_awal_kredit = $saldo_kredit + $kredit;
    
                    $saldo_balance = ($dataCoa->posisi_debet != '0') ? $saldo_awal_debet - $saldo_awal_kredit : $saldo_awal_kredit - $saldo_awal_debet;
                    $result_detail[] = (object) [
                        "id_jurnal" => "",
                        "id_cabang" => $id_cabang,
                        "id_akun" => $coa,
                        "kode_akun" => $dataCoa->kode_akun,
                        "nama_akun" => $dataCoa->nama_akun,
                        "kode_jurnal" => "",
                        "jenis_jurnal" => "",
                        "id_transaksi" => "",
                        "keterangan" => "Saldo Awal",
                        "debet" => $saldo_awal_debet,
                        "kredit" => $saldo_awal_kredit,
                        "tanggal_jurnal" => $saldo_date,
                        "saldo_balance" => round($saldo_balance, 2),
                    ];
                }
                else {
                    $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                        ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                        ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                        ->where("jurnal_detail.id_akun", $coa)
                        ->where("jurnal_header.id_cabang", $id_cabang)
                        ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                        ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                        ->groupBy("jurnal_detail.id_akun")->first();
                    $saldo_debet = 0;
                    $saldo_kredit = 0;
                    $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                    $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                    $saldo_awal_debet = $saldo_debet + $debet;
                    $saldo_awal_kredit = $saldo_kredit + $kredit;
                    
                    $saldo_balance = ($dataCoa->posisi_debet != '0') ? $saldo_awal_debet - $saldo_awal_kredit : $saldo_awal_kredit - $saldo_awal_debet;
                    $result_detail[] = (object) [
                        "id_jurnal" => "",
                        "id_cabang" => $id_cabang,
                        "id_akun" => $coa,
                        "kode_akun" => $dataCoa->kode_akun,
                        "nama_akun" => $dataCoa->nama_akun,
                        "kode_jurnal" => "",
                        "jenis_jurnal" => "",
                        "id_transaksi" => "",
                        "keterangan" => "Saldo Awal",
                        "debet" => $saldo_awal_debet,
                        "kredit" => $saldo_awal_kredit,
                        "tanggal_jurnal" => $saldo_date,
                        "saldo_balance" => round($saldo_balance, 2),
                    ];
                }
            }
            Log::info(count($result));
            foreach ($result as $key => $value) {
                if ($type == "recap") {
                    $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $value->id_akun)->where("id_cabang", $value->id_cabang)->where("bulan", $month)->where("tahun", $year)->first();
                    
                    $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                        ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                        ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                        ->where("jurnal_detail.id_akun", $value->id_akun)
                        ->where("jurnal_header.id_cabang", $value->id_cabang)
                        ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                        ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                        ->groupBy("jurnal_detail.id_akun")->first();
                    $saldo_debet = ($saldo) ? $saldo->saldo_debet : 0;
                    $saldo_kredit = ($saldo) ? $saldo->saldo_kredit : 0;
                    $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                    $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                    $saldo_awal = ($saldo_debet - $saldo_kredit) + ($debet - $kredit);
                    $saldo_akhir = $saldo_awal + $value->debet - $value->kredit;
                    $value["saldo_awal"] = round($saldo_awal, 2);
                    $value["saldo_akhir"] = round($saldo_akhir, 2);
                } else {
                    $posisi = ($value->posisi_debet != "") ? $value->posisi_debet : 1;
                    $saldo_balance = ($posisi != 0) ? $saldo_balance + $value->debet - $value->kredit : $saldo_balance + $value->kredit - $value->debet;
                    $result_detail[] = (object) [
                        "id_jurnal" => $value->id_jurnal,
                        "id_cabang" => $value->id_cabang,
                        "id_akun" => $value->id_akun,
                        "kode_akun" => $value->kode_akun,
                        "nama_akun" => $value->nama_akun,
                        "kode_jurnal" => $value->kode_jurnal,
                        "jenis_jurnal" => $value->jenis_jurnal,
                        "id_transaksi" => $value->id_transaksi,
                        "keterangan" => $value->keterangan,
                        "debet" => $value->debet,
                        "kredit" => $value->kredit,
                        "tanggal_jurnal" => $value->tanggal_jurnal,
                        "saldo_balance" => round($saldo_balance, 2),
                    ];
                }
            }

            // Log::info(json_encode($resultNon));
            // Log::info(json_encode($result));
            $table['resultNon'] = $resultNon;
            $table['draw'] = $draw;
            $table['recordsTotal'] = $data_ledgers->count();
            $table['recordsFiltered'] = $filtered_data->count();
            $table['data'] = ($type == "recap") ? $result : $result_detail;
            return json_encode($table);
        } catch (\Exception $e) {
            $message = "Failed to get populate general ledger for view";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            // dd($request->all());
            // Init Data
            $id_cabang = $request->id_cabang;
            $cabang = Cabang::find($id_cabang);
            $nama_cabang = ($cabang) ? $cabang->nama_cabang : null;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $type = $request->type;
            $coa = $request->coa;
            $month = date("m", strtotime("$start_date"));
            $year = date("Y", strtotime($start_date));
            $start_of_the_month = date("Y-m-01", strtotime($start_date));
            $saldo_date = date("Y-m-d", strtotime($start_date . " -1 day"));

            $data_ledgers = JurnalDetail::join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                ->where("jurnal_header.void", "0")
                ->whereRaw('((jurnal_header.id_transaksi NOT LIKE "Closing 1%" AND jurnal_header.id_transaksi NOT LIKE "Closing 2%") OR jurnal_header.id_transaksi IS NULL)')
                ->whereBetween("jurnal_header.tanggal_jurnal", [$start_date, $end_date]);
            if ($type == "recap") {
                $data_ledgers = $data_ledgers->selectRaw("master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, SUM(jurnal_detail.debet) as debet, SUM(jurnal_detail.credit) as kredit")->groupBy("jurnal_detail.id_akun");
            } else {
                $data_ledgers = $data_ledgers->selectRaw("master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, jurnal_header.kode_jurnal, jurnal_detail.keterangan, jurnal_detail.id_transaksi, jurnal_detail.debet as debet, jurnal_detail.credit as kredit, jurnal_header.tanggal_jurnal");
            }
            if ($id_cabang != "all") {
                $data_ledgers = $data_ledgers->where("jurnal_header.id_cabang", $id_cabang);
            }
            if ($coa != "") {
                $data_ledgers = $data_ledgers->where("jurnal_detail.id_akun", $coa);
            }
            if ($type == "recap") {
                $data_ledgers->orderBy("master_akun.kode_akun", "ASC");
            } else {
                $data_ledgers->orderBy("jurnal_header.tanggal_jurnal", "ASC");
                $data_ledgers->orderBy("master_akun.kode_akun", "ASC");
            }
            // Get saldo awal dan saldo akhir
            $result = $data_ledgers->get();
            $result_detail = [];
            $saldo_awal_current = '';
            $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $coa)->where("id_cabang", $id_cabang)->where("bulan", $month)->where("tahun", $year)->first();
            if ($saldo) {
                $dataCoa = DB::table('master_akun')->where('id_akun', $coa)->first();
                $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                    ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                    ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                    ->where("jurnal_detail.id_akun", $coa)
                    ->where("jurnal_header.id_cabang", $id_cabang)
                    ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                    ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                    ->groupBy("jurnal_detail.id_akun")->first();
                $saldo_debet = $saldo->saldo_debet;
                $saldo_kredit = $saldo->saldo_kredit;
                $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                $saldo_awal_debet = $saldo_debet + $debet;
                $saldo_awal_kredit = $saldo_kredit + $kredit;

                $saldo_balance = ($dataCoa->posisi_debet != '0') ? $saldo_awal_debet - $saldo_awal_kredit : $saldo_awal_kredit - $saldo_awal_debet;
                $result_detail[] = (object) [
                    "id_jurnal" => "",
                    "id_cabang" => $id_cabang,
                    "id_akun" => $coa,
                    "kode_akun" => $dataCoa->kode_akun,
                    "nama_akun" => $dataCoa->nama_akun,
                    "kode_jurnal" => "",
                    "jenis_jurnal" => "",
                    "id_transaksi" => "",
                    "keterangan" => "Saldo Awal",
                    "debet" => $saldo_awal_debet,
                    "kredit" => $saldo_awal_kredit,
                    "tanggal_jurnal" => $saldo_date,
                    "saldo_balance" => round($saldo_balance, 2),
                ];
            }

            foreach ($result as $key => $value) {
                if ($type == "recap") {
                    $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $value->id_akun)->where("id_cabang", $value->id_cabang)->where("bulan", $month)->where("tahun", $year)->first();
                    $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                        ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                        ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                        ->where("jurnal_detail.id_akun", $value->id_akun)
                        ->where("jurnal_header.id_cabang", $value->id_cabang)
                        ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                        ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                        ->groupBy("jurnal_detail.id_akun")->first();
                    $saldo_debet = ($saldo) ? $saldo->saldo_debet : 0;
                    $saldo_kredit = ($saldo) ? $saldo->saldo_kredit : 0;
                    $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                    $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                    $saldo_awal = ($saldo_debet - $saldo_kredit) + ($debet - $kredit);
                    $saldo_akhir = $saldo_awal + $value->debet - $value->kredit;
                    $value["saldo_awal"] = $saldo_awal;
                    $value["saldo_akhir"] = $saldo_akhir;
                } else {
                    $saldo_balance = $saldo_balance + $value->debet - $value->kredit;
                    $result_detail[] = (object) [
                        "id_cabang" => $value->id_cabang,
                        "id_akun" => $value->id_akun,
                        "kode_akun" => $value->kode_akun,
                        "nama_akun" => $value->nama_akun,
                        "kode_jurnal" => $value->kode_jurnal,
                        "id_transaksi" => $value->id_transaksi,
                        "keterangan" => $value->keterangan,
                        "debet" => $value->debet,
                        "kredit" => $value->kredit,
                        "tanggal_jurnal" => $value->tanggal_jurnal,
                        "saldo_balance" => $saldo_balance,
                    ];
                }
            }
            $data = [
                "type" => $type,
                "start_date" => $start_date,
                "end_date" => $end_date,
                "cabang" => $nama_cabang,
                "data" => ($type == "recap") ? $result : $result_detail,
            ];
            if (count($data["data"]) > 0) {
                $pdf = PDF::loadView('accounting.report.general_ledger.print', $data);
                $pdf->setPaper('a4', 'landscape');
                $headers = [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="download.pdf"',
                ];
                return response()->json([
                    "result" => true,
                    "pdfData" => base64_encode($pdf->output()),
                    "pdfHeaders" => $headers,
                ]);
            } else {
                return response()->json([
                    "result" => false,
                    "message" => "Tidak ada data",
                ]);
            }
        } catch (\Exception $e) {
            $message = "Failed to print general ledger for pdf";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            // dd($request->all());
            // Init Data
            $id_cabang = $request->id_cabang;
            $cabang = Cabang::find($id_cabang);
            $nama_cabang = ($cabang) ? $cabang->nama_cabang : null;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $type = $request->type;
            $coa = $request->coa;
            $month = date("m", strtotime("$start_date"));
            $year = date("Y", strtotime($start_date));
            $start_of_the_month = date("Y-m-01", strtotime($start_date));
            $saldo_date = date("Y-m-d", strtotime($start_date . " -1 day"));

            $data_ledgers = JurnalDetail::join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                ->where("jurnal_header.void", "0")
                ->whereRaw('((jurnal_header.id_transaksi NOT LIKE "Closing 1%" AND jurnal_header.id_transaksi NOT LIKE "Closing 2%") OR jurnal_header.id_transaksi IS NULL)')
                ->whereBetween("jurnal_header.tanggal_jurnal", [$start_date, $end_date]);
            if ($type == "recap") {
                $data_ledgers = $data_ledgers->selectRaw("master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, SUM(jurnal_detail.debet) as debet, SUM(jurnal_detail.credit) as kredit")->groupBy("jurnal_detail.id_akun");
            } else {
                $data_ledgers = $data_ledgers->selectRaw("master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, jurnal_header.kode_jurnal, jurnal_detail.keterangan, jurnal_detail.id_transaksi, jurnal_detail.debet as debet, jurnal_detail.credit as kredit, jurnal_header.tanggal_jurnal");
            }
            if ($id_cabang != "all") {
                $data_ledgers = $data_ledgers->where("jurnal_header.id_cabang", $id_cabang);
            }
            if ($coa != "") {
                $data_ledgers = $data_ledgers->where("jurnal_detail.id_akun", $coa);
            }
            if ($type == "recap") {
                $data_ledgers->orderBy("master_akun.kode_akun", "ASC");
            } else {
                $data_ledgers->orderBy("jurnal_header.tanggal_jurnal", "ASC");
                $data_ledgers->orderBy("master_akun.kode_akun", "ASC");
            }
            // Get saldo awal dan saldo akhir
            $result = $data_ledgers->get();
            $result_detail = [];
            $saldo_awal_current = '';
            $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $coa)->where("id_cabang", $id_cabang)->where("bulan", $month)->where("tahun", $year)->first();
            if ($saldo) {
                $dataCoa = DB::table('master_akun')->where('id_akun', $coa)->first();
                $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                    ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                    ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                    ->where("jurnal_detail.id_akun", $coa)
                    ->where("jurnal_header.id_cabang", $id_cabang)
                    ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                    ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                    ->groupBy("jurnal_detail.id_akun")->first();
                $saldo_debet = $saldo->saldo_debet;
                $saldo_kredit = $saldo->saldo_kredit;
                $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                $saldo_awal_debet = $saldo_debet + $debet;
                $saldo_awal_kredit = $saldo_kredit + $kredit;

                $saldo_balance = ($dataCoa->posisi_debet != '0') ? $saldo_awal_debet - $saldo_awal_kredit : $saldo_awal_kredit - $saldo_awal_debet;
                $result_detail[] = (object) [
                    "id_jurnal" => "",
                    "id_cabang" => $id_cabang,
                    "id_akun" => $coa,
                    "kode_akun" => $dataCoa->kode_akun,
                    "nama_akun" => $dataCoa->nama_akun,
                    "kode_jurnal" => "",
                    "jenis_jurnal" => "",
                    "id_transaksi" => "",
                    "keterangan" => "Saldo Awal",
                    "debet" => $saldo_awal_debet,
                    "kredit" => $saldo_awal_kredit,
                    "tanggal_jurnal" => $saldo_date,
                    "saldo_balance" => round($saldo_balance, 2),
                ];
            }

            foreach ($result as $key => $value) {
                if ($type == "recap") {
                    $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $value->id_akun)->where("id_cabang", $value->id_cabang)->where("bulan", $month)->where("tahun", $year)->first();
                    $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                        ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                        ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                        ->where("jurnal_detail.id_akun", $value->id_akun)
                        ->where("jurnal_header.id_cabang", $value->id_cabang)
                        ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                        ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                        ->groupBy("jurnal_detail.id_akun")->first();
                    $saldo_debet = ($saldo) ? $saldo->saldo_debet : 0;
                    $saldo_kredit = ($saldo) ? $saldo->saldo_kredit : 0;
                    $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                    $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                    $saldo_awal = ($saldo_debet - $saldo_kredit) + ($debet - $kredit);
                    $saldo_akhir = $saldo_awal + $value->debet - $value->kredit;
                    $value["saldo_awal"] = $saldo_awal;
                    $value["saldo_akhir"] = $saldo_akhir;
                } else {
                    $saldo_balance = $saldo_balance + $value->debet - $value->kredit;
                    $result_detail[] = (object) [
                        "id_cabang" => $value->id_cabang,
                        "id_akun" => $value->id_akun,
                        "kode_akun" => $value->kode_akun,
                        "nama_akun" => $value->nama_akun,
                        "kode_jurnal" => $value->kode_jurnal,
                        "id_transaksi" => $value->id_transaksi,
                        "keterangan" => $value->keterangan,
                        "debet" => $value->debet,
                        "kredit" => $value->kredit,
                        "tanggal_jurnal" => $value->tanggal_jurnal,
                        "saldo_balance" => $saldo_balance,
                    ];
                }
            }
            $data = [
                "type" => $type,
                "start_date" => $start_date,
                "end_date" => $end_date,
                "cabang" => $nama_cabang,
                "data" => ($type == "recap") ? $result : $result_detail,
            ];
            // dd(count($data["data"]));
            if (count($data["data"]) > 0) {
                return Excel::download(new ReportGeneralLedgerExport($data), 'ReportGeneralLedger.xlsx');
            } else {
                return response()->json([
                    "result" => false,
                    "message" => "Tidak ada data",
                ]);
            }
        } catch (\Exception $e) {
            $message = "Failed to print general ledger for excel";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }
}
