<?php

namespace App\Http\Controllers;

use App\Exports\ReportGeneralLedgerExport;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\SaldoBalance;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use Illuminate\Support\Facades\DB;
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
            $getAkun = $request->cabang != '' ? Akun::where("kode_akun", $kode_akun)->where("id_cabang", $cabang)->first() : Akun::where("kode_akun", $kode_akun)->first();
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
            Log::info("start");
            // Init Data
            $id_cabang = $request->id_cabang;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $type = $request->type;
            $coa = $request->coa;
            $month = date("m", strtotime("$start_date"));
            $endMonth = date("m", strtotime("$end_date"));
            $year = date("Y", strtotime($start_date));
            $start_of_the_month = date("Y-m-01", strtotime($start_date));
            $saldo_date = date("Y-m-d", strtotime($start_date . " -1 day"));
            Log::info("end month ".$endMonth);

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
                ->join("cabang", "cabang.id_cabang", "jurnal_header.id_cabang")
                ->where("jurnal_header.void", "0")
                ->whereBetween("jurnal_header.tanggal_jurnal", [$start_date, $end_date])
                ->whereRaw("(COALESCE(jurnal_header.id_transaksi, '') NOT LIKE
                CASE
                    WHEN master_akun.tipe_akun = 1 THEN \"Closing %\"
                    ELSE \"--\"
                END 
                AND COALESCE(jurnal_header.id_transaksi, '') NOT LIKE
                CASE
                    WHEN master_akun.tipe_akun = 0 THEN
                        CASE
                            WHEN MONTH(\"".$end_date."\") = 12 THEN \"Closing 3%\"
                            ELSE \"--\"
                        END
                    ELSE \"--\"
                END
                OR jurnal_header.id_transaksi IS NULL)");
            if ($type == "recap") {
                $data_ledgers = $data_ledgers->selectRaw("jurnal_header.id_jurnal, master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, SUM(jurnal_detail.debet) as debet, SUM(jurnal_detail.credit) as kredit")
                ->leftJoin("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun");
                // ->groupBy("jurnal_detail.id_akun");
                if ($id_cabang != "all" && $id_cabang != "") {
                    $data_ledgers = $data_ledgers->groupBy("master_akun.kode_akun");
                }
                else {
                    $data_ledgers = $data_ledgers->groupBy("master_akun.kode_akun");
                }
            } else {
                $data_ledgers = $data_ledgers->selectRaw("jurnal_header.id_jurnal, jurnal_header.jenis_jurnal, master_akun.id_cabang, master_akun.id_akun, master_akun.kode_akun, master_akun.nama_akun, master_akun.posisi_debet, jurnal_header.kode_jurnal, jurnal_detail.keterangan, jurnal_detail.id_transaksi, jurnal_detail.debet as debet, jurnal_detail.credit as kredit, jurnal_header.tanggal_jurnal, cabang.nama_cabang as nama_cabang")
                ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun");
            }
            if ($id_cabang != "all" && $id_cabang != "") {
                $data_ledgers = $data_ledgers->where("jurnal_header.id_cabang", $id_cabang)->where("master_akun.id_cabang", $id_cabang);
            }
            if ($coa != "" && $coa != "all" && $coa != "recap" && $coa != "null") {
                if ($id_cabang != "all" && $id_cabang != "") {
                    $data_ledgers = $data_ledgers->where("jurnal_detail.id_akun", $coa);
                }
                else {
                    $kodeCoa = Akun::select("kode_akun")->where("id_akun", $coa)->first();
                    $data_ledgers = $data_ledgers->where("master_akun.kode_akun", $kodeCoa->kode_akun);
                }
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
            $saldo_balance = 0;
            // Log::info(json_encode($result));
            foreach ($result as $key => $value) {
                if ($type == "recap") {
                    if ($id_cabang != "all" && $id_cabang != "") {
                        $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $value->id_akun)->where("id_cabang", $id_cabang)->where("bulan", (int) $month)->where("tahun", (int) $year)->first();
                    }
                    else {
                        $kodeCoa = Akun::select("kode_akun")->where("id_akun", $value->id_akun)->first();
                        if (!$kodeCoa) {
                            $message = "Failed to get populate general ledger for view. Coa is null when using ALL branch. Check journal with ID: ".$value->id_jurnal;
                            Log::error($message);
                            return response()->json([
                                "result" => false,
                                "message" => $message,
                            ]);
                        }
                        $saldo = SaldoBalance::selectRaw("IFNULL(SUM(debet), 0) as saldo_debet, IFNULL(SUM(credit), 0) as saldo_kredit")->join("master_akun", "master_akun.id_akun", "saldo_balance.id_akun")->where("master_akun.kode_akun", $kodeCoa->kode_akun)->where("bulan", (int) $month)->where("tahun", (int) $year)->first();
                    }
                    
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
                    // Create Saldo Awal Record
                    if ($id_cabang != "all" && $id_cabang != "") {
                        if ($saldo_awal_current != $value->id_akun && $coa != "all") {
                            $saldo_awal_current = $value->id_akun;
                            if ($id_cabang != "all" && $id_cabang != "") {
                                $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $value->id_akun)->where("id_cabang", $id_cabang)->where("bulan", (int) $month)->where("tahun", (int) $year)->first();
                            }
                            else {
                                $kodeCoa = Akun::select("kode_akun")->where("id_akun", $value->id_akun)->first();
                                $saldo = SaldoBalance::selectRaw("IFNULL(SUM(debet), 0) as saldo_debet, IFNULL(SUM(credit), 0) as saldo_kredit")->join("master_akun", "master_akun.id_akun", "saldo_balance.id_akun")->where("master_akun.kode_akun", $kodeCoa->kode_akun)->where("bulan", (int) $month)->where("tahun", (int) $year)->first();
                            }
                            $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                            ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                            ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                            ->where("jurnal_detail.id_akun", $coa)
                            ->where("jurnal_header.id_cabang", $id_cabang)
                            ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                            ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                            ->groupBy("jurnal_detail.id_akun")->first();
                            $saldo_debet = ($saldo)?$saldo->saldo_debet:0;
                            $saldo_kredit = ($saldo)?$saldo->saldo_kredit:0;
                            $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                            $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                            $saldo_awal_debet = $saldo_debet + $debet;
                            $saldo_awal_kredit = $saldo_kredit + $kredit;
                            $saldo_balance = ($posisi != 0) ? $saldo_awal_debet - $saldo_awal_kredit : $saldo_awal_kredit - $saldo_awal_debet;
                            $result_detail[] = (object) [
                                "id_jurnal" => "",
                                "id_cabang" => $id_cabang,
                                "id_akun" => $value->id_akun,
                                "nama_cabang" => "-",
                                "kode_akun" => $value->kode_akun,
                                "nama_akun" => $value->nama_akun,
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
                    else {
                        if ($saldo_awal_current != $value->kode_akun && $coa != "all") {
                            $saldo_awal_current = $value->kode_akun;
                            if ($id_cabang != "all" && $id_cabang != "") {
                                $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $value->id_akun)->where("id_cabang", $id_cabang)->where("bulan", (int) $month)->where("tahun", (int) $year)->first();
                            }
                            else {
                                $kodeCoa = Akun::select("kode_akun")->where("id_akun", $value->id_akun)->first();
                                Log::info(json_encode($kodeCoa));
                                $saldo = SaldoBalance::selectRaw("IFNULL(SUM(debet), 0) as saldo_debet, IFNULL(SUM(credit), 0) as saldo_kredit")->join("master_akun", "master_akun.id_akun", "saldo_balance.id_akun")->where("master_akun.kode_akun", $kodeCoa->kode_akun)->where("bulan", (int) $month)->where("tahun", (int) $year)->first();
                            }
                            $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                            ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                            ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                            ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                            ->where("jurnal_header.tanggal_jurnal", "<", $start_date);
                            if ($id_cabang != "all" && $id_cabang != "") {
                                $data_saldo_ledgers = $data_saldo_ledgers->where("jurnal_header.id_cabang", $id_cabang)
                                ->where("jurnal_detail.id_akun", $coa)
                                ->groupBy("jurnal_detail.id_akun")->first();
                            }
                            else {
                                $data_saldo_ledgers = $data_saldo_ledgers->where("master_akun.kode_akun", $kodeCoa->kode_akun)
                                ->groupBy("master_akun.kode_akun")->first();
                            }
                            // Log::info(json_encode($data_saldo_ledgers));
                            $saldo_debet = ($saldo)?$saldo->saldo_debet:0;
                            $saldo_kredit = ($saldo)?$saldo->saldo_kredit:0;
                            $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                            $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                            $saldo_awal_debet = $saldo_debet + $debet;
                            $saldo_awal_kredit = $saldo_kredit + $kredit;
                            $saldo_balance = ($posisi != 0) ? $saldo_awal_debet - $saldo_awal_kredit : $saldo_awal_kredit - $saldo_awal_debet;
                            $result_detail[] = (object) [
                                "id_jurnal" => "",
                                "id_cabang" => $id_cabang,
                                "id_akun" => $value->id_akun,
                                "nama_cabang" => "-",
                                "kode_akun" => $value->kode_akun,
                                "nama_akun" => $value->nama_akun,
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
                    

                    $saldo_balance = ($posisi != 0) ? $saldo_balance + $value->debet - $value->kredit : $saldo_balance + $value->kredit - $value->debet;
                    $result_detail[] = (object) [
                        "id_jurnal" => $value->id_jurnal,
                        "id_cabang" => $value->id_cabang,
                        "id_akun" => $value->id_akun,
                        "nama_cabang" => $value->nama_cabang,
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

            if ($type != "recap" && count($result) == 0) {
                if ($id_cabang != "all" && $id_cabang != "") {
                    $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $coa)->where("id_cabang", $id_cabang)->where("bulan", (int) $month)->where("tahun", (int) $year)->first();
                }
                else {
                    $kodeCoa = Akun::select("kode_akun")->where("id_akun", $coa)->first();
                    $saldo = SaldoBalance::selectRaw("IFNULL(SUM(debet), 0) as saldo_debet, IFNULL(SUM(credit), 0) as saldo_kredit")->join("master_akun", "master_akun.id_akun", "saldo_balance.id_akun")->where("master_akun.kode_akun", $kodeCoa->kode_akun)->where("bulan", (int) $month)->where("tahun", (int) $year)->first();
                }
                
                $dataCoa = DB::table('master_akun')->where('id_akun', $coa)->first();
                if ($saldo) {
                    // $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                    //     ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                    //     ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                    //     ->where("jurnal_detail.id_akun", $coa)
                    //     ->where("jurnal_header.id_cabang", $id_cabang)
                    //     ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                    //     ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                    //     ->groupBy("jurnal_detail.id_akun")->first();
                    
                    $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                        ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                        ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                        ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                        ->where("jurnal_header.tanggal_jurnal", "<", $start_date);
                    if ($id_cabang != "all" && $id_cabang != "") {
                            $data_saldo_ledgers = $data_saldo_ledgers->where("jurnal_header.id_cabang", $id_cabang)
                            ->where("jurnal_detail.id_akun", $coa)
                            ->groupBy("jurnal_detail.id_akun")->first();
                    }
                    else {
                            $data_saldo_ledgers = $data_saldo_ledgers->where("master_akun.kode_akun", $kodeCoa->kode_akun)
                            ->groupBy("master_akun.kode_akun")->first();
                    }

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
                        "nama_cabang" => "-",
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
                    // $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                    //     ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                    //     ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                    //     ->where("jurnal_detail.id_akun", $coa)
                    //     ->where("jurnal_header.id_cabang", $id_cabang)
                    //     ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                    //     ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                    //     ->groupBy("jurnal_detail.id_akun")->first();

                    $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                        ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                        ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                        ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                        ->where("jurnal_header.tanggal_jurnal", "<", $start_date);
                    if ($id_cabang != "all" && $id_cabang != "") {
                            $data_saldo_ledgers = $data_saldo_ledgers->where("jurnal_header.id_cabang", $id_cabang)
                            ->where("jurnal_detail.id_akun", $coa)
                            ->groupBy("jurnal_detail.id_akun")->first();
                    }
                    else {
                            $data_saldo_ledgers = $data_saldo_ledgers->where("master_akun.kode_akun", $kodeCoa->kode_akun)
                            ->groupBy("master_akun.kode_akun")->first();
                    }
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
                        "nama_cabang" => "-",
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

            // Get saldo that have no result
            if ($type == "recap") {
                if ($id_cabang != "all" && $id_cabang != "") {
                    $allAkun = Akun::where("isshown", 1)->where("id_cabang", $id_cabang)->get()->toArray();
                }
                else {
                    $allAkun = Akun::where("isshown", 1)->groupBy("kode_akun")->get()->toArray();
                }

                $resultKodeAkun = array_column(json_decode(json_encode($result), true), 'kode_akun');

                $filteredAkunArray = array_filter($allAkun, function ($akun) use ($resultKodeAkun) {
                    return !in_array($akun["kode_akun"], $resultKodeAkun);
                });

                foreach ($filteredAkunArray as $key => $value) {

                    if ($id_cabang != "all" && $id_cabang != "") {
                        $saldo = SaldoBalance::selectRaw("IFNULL(debet, 0) as saldo_debet, IFNULL(credit, 0) as saldo_kredit")->where("id_akun", $value["id_akun"])->where("id_cabang", $id_cabang)->where("bulan", (int) $month)->where("tahun", (int) $year)->first();
                    }
                    else {
                        $kodeCoa = Akun::select("kode_akun")->where("id_akun", $value["id_akun"])->first();
                        $saldo = SaldoBalance::selectRaw("IFNULL(SUM(debet), 0) as saldo_debet, IFNULL(SUM(credit), 0) as saldo_kredit")->join("master_akun", "master_akun.id_akun", "saldo_balance.id_akun")->where("master_akun.kode_akun", $kodeCoa->kode_akun)->where("bulan", (int) $month)->where("tahun", (int) $year)->first();
                    }
                    
                    $data_saldo_ledgers = JurnalDetail::selectRaw("IFNULL(SUM(jurnal_detail.debet), 0) as debet, IFNULL(SUM(jurnal_detail.credit), 0) as kredit")
                        ->join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
                        ->join("master_akun", "master_akun.id_akun", "jurnal_detail.id_akun")
                        ->where("jurnal_detail.id_akun", $value["id_akun"])
                        ->where("jurnal_header.id_cabang", $value["id_cabang"])
                        ->where("jurnal_header.tanggal_jurnal", ">=", $start_of_the_month)
                        ->where("jurnal_header.tanggal_jurnal", "<", $start_date)
                        ->groupBy("jurnal_detail.id_akun")->first();
                    $saldo_debet = ($saldo) ? $saldo->saldo_debet : 0;
                    $saldo_kredit = ($saldo) ? $saldo->saldo_kredit : 0;
                    $debet = ($data_saldo_ledgers) ? $data_saldo_ledgers->debet : 0;
                    $kredit = ($data_saldo_ledgers) ? $data_saldo_ledgers->kredit : 0;
                    $saldo_awal = ($saldo_debet - $saldo_kredit) + ($debet - $kredit);
                    $saldo_akhir = $saldo_awal;
                    if ($saldo_akhir > 0) {
                        $result[] = (object) [
                            "id_jurnal" => "",
                            "id_cabang" => $value["id_cabang"],
                            "id_akun" => $value["id_akun"],
                            "kode_akun" => $value["kode_akun"],
                            "nama_akun" => $value["nama_akun"],
                            "debet" => 0,
                            "kredit" => 0,
                            "saldo_awal" => round($saldo_awal, 2),
                            "saldo_akhir" => round($saldo_akhir, 2)
                        ];
                    }
                }
            }

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

    public function populateRecap(Request $request) {
        try {
            Log::info("start recap");
            // dd($request->all());
            // Init Data
            $id_cabang = $request->id_cabang;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $type = $request->type;
            $coa = $request->coa;
            $month = date("m", strtotime("$start_date"));
            $endMonth = date("m", strtotime("$end_date"));
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

            // Start the queries
            $mainQuery = DB::table("master_akun AS ma")
                ->select(
                    'jl.id_jurnal',
                    'jl.id_akun',
                    'ma.kode_akun',
                    'ma.nama_akun',
                    'ma.posisi_debet',
                    DB::raw('IFNULL(SUM(jl.debet), 0) AS debet'),
                    DB::raw('IFNULL(SUM(jl.credit), 0) AS credit'),
                    DB::raw('IFNULL(sb.debet, 0) AS saldo_debet'),
                    DB::raw('IFNULL(sb.credit, 0) AS saldo_credit'),
                    DB::raw('0 AS trx_debet'),
                    DB::raw('0 AS trx_credit')
                )->leftJoin('saldo_balance as sb', function ($join) use ($month, $year) {
                        $join->on('sb.id_akun', '=', 'ma.id_akun')
                            ->where('sb.bulan', '=', $month)
                            ->where('sb.tahun', '=', $year);
                    }
                )->leftJoin(
                    DB::raw('
                        (SELECT 
                            jd.id_jurnal,
                            jd.id_akun,
                            jd.debet,
                            jd.credit
                        FROM 
                            jurnal_detail jd
                            JOIN jurnal_header jh ON jh.id_jurnal = jd.id_jurnal
                            JOIN master_akun mal ON mal.id_akun = jd.id_akun
                        WHERE
                            jh.tanggal_jurnal >= "'.$start_date.'" 
                            AND jh.tanggal_jurnal <= "'.$end_date.'"
                            AND jh.void = 0
                            AND (
                                CASE 
                                    WHEN MONTH("'.$start_date.'") = 12 THEN
                                        COALESCE(jh.id_transaksi, "") NOT LIKE
                                            CASE WHEN mal.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                        AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                            CASE WHEN mal.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                        AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                            CASE WHEN mal.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                                    ELSE 
                                        COALESCE(jh.id_transaksi, "") NOT LIKE
                                            CASE WHEN mal.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                        AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                            CASE WHEN mal.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                END
                            )
                    ) jl'), 'jl.id_akun', '=', 'ma.id_akun'
                )->groupBy('ma.kode_akun');
            
            $secondQuery = DB::table("master_akun AS ma")
                ->select(
                    'jd.id_jurnal',
                    'jd.id_akun',
                    'ma.kode_akun',
                    'ma.nama_akun',
                    'ma.posisi_debet',
                    DB::raw('0 AS debet'),
                    DB::raw('0 AS credit'),
                    DB::raw('0 AS saldo_debet'),
                    DB::raw('0 AS saldo_credit'),
                    DB::raw('IFNULL(SUM(jd.debet), 0) AS trx_debet'),
                    DB::raw('IFNULL(SUM(jd.credit), 0) AS trx_credit')
                )->leftJoin('jurnal_detail AS jd', 'jd.id_akun', '=', 'ma.id_akun')
                ->leftJoin('jurnal_header AS jh', 'jh.id_jurnal', '=', 'jd.id_jurnal')
                ->where('jh.tanggal_jurnal', '>=', $start_of_the_month)
                ->where('jh.tanggal_jurnal', '<', $start_date)
                ->where('jh.void', '=', 0)
                ->whereRaw(
                    'CASE 
                        WHEN MONTH("'.$start_date.'") = 12 THEN
                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                        ELSE 
                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                    END'
                )->groupBy('ma.kode_akun');
            
            if ($id_cabang != "all" && $id_cabang != "") {
                $mainQuery = $mainQuery->where('ma.id_cabang', '=', $id_cabang);
                $secondQuery = $secondQuery->where('ma.id_cabang', '=', $id_cabang);
            }
            
            $combinedQuery = $secondQuery->union($mainQuery);

            $results = DB::query()->fromSub($combinedQuery, "fQ")
            ->select(
                'id_akun',
                'kode_akun',
                'nama_akun',
                'posisi_debet',
                DB::raw('SUM(debet) AS debet'),
                DB::raw('SUM(credit) AS credit'),
                DB::raw('SUM(saldo_debet) AS saldo_debet'),
                DB::raw('SUM(saldo_credit) AS saldo_credit'),
                DB::raw('SUM(trx_debet) AS trx_debet'),
                DB::raw('SUM(trx_credit) AS trx_credit'),
                DB::raw('CASE 
                    WHEN IFNULL(posisi_debet, 1) != 0 THEN
                        ROUND((SUM(saldo_debet) - SUM(saldo_credit) + SUM(trx_debet) - SUM(trx_credit)), 2)
                    ELSE
                        ROUND((SUM(saldo_credit) - SUM(saldo_debet) + SUM(trx_credit) - SUM(trx_debet)), 2)
                END AS saldo_start'),
                DB::raw('CASE 
                    WHEN IFNULL(posisi_debet, 1) != 0 THEN
                        ROUND((SUM(saldo_debet) - SUM(saldo_credit) + SUM(trx_debet) - SUM(trx_credit) + SUM(debet) - SUM(credit)), 2)
                    ELSE
                        ROUND((SUM(saldo_credit) - SUM(saldo_debet) + SUM(trx_credit) - SUM(trx_debet) + SUM(credit) - SUM(debet)), 2)
                END AS saldo_balance')
            )->groupBy("kode_akun")
            ->havingRaw("debet <> 0 OR credit <> 0 OR saldo_debet <> 0 OR saldo_credit <> 0 OR saldo_balance <> 0");
            
            if (isset($keyword)) {
                $results->where(function ($query) use ($keyword, $type) {
                    $query->orWhere("kode_akun", "LIKE", "%$keyword%")
                        ->orWhere("nama_akun", "LIKE", "%$keyword%");
                });
            }

            $filtered_data = $results->get();
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
                        $results->orderBy($column, $directon);
                    }
                }
            } 
            else {
                $results->orderBy("kode_akun", "ASC");
            }

            // pagination
            if ($current_page) {
                $page = $current_page;
                $limit_data = count($filtered_data);
                if ($limit) {
                    $limit_data = $limit;
                }
                $offset = ($page - 1) * $limit_data;
                if ($offset < 0) {
                    $offset = 0;
                }
                if ($limit != -1) {
                    $results->skip($offset)->take($limit_data);
                }
            }

            // dd(count($groupByQuery->get()));
            $datas = $results->get();
            $table['draw'] = $draw;
            $table['recordsTotal'] = count($datas);
            $table['recordsFiltered'] = count($filtered_data);
            $table['data'] = $datas;
            Log::info("finish recap");
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

    public function populateDetail(Request $request) {
        try {
            Log::info("start detail");
            // dd($request->all());
            // Init Data
            $id_cabang = $request->id_cabang;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $type = $request->type;
            $coa = $request->coa;
            $month = date("m", strtotime("$start_date"));
            $endMonth = date("m", strtotime("$end_date"));
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
            
            // Start queries
            $mainQuery = DB::table("master_akun AS ma")
                ->select(
                    DB::raw('"'.$saldo_date.'" AS tanggal_jurnal'),
                    DB::raw('"-" AS id_jurnal'),
                    DB::raw('"-" AS kode_jurnal'),
                    DB::raw('"-" AS jenis_jurnal'),
                    DB::raw('"-" AS id_transaksi'),
                    DB::raw('"-" AS id_cabang'),
                    DB::raw('"-" AS nama_cabang'),
                    'sb.id_akun',
                    'ma.kode_akun',
                    'ma.nama_akun',
                    'ma.posisi_debet',
                    DB::raw('"Saldo Awal" AS keterangan'),
                    DB::raw('IFNULL(SUM(sb.debet), 0) AS debet'),
                    DB::raw('IFNULL(SUM(sb.credit), 0) AS credit'),
                    DB::raw('IFNULL(SUM(trx.trx_debet), 0) AS trx_debet'),
                    DB::raw('IFNULL(SUM(trx.trx_credit), 0) AS trx_credit')
                )->leftJoin('saldo_balance as sb', function ($join) use ($month, $year) {
                    $join->on('sb.id_akun', '=', 'ma.id_akun')
                        ->where('sb.bulan', '=', $month)
                        ->where('sb.tahun', '=', $year);
                    }
                )->leftJoin(
                    DB::raw('
                        (SELECT
                            jh.tanggal_jurnal, 
                            jd.id_jurnal, 
                            jh.kode_jurnal,
                            jh.id_cabang,
                            ma.id_akun, 
                            ma.kode_akun, 
                            IFNULL(SUM(jd.debet), 0) AS trx_debet, 
                            IFNULL(SUM(jd.credit), 0) AS trx_credit
                        FROM
                            jurnal_detail jd
                        JOIN jurnal_header jh ON jh.id_jurnal = jd.id_jurnal
                        JOIN master_akun ma ON ma.id_akun = jd.id_akun
                        JOIN cabang cb ON cb.id_cabang = jh.id_cabang
                        WHERE
                            jh.tanggal_jurnal >= "'.$start_of_the_month.'"
                            AND jh.tanggal_jurnal < "'.$start_date.'"
                            AND jh.void = 0
                            AND (
                                    CASE 
                                        WHEN MONTH("'.$start_date.'") = 12 THEN
                                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                                CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                                        ELSE 
                                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                        END
                            )
                        GROUP BY
                            ma.kode_akun
                    ) trx'), 'trx.id_akun', '=', 'ma.id_akun'
                )->groupBy('ma.kode_akun');

            $secondQuery = DB::table("jurnal_detail AS jd")
                ->select(
                    'jh.tanggal_jurnal', 
                    'jd.id_jurnal', 
                    'jh.kode_jurnal',
                    'jh.jenis_jurnal',
                    'jh.id_transaksi',
                    'jh.id_cabang', 
                    'cb.nama_cabang',
                    'jd.id_akun', 
                    'ma.kode_akun', 
                    'ma.nama_akun',
                    'ma.posisi_debet', 
                    'jd.keterangan',
                    'jd.debet', 
                    'jd.credit',
                    DB::raw('0 AS trx_debet'),
                    DB::raw('0 AS trx_credit')
                )->join('jurnal_header AS jh', 'jh.id_jurnal', '=', 'jd.id_jurnal')
                ->join('master_akun AS ma', 'ma.id_akun', '=', 'jd.id_akun')
                ->join('cabang AS cb', 'cb.id_cabang', '=', 'jh.id_cabang')
                ->where('jh.tanggal_jurnal', '>=', $start_date)
                ->where('jh.tanggal_jurnal', '<=', $end_date)
                ->where('jh.void', '=', 0)
                ->whereRaw(
                    '(CASE 
                        WHEN MONTH("'.$start_date.'") = 12 THEN
                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                        ELSE 
                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                    END)'
                );
            
            if ($id_cabang != "all" && $id_cabang != "") {
                $mainQuery = $mainQuery->where('ma.id_cabang', '=', $id_cabang);
                $secondQuery = $secondQuery->where('jh.id_cabang', '=', $id_cabang);
            }

            if ($coa != "all" && $coa != "") {
                if ($id_cabang != "all" && $id_cabang != "") {
                    $mainQuery = $mainQuery->where('ma.id_akun', '=', $coa);
                    $secondQuery = $secondQuery->where('ma.id_akun', '=', $coa);
                }
                else {
                    $checkAkun = Akun::where('id_akun', $coa)->first();
                    $kodeAkun = ($checkAkun)?$checkAkun->kode_akun:"--";
                    $mainQuery = $mainQuery->where('ma.kode_akun', '=', $kodeAkun);
                    $secondQuery = $secondQuery->where('ma.kode_akun', '=', $kodeAkun);
                }
            }

            $combinedQuery = $secondQuery->union($mainQuery);
            DB::statement("SET @running_balance = 0");
            $results = DB::query()
            ->fromSub($combinedQuery, "fQ")
            ->select(
                'tanggal_jurnal',
                'id_jurnal',
                'kode_jurnal',
                'jenis_jurnal',
                'id_transaksi',
                'id_cabang',
                'nama_cabang',
                'id_akun',
                'kode_akun',
                'nama_akun',
                'keterangan',
                DB::raw('IFNULL(debet, 0) + IFNULL(trx_debet, 0) AS debet'),
	            DB::raw('IFNULL(credit, 0) + IFNULL(trx_credit, 0) AS credit'),
                'trx_debet',
                'trx_credit',
                'posisi_debet'
            );

            // if ($coa != "all" && $coa != "") {
            //     if ($id_cabang != "all" && $id_cabang != "") {
            //         $results = $results->where('id_akun', '=', $coa);
            //     }
            //     else {
            //         $checkAkun = Akun::where('id_akun', $coa)->first();
            //         $kodeAkun = ($checkAkun)?$checkAkun->kode_akun:"--";
            //         $results = $results->where('kode_akun', '=', $kodeAkun);
            //     }
            // }

            if (isset($keyword)) {
                $results->where(function ($query) use ($keyword, $type) {
                    $query->orWhere("kode_akun", "LIKE", "%$keyword%")
                        ->orWhere("nama_akun", "LIKE", "%$keyword%")
                        ->orWhere("keterangan", "LIKE", "%$keyword%")
                        ->orWhere("id_transaksi", "LIKE", "%$keyword%")
                        ->orWhere("kode_jurnal", "LIKE", "%$keyword%");
                });
            }

            $filtered_data = $results->get();
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
                        $results->orderBy($column, $directon);
                    }
                }
            } 
            else {
                $results->orderBy('kode_akun', 'ASC')
                ->orderBy('tanggal_jurnal', 'ASC');
            }

            // pagination
            if ($current_page) {
                $page = $current_page;
                $limit_data = count($filtered_data);
                if ($limit) {
                    $limit_data = $limit;
                }
                $offset = ($page - 1) * $limit_data;
                if ($offset < 0) {
                    $offset = 0;
                }
                if ($limit != -1) {
                    $results->skip($offset)->take($limit_data);
                }
            }

            // dd(count($groupByQuery->get()));
            $datas = $results->get();

            // Initialize running balance
            $runningBalance = 0;
            $runningCoa = '';
            // Iterate over the results to accumulate the running balance
            foreach ($datas as $key => $row) {
                if ($runningCoa != $row->kode_akun) {
                    $runningCoa = $row->kode_akun;
                    if ($row->posisi_debet ?? 1 != 0) {
                        $runningBalance = $row->debet - $row->credit;
                    } else {
                        $runningBalance = $row->credit - $row->debet;
                    }
                } 
                else {
                    if ($row->posisi_debet ?? 1 != 0) {
                        $runningBalance = $runningBalance + $row->debet - $row->credit;
                    }
                    else {
                        $runningBalance = $runningBalance + $row->credit - $row->debet;
                    }
                }
                // Add saldo_baru to the current row
                $datas[$key]->saldo_balance = $runningBalance;
            }

            $table['draw'] = $draw;
            $table['recordsTotal'] = count($datas);
            $table['recordsFiltered'] = count($filtered_data);
            $table['data'] = $datas;
            // Log::info(json_encode($datas));
            Log::info("finish detail");
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
    
    public function populateStaticRecap(Request $request){
        // Init Data
        $id_cabang = $request->id_cabang;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $type = $request->type;
        $coa = $request->coa;
        $month = date("m", strtotime("$start_date"));
        $endMonth = date("m", strtotime("$end_date"));
        $year = date("Y", strtotime($start_date));
        $start_of_the_month = date("Y-m-01", strtotime($start_date));
        $saldo_date = date("Y-m-d", strtotime($start_date . " -1 day"));

        try {
            // Start the queries
            $mainQuery = DB::table("master_akun AS ma")
                ->select(
                    'jl.id_jurnal',
                    'jl.id_akun',
                    'ma.kode_akun',
                    'ma.nama_akun',
                    'ma.posisi_debet',
                    DB::raw('IFNULL(SUM(jl.debet), 0) AS debet'),
                    DB::raw('IFNULL(SUM(jl.credit), 0) AS credit'),
                    DB::raw('IFNULL(sb.debet, 0) AS saldo_debet'),
                    DB::raw('IFNULL(sb.credit, 0) AS saldo_credit'),
                    DB::raw('0 AS trx_debet'),
                    DB::raw('0 AS trx_credit')
                )->leftJoin('saldo_balance as sb', function ($join) use ($month, $year) {
                        $join->on('sb.id_akun', '=', 'ma.id_akun')
                            ->where('sb.bulan', '=', $month)
                            ->where('sb.tahun', '=', $year);
                    }
                )->leftJoin(
                    DB::raw('
                        (SELECT 
                            jd.id_jurnal,
                            jd.id_akun,
                            jd.debet,
                            jd.credit
                        FROM 
                            jurnal_detail jd
                            JOIN jurnal_header jh ON jh.id_jurnal = jd.id_jurnal
                            JOIN master_akun mal ON mal.id_akun = jd.id_akun
                        WHERE
                            jh.tanggal_jurnal >= "'.$start_date.'" 
                            AND jh.tanggal_jurnal <= "'.$end_date.'"
                            AND jh.void = 0
                            AND (
                                CASE 
                                    WHEN MONTH("'.$start_date.'") = 12 THEN
                                        COALESCE(jh.id_transaksi, "") NOT LIKE
                                            CASE WHEN mal.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                        AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                            CASE WHEN mal.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                        AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                            CASE WHEN mal.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                                    ELSE 
                                        COALESCE(jh.id_transaksi, "") NOT LIKE
                                            CASE WHEN mal.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                        AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                            CASE WHEN mal.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                END
                            )
                    ) jl'), 'jl.id_akun', '=', 'ma.id_akun'
                )->groupBy('ma.kode_akun');
        
            $secondQuery = DB::table("master_akun AS ma")
                ->select(
                    'jd.id_jurnal',
                    'jd.id_akun',
                    'ma.kode_akun',
                    'ma.nama_akun',
                    'ma.posisi_debet',
                    DB::raw('0 AS debet'),
                    DB::raw('0 AS credit'),
                    DB::raw('0 AS saldo_debet'),
                    DB::raw('0 AS saldo_credit'),
                    DB::raw('IFNULL(SUM(jd.debet), 0) AS trx_debet'),
                    DB::raw('IFNULL(SUM(jd.credit), 0) AS trx_credit')
                )->leftJoin('jurnal_detail AS jd', 'jd.id_akun', '=', 'ma.id_akun')
                ->leftJoin('jurnal_header AS jh', 'jh.id_jurnal', '=', 'jd.id_jurnal')
                ->where('jh.tanggal_jurnal', '>=', $start_of_the_month)
                ->where('jh.tanggal_jurnal', '<', $start_date)
                ->where('jh.void', '=', 0)
                ->whereRaw(
                    'CASE 
                        WHEN MONTH("'.$start_date.'") = 12 THEN
                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                        ELSE 
                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                    END'
                )->groupBy('ma.kode_akun');
            
            if ($id_cabang != "all" && $id_cabang != "") {
                $mainQuery = $mainQuery->where('ma.id_cabang', '=', $id_cabang);
                $secondQuery = $secondQuery->where('ma.id_cabang', '=', $id_cabang);
            }
            
            $combinedQuery = $secondQuery->union($mainQuery);
    
            $results = DB::query()->fromSub($combinedQuery, "fQ")
            ->select(
                'id_akun',
                'kode_akun',
                'nama_akun',
                'posisi_debet',
                DB::raw('SUM(debet) AS debet'),
                DB::raw('SUM(credit) AS credit'),
                DB::raw('SUM(saldo_debet) AS saldo_debet'),
                DB::raw('SUM(saldo_credit) AS saldo_credit'),
                DB::raw('SUM(trx_debet) AS trx_debet'),
                DB::raw('SUM(trx_credit) AS trx_credit'),
                DB::raw('CASE 
                    WHEN IFNULL(posisi_debet, 1) != 0 THEN
                        SUM(saldo_debet) - SUM(saldo_credit) + SUM(trx_debet) - SUM(trx_credit)
                    ELSE
                        SUM(saldo_credit) - SUM(saldo_debet) + SUM(trx_credit) - SUM(trx_debet)
                END AS saldo_start'),
                DB::raw('CASE 
                    WHEN IFNULL(posisi_debet, 1) != 0 THEN
                        SUM(saldo_debet) - SUM(saldo_credit) + SUM(trx_debet) - SUM(trx_credit) + SUM(debet) - SUM(credit)
                    ELSE
                        SUM(saldo_credit) - SUM(saldo_debet) + SUM(trx_credit) - SUM(trx_debet) + SUM(credit) - SUM(debet)
                END AS saldo_balance')
            )->groupBy("kode_akun")
            ->havingRaw("debet <> 0 OR credit <> 0 OR saldo_debet <> 0 OR saldo_credit <> 0 OR saldo_balance <> 0");
    
            $data = Akun::selectRaw('
                CASE WHEN header1 IS NULL OR header1 = "" THEN "" ELSE header1 END as new_header1,
                CASE WHEN header2 IS NULL OR header2 = "" THEN "" ELSE header2 END as new_header2,
                CASE WHEN header3 IS NULL OR header3 = "" THEN "" ELSE header3 END as new_header3,
                master_akun.kode_akun,
                master_akun.nama_akun,
                master_akun.id_akun,
                saldo_start as saldo_awal,
                debet,
                credit,
                saldo_balance as saldo_akhir,
                master_akun.posisi_debet 
            ')
            ->join(DB::raw('(' . $results->toSql() . ') as jurnal'), function ($join) use ($results) {
                $join->on('master_akun.kode_akun', '=', 'jurnal.kode_akun');
            })
            ->addBinding($results->getBindings(), 'select')
            ->groupBy('new_header1', 'new_header2', 'new_header3', 'master_akun.kode_akun')
            ->get();
    
            $total = [];
    
            $total['grand_total'] = 0;
    
            $detail_data = [
                'transaction_data' => $data,
                'period' => $start_date . '-' . $end_date,
                'start' => $start_date,
                'end' => $end_date,
                'id_cabang' => $id_cabang,
                'total' => $total,
            ];
    
            $data = $this->getMapStaticRecap($detail_data);
    
            $data = [
                'data' => (Object) $data['map'],
                'total' => $data['total'],
            ];
    
            // dd($data);
    
            return response()->json([
                "result" => true,
                "data" => $data,
            ]);
        } catch (\Exception $e) {
            $message = "Failed to get populate profit and loss for view";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    public function getMapStaticRecap($detail_data){        
        $data = $detail_data['transaction_data'];
        if(isset($detail_data['start'])){
            $start_date = date('Y-m-d', strtotime($detail_data['start']));
            $end_date = date('Y-m-d', strtotime($detail_data['end']));
        }else{
            $start_date = date('Y-m-d', strtotime($detail_data['period'] . '-1'));
            $end_date = date('Y-m-t', strtotime($detail_data['period'] . '-1'));
        }
        $id_cabang = $detail_data['id_cabang'];
        $total = $detail_data['total'];

        // Initialize a hash map to keep track of parent-child relationships
        $map = [];

        // Loop through the data and build the hierarchy
        foreach ($data as $item) {
            $newHeader1 = $item['new_header1'];
            $newHeader2 = $item['new_header2'];
            $newHeader3 = $item['new_header3'];
            $newHeader4 = $item['kode_akun'] . '.' . $item['nama_akun'];
            $posisi_debet = $item['posisi_debet'];
            $saldo_awal = $item['saldo_awal'];
            $debet = $item['debet'];
            $credit = $item['credit'];
            $saldo_akhir = $item['saldo_akhir'];

            if ($newHeader1 == "") {
                $newHeader1 = "00. Header1";
            }

            if ($newHeader2 == "") {
                $newHeader2 = "00. Header2";
            }

            if ($newHeader3 == "") {
                $newHeader3 = "00. Header3";
            }

            // Add new_header1 as a parent
            if (!isset($map[$newHeader1])) {
                $map[$newHeader1] = [
                    'header' => $newHeader1,
                    'saldo_awal' => 0,
                    'debet' => 0,
                    'credit' => 0,
                    'saldo_akhir' => 0,
                    'children' => [],
                ];
            }

            // Add new_header2 as a child of new_header1
            if (!empty($newHeader2)) {
                if (!isset($map[$newHeader1]['children'][$newHeader2])) {
                    $map[$newHeader1]['children'][$newHeader2] = [
                        'header' => $newHeader2,
                        'saldo_awal' => 0,
                        'debet' => 0,
                        'credit' => 0,
                        'saldo_akhir' => 0,
                        'children' => [],
                    ];
                }

                // Add new_header3 as a child of new_header2
                if (!empty($newHeader3)) {
                    if (!isset($map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3])) {
                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3] = [
                            'header' => $newHeader3,
                            'saldo_awal' => 0,
                            'debet' => 0,
                            'credit' => 0,
                            'saldo_akhir' => 0,
                            'children' => [],
                        ];
                    }

                    // Add new_header4 as a child of new_header3
                    if (!empty($newHeader4)) {
                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['children'][] = [
                            'header' => $newHeader4,
                            'akun' => $item['id_akun'],
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                            'id_cabang' => $id_cabang,
                            'saldo_awal' => $saldo_awal,
                            'debet' => $debet,
                            'credit' => $credit,
                            'saldo_akhir' => $saldo_akhir,
                        ];

                        $map[$newHeader1]['children'][$newHeader2]['children'][$newHeader3]['saldo_akhir'] += $saldo_akhir;
                    }

                    $map[$newHeader1]['children'][$newHeader2]['saldo_akhir'] += $saldo_akhir;
                } else {
                    // Add new_header3 as a child of new_header1
                    if (!empty($newHeader3)) {
                        $map[$newHeader1]['children'][] = [
                            'header' => $newHeader3,
                            'saldo_awal' => $saldo_awal,
                            'debet' => $debet,
                            'credit' => $credit,
                            'saldo_akhir' => $saldo_akhir,
                        ];
                        $map[$newHeader1]['children'][$newHeader2]['saldo_akhir'] += $saldo_akhir;
                    }
                }
                $map[$newHeader1]['saldo_akhir'] += $saldo_akhir;
                $total['grand_total'] += $saldo_akhir;
            } else {
                // maybe never execute
                // Add new_header4 as a child of new_header1
                if (!empty($newHeader4)) {
                    $map[$newHeader1]['children'][] = [
                        'header' => $newHeader4,
                        'saldo_awal' => $saldo_awal,
                        'debet' => $debet,
                        'credit' => $credit,
                        'saldo_akhir' => $saldo_akhir,
                    ];
                    $map[$newHeader1]['saldo_akhir'] += $saldo_akhir;
                    $total['grand_total'] += $saldo_akhir;
                }
            }
        }

        // Convert the hash map to an array
        $data = ['map' => array_values($map), 'total' => $total];

        return $data;
    }

    public function populateStaticDetail(Request $request){
        // Init Data
        $id_cabang = $request->id_cabang;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $type = $request->type;
        $coa = $request->coa;
        $month = date("m", strtotime("$start_date"));
        $endMonth = date("m", strtotime("$end_date"));
        $year = date("Y", strtotime($start_date));
        $start_of_the_month = date("Y-m-01", strtotime($start_date));
        $saldo_date = date("Y-m-d", strtotime($start_date . " -1 day"));

        try {
            $mainQuery = DB::table("master_akun AS ma")
                ->select(
                    DB::raw('"'.$saldo_date.'" AS tanggal_jurnal'),
                    DB::raw('"-" AS id_jurnal'),
                    DB::raw('"-" AS kode_jurnal'),
                    DB::raw('"-" AS jenis_jurnal'),
                    DB::raw('"-" AS id_transaksi'),
                    DB::raw('"-" AS id_cabang'),
                    DB::raw('"-" AS nama_cabang'),
                    'sb.id_akun',
                    'ma.kode_akun',
                    'ma.nama_akun',
                    'ma.posisi_debet',
                    DB::raw('"Saldo Awal" AS keterangan'),
                    DB::raw('IFNULL(SUM(sb.debet), 0) AS debet'),
                    DB::raw('IFNULL(SUM(sb.credit), 0) AS credit'),
                    DB::raw('IFNULL(SUM(trx.trx_debet), 0) AS trx_debet'),
                    DB::raw('IFNULL(SUM(trx.trx_credit), 0) AS trx_credit')
                )->leftJoin('saldo_balance as sb', function ($join) use ($month, $year) {
                    $join->on('sb.id_akun', '=', 'ma.id_akun')
                        ->where('sb.bulan', '=', $month)
                        ->where('sb.tahun', '=', $year);
                    }
                )->leftJoin(
                    DB::raw('
                        (SELECT
                            jh.tanggal_jurnal, 
                            jd.id_jurnal, 
                            jh.kode_jurnal,
                            jh.id_cabang,
                            ma.id_akun, 
                            ma.kode_akun, 
                            IFNULL(SUM(jd.debet), 0) AS trx_debet, 
                            IFNULL(SUM(jd.credit), 0) AS trx_credit
                        FROM
                            jurnal_detail jd
                        JOIN jurnal_header jh ON jh.id_jurnal = jd.id_jurnal
                        JOIN master_akun ma ON ma.id_akun = jd.id_akun
                        JOIN cabang cb ON cb.id_cabang = jh.id_cabang
                        WHERE
                            jh.tanggal_jurnal >= "'.$start_of_the_month.'"
                            AND jh.tanggal_jurnal < "'.$start_date.'"
                            AND jh.void = 0
                            AND (
                                    CASE 
                                        WHEN MONTH("'.$start_date.'") = 12 THEN
                                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                                CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                                        ELSE 
                                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                        END
                            )
                        GROUP BY
                            ma.kode_akun
                    ) trx'), 'trx.id_akun', '=', 'ma.id_akun'
                )->groupBy('ma.kode_akun');

            $secondQuery = DB::table("jurnal_detail AS jd")
                ->select(
                    'jh.tanggal_jurnal', 
                    'jd.id_jurnal', 
                    'jh.kode_jurnal',
                    'jh.jenis_jurnal',
                    'jh.id_transaksi',
                    'jh.id_cabang', 
                    'cb.nama_cabang',
                    'jd.id_akun', 
                    'ma.kode_akun', 
                    'ma.nama_akun',
                    'ma.posisi_debet', 
                    'jd.keterangan',
                    'jd.debet', 
                    'jd.credit',
                    DB::raw('0 AS trx_debet'),
                    DB::raw('0 AS trx_credit')
                )->join('jurnal_header AS jh', 'jh.id_jurnal', '=', 'jd.id_jurnal')
                ->join('master_akun AS ma', 'ma.id_akun', '=', 'jd.id_akun')
                ->join('cabang AS cb', 'cb.id_cabang', '=', 'jh.id_cabang')
                ->where('jh.tanggal_jurnal', '>=', $start_date)
                ->where('jh.tanggal_jurnal', '<=', $end_date)
                ->where('jh.void', '=', 0)
                ->whereRaw(
                    '(CASE 
                        WHEN MONTH("'.$start_date.'") = 12 THEN
                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                        ELSE 
                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                    END)'
                );
            
            if ($id_cabang != "all" && $id_cabang != "") {
                $mainQuery = $mainQuery->where('ma.id_cabang', '=', $id_cabang);
                $secondQuery = $secondQuery->where('jh.id_cabang', '=', $id_cabang);
            }

            if ($coa != "all" && $coa != "") {
                if ($id_cabang != "all" && $id_cabang != "") {
                    $mainQuery = $mainQuery->where('ma.id_akun', '=', $coa);
                    $secondQuery = $secondQuery->where('ma.id_akun', '=', $coa);
                }
                else {
                    $checkAkun = Akun::where('id_akun', $coa)->first();
                    $kodeAkun = ($checkAkun)?$checkAkun->kode_akun:"--";
                    $mainQuery = $mainQuery->where('ma.kode_akun', '=', $kodeAkun);
                    $secondQuery = $secondQuery->where('ma.kode_akun', '=', $kodeAkun);
                }
            }

            $combinedQuery = $secondQuery->union($mainQuery);
            DB::statement("SET @running_balance = 0");
            $results = DB::query()
            ->fromSub($combinedQuery, "fQ")
            ->select(
                'tanggal_jurnal',
                'jd.id_jurnal',
                'kode_jurnal',
                'jenis_jurnal',
                'jd.id_transaksi',
                'fQ.id_cabang',
                'nama_cabang',
                'jd.id_akun',
                'ma.kode_akun',
                'ma.nama_akun',
                'jd.keterangan',
                DB::raw('IFNULL(jd.debet, 0) + IFNULL(trx_debet, 0) AS debet'),
                DB::raw('IFNULL(jd.credit, 0) + IFNULL(trx_credit, 0) AS credit'),
                'trx_debet',
                'trx_credit',
                'ma.posisi_debet'
            )
            ->join('jurnal_detail AS jd', 'fQ.id_jurnal', '=', 'jd.id_jurnal')
            ->join('master_akun AS ma', 'jd.id_akun', '=', 'ma.id_akun')
            ->orderBy('tanggal_jurnal', 'ASC');

            $datas = $results->get();

            // Log::debug(json_encode($datas));
    
            // dd($datas);
    
            return response()->json([
                "result" => true,
                "data" => $datas,
            ]);
        } catch (\Exception $e) {
            $message = "Failed to get populate profit and loss for view";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    public function exportPdf(Request $request) {
        try {
            Log::info("start export pdf");
            // dd($request->all());
            // Init Data
            $type = $request->type;
            $id_cabang = $request->id_cabang;
            $cabang = Cabang::find($id_cabang);
            $nama_cabang = ($cabang) ? $cabang->nama_cabang : null;
            $coa = $request->coa;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $month = date("m", strtotime("$start_date"));
            $year = date("Y", strtotime($start_date));
            $start_of_the_month = date("Y-m-01", strtotime($start_date));
            $saldo_date = date("Y-m-d", strtotime($start_date . " -1 day"));

            if ($type == "recap") {
                // Start the queries
                $mainQuery = DB::table("master_akun AS ma")
                    ->select(
                        'jl.id_jurnal',
                        'jl.id_akun',
                        'ma.kode_akun',
                        'ma.nama_akun',
                        'ma.posisi_debet',
                        DB::raw('IFNULL(SUM(jl.debet), 0) AS debet'),
                        DB::raw('IFNULL(SUM(jl.credit), 0) AS credit'),
                        DB::raw('IFNULL(sb.debet, 0) AS saldo_debet'),
                        DB::raw('IFNULL(sb.credit, 0) AS saldo_credit'),
                        DB::raw('0 AS trx_debet'),
                        DB::raw('0 AS trx_credit')
                    )->leftJoin('saldo_balance as sb', function ($join) use ($month, $year) {
                            $join->on('sb.id_akun', '=', 'ma.id_akun')
                                ->where('sb.bulan', '=', $month)
                                ->where('sb.tahun', '=', $year);
                        }
                    )->leftJoin(
                        DB::raw('
                            (SELECT 
                                jd.id_jurnal,
                                jd.id_akun,
                                jd.debet,
                                jd.credit
                            FROM 
                                jurnal_detail jd
                                JOIN jurnal_header jh ON jh.id_jurnal = jd.id_jurnal
                                JOIN master_akun mal ON mal.id_akun = jd.id_akun
                            WHERE
                                jh.tanggal_jurnal >= "'.$start_date.'" 
                                AND jh.tanggal_jurnal <= "'.$end_date.'"
                                AND jh.void = 0
                                AND (
                                    CASE 
                                        WHEN MONTH("'.$start_date.'") = 12 THEN
                                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN mal.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN mal.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                                CASE WHEN mal.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                                        ELSE 
                                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN mal.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN mal.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                    END
                                )
                        ) jl'), 'jl.id_akun', '=', 'ma.id_akun'
                    )->groupBy('ma.kode_akun');
            
                $secondQuery = DB::table("master_akun AS ma")
                    ->select(
                        'jd.id_jurnal',
                        'jd.id_akun',
                        'ma.kode_akun',
                        'ma.nama_akun',
                        'ma.posisi_debet',
                        DB::raw('0 AS debet'),
                        DB::raw('0 AS credit'),
                        DB::raw('0 AS saldo_debet'),
                        DB::raw('0 AS saldo_credit'),
                        DB::raw('IFNULL(SUM(jd.debet), 0) AS trx_debet'),
                        DB::raw('IFNULL(SUM(jd.credit), 0) AS trx_credit')
                    )->leftJoin('jurnal_detail AS jd', 'jd.id_akun', '=', 'ma.id_akun')
                    ->leftJoin('jurnal_header AS jh', 'jh.id_jurnal', '=', 'jd.id_jurnal')
                    ->where('jh.tanggal_jurnal', '>=', $start_of_the_month)
                    ->where('jh.tanggal_jurnal', '<', $start_date)
                    ->where('jh.void', '=', 0)
                    ->whereRaw(
                        'CASE 
                            WHEN MONTH("'.$start_date.'") = 12 THEN
                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                    CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                            ELSE 
                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                        END'
                    )->groupBy('ma.kode_akun');
            
                if ($id_cabang != "all" && $id_cabang != "") {
                    $mainQuery = $mainQuery->where('ma.id_cabang', '=', $id_cabang);
                    $secondQuery = $secondQuery->where('ma.id_cabang', '=', $id_cabang);
                }
            
                $combinedQuery = $secondQuery->union($mainQuery);

                $results = DB::query()->fromSub($combinedQuery, "fQ")
                    ->select(
                        'id_akun',
                        'kode_akun',
                        'nama_akun',
                        'posisi_debet',
                        DB::raw('SUM(debet) AS debet'),
                        DB::raw('SUM(credit) AS credit'),
                        DB::raw('SUM(saldo_debet) AS saldo_debet'),
                        DB::raw('SUM(saldo_credit) AS saldo_credit'),
                        DB::raw('SUM(trx_debet) AS trx_debet'),
                        DB::raw('SUM(trx_credit) AS trx_credit'),
                        DB::raw('CASE 
                            WHEN IFNULL(posisi_debet, 1) != 0 THEN
                                SUM(saldo_debet) - SUM(saldo_credit) + SUM(trx_debet) - SUM(trx_credit)
                            ELSE
                                SUM(saldo_credit) - SUM(saldo_debet) + SUM(trx_credit) - SUM(trx_debet)
                        END AS saldo_start'),
                        DB::raw('CASE 
                            WHEN IFNULL(posisi_debet, 1) != 0 THEN
                                SUM(saldo_debet) - SUM(saldo_credit) + SUM(trx_debet) - SUM(trx_credit) + SUM(debet) - SUM(credit)
                            ELSE
                                SUM(saldo_credit) - SUM(saldo_debet) + SUM(trx_credit) - SUM(trx_debet) + SUM(credit) - SUM(debet)
                        END AS saldo_balance')
                    )->groupBy("kode_akun")
                    ->havingRaw("debet <> 0 OR credit <> 0 OR saldo_debet <> 0 OR saldo_credit <> 0 OR saldo_balance <> 0")
                    ->orderBy("kode_akun", "ASC");
            } 
            else {
                // Start queries
                $mainQuery = DB::table("master_akun AS ma")
                    ->select(
                        DB::raw('"'.$saldo_date.'" AS tanggal_jurnal'),
                        DB::raw('"-" AS id_jurnal'),
                        DB::raw('"-" AS kode_jurnal'),
                        DB::raw('"-" AS jenis_jurnal'),
                        DB::raw('"-" AS id_transaksi'),
                        DB::raw('"-" AS id_cabang'),
                        DB::raw('"-" AS nama_cabang'),
                        'sb.id_akun',
                        'ma.kode_akun',
                        'ma.nama_akun',
                        'ma.posisi_debet',
                        DB::raw('"Saldo Awal" AS keterangan'),
                        DB::raw('IFNULL(SUM(sb.debet), 0) AS debet'),
                        DB::raw('IFNULL(SUM(sb.credit), 0) AS credit'),
                        DB::raw('IFNULL(SUM(trx.trx_debet), 0) AS trx_debet'),
                        DB::raw('IFNULL(SUM(trx.trx_credit), 0) AS trx_credit')
                    )->leftJoin('saldo_balance as sb', function ($join) use ($month, $year) {
                        $join->on('sb.id_akun', '=', 'ma.id_akun')
                            ->where('sb.bulan', '=', $month)
                            ->where('sb.tahun', '=', $year);
                        }
                    )->leftJoin(
                        DB::raw('
                            (SELECT
                                jh.tanggal_jurnal, 
                                jd.id_jurnal, 
                                jh.kode_jurnal,
                                jh.id_cabang,
                                ma.id_akun, 
                                ma.kode_akun, 
                                IFNULL(SUM(jd.debet), 0) AS trx_debet, 
                                IFNULL(SUM(jd.credit), 0) AS trx_credit
                            FROM
                                jurnal_detail jd
                            JOIN jurnal_header jh ON jh.id_jurnal = jd.id_jurnal
                            JOIN master_akun ma ON ma.id_akun = jd.id_akun
                            JOIN cabang cb ON cb.id_cabang = jh.id_cabang
                            WHERE
                                jh.tanggal_jurnal >= "'.$start_of_the_month.'"
                                AND jh.tanggal_jurnal < "'.$start_date.'"
                                AND jh.void = 0
                                AND (
                                        CASE 
                                            WHEN MONTH("'.$start_date.'") = 12 THEN
                                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                                AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                                    CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                                            ELSE 
                                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                            END
                                )
                            GROUP BY
                                ma.kode_akun
                        ) trx'), 'trx.id_akun', '=', 'ma.id_akun'
                    )->groupBy('ma.kode_akun');

                $secondQuery = DB::table("jurnal_detail AS jd")
                    ->select(
                        'jh.tanggal_jurnal', 
                        'jd.id_jurnal', 
                        'jh.kode_jurnal',
                        'jh.jenis_jurnal',
                        'jh.id_transaksi',
                        'jh.id_cabang', 
                        'cb.nama_cabang',
                        'jd.id_akun', 
                        'ma.kode_akun', 
                        'ma.nama_akun',
                        'ma.posisi_debet', 
                        'jd.keterangan',
                        'jd.debet', 
                        'jd.credit',
                        DB::raw('0 AS trx_debet'),
                        DB::raw('0 AS trx_credit')
                    )->join('jurnal_header AS jh', 'jh.id_jurnal', '=', 'jd.id_jurnal')
                    ->join('master_akun AS ma', 'ma.id_akun', '=', 'jd.id_akun')
                    ->join('cabang AS cb', 'cb.id_cabang', '=', 'jh.id_cabang')
                    ->where('jh.tanggal_jurnal', '>=', $start_date)
                    ->where('jh.tanggal_jurnal', '<=', $end_date)
                    ->where('jh.void', '=', 0)
                    ->whereRaw(
                        '(CASE 
                            WHEN MONTH("'.$start_date.'") = 12 THEN
                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                    CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                            ELSE 
                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                        END)'
                    );
            
                if ($id_cabang != "all" && $id_cabang != "") {
                    $mainQuery = $mainQuery->where('ma.id_cabang', '=', $id_cabang);
                    $secondQuery = $secondQuery->where('jh.id_cabang', '=', $id_cabang);
                }
                if ($coa != "all" && $coa != "") {
                    if ($id_cabang != "all" && $id_cabang != "") {
                        $mainQuery = $mainQuery->where('ma.id_akun', '=', $coa);
                        $secondQuery = $secondQuery->where('ma.id_akun', '=', $coa);
                    }
                    else {
                        $checkAkun = Akun::where('id_akun', $coa)->first();
                        $kodeAkun = ($checkAkun)?$checkAkun->kode_akun:"--";
                        $mainQuery = $mainQuery->where('ma.kode_akun', '=', $kodeAkun);
                        $secondQuery = $secondQuery->where('ma.kode_akun', '=', $kodeAkun);
                    }
                }

                $combinedQuery = $secondQuery->union($mainQuery);

                $results = DB::query()
                    ->fromSub($combinedQuery, "fQ")
                    ->select(
                        'tanggal_jurnal',
                        'id_jurnal',
                        'kode_jurnal',
                        'jenis_jurnal',
                        'id_transaksi',
                        'id_cabang',
                        'nama_cabang',
                        'id_akun',
                        'kode_akun',
                        'nama_akun',
                        'keterangan',
                        DB::raw('IFNULL(debet, 0) + IFNULL(trx_debet, 0) AS debet'),
                        DB::raw('IFNULL(credit, 0) + IFNULL(trx_credit, 0) AS credit'),
                        'trx_debet',
                        'trx_credit',
                        'posisi_debet'
                    )->orderBy('kode_akun', 'ASC')
                    ->orderBy('tanggal_jurnal', 'ASC');
            }

            // dd(count($groupByQuery->get()));
            $datas = $results->get();

            if ($type == "detail") {
                // Initialize running balance
                $runningBalance = 0;
                $runningCoa = '';
                // Iterate over the results to accumulate the running balance
                foreach ($datas as $key => $row) {
                    if ($runningCoa != $row->kode_akun) {
                        $runningCoa = $row->kode_akun;
                        if ($row->posisi_debet ?? 1 != 0) {
                            $runningBalance = $row->debet - $row->credit;
                        } else {
                            $runningBalance = $row->credit - $row->debet;
                        }
                    } 
                    else {
                        if ($row->posisi_debet ?? 1 != 0) {
                            $runningBalance = $runningBalance + $row->debet - $row->credit;
                        }
                        else {
                            $runningBalance = $runningBalance + $row->credit - $row->debet;
                        }
                    }
                    // Add saldo_baru to the current row
                    $datas[$key]->saldo_balance = $runningBalance;
                }
            }

            $data = [
                "type" => $type,
                "start_date" => $start_date,
                "end_date" => $end_date,
                "cabang" => $nama_cabang,
                "data" => $datas,
            ];
            Log::info("finish export pdf");
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
            } 
            else {
                return response()->json([
                    "result" => false,
                    "message" => "Tidak ada data",
                ]);
            }
        } catch (\Exception $e) {
            $message = "Failed to get populate general ledger for export";
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
            Log::info("start export excel");
            // dd($request->all());
            // Init Data
            $type = $request->type;
            $id_cabang = $request->id_cabang;
            $cabang = Cabang::find($id_cabang);
            $nama_cabang = ($cabang) ? $cabang->nama_cabang : null;
            $coa = $request->coa;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $month = date("m", strtotime("$start_date"));
            $year = date("Y", strtotime($start_date));
            $start_of_the_month = date("Y-m-01", strtotime($start_date));
            $saldo_date = date("Y-m-d", strtotime($start_date . " -1 day"));

            if ($type == "recap") {
                // Start the queries
                $mainQuery = DB::table("master_akun AS ma")
                    ->select(
                        'jl.id_jurnal',
                        'jl.id_akun',
                        'ma.kode_akun',
                        'ma.nama_akun',
                        'ma.posisi_debet',
                        DB::raw('IFNULL(SUM(jl.debet), 0) AS debet'),
                        DB::raw('IFNULL(SUM(jl.credit), 0) AS credit'),
                        DB::raw('IFNULL(sb.debet, 0) AS saldo_debet'),
                        DB::raw('IFNULL(sb.credit, 0) AS saldo_credit'),
                        DB::raw('0 AS trx_debet'),
                        DB::raw('0 AS trx_credit')
                    )->leftJoin('saldo_balance as sb', function ($join) use ($month, $year) {
                            $join->on('sb.id_akun', '=', 'ma.id_akun')
                                ->where('sb.bulan', '=', $month)
                                ->where('sb.tahun', '=', $year);
                        }
                    )->leftJoin(
                        DB::raw('
                            (SELECT 
                                jd.id_jurnal,
                                jd.id_akun,
                                jd.debet,
                                jd.credit
                            FROM 
                                jurnal_detail jd
                                JOIN jurnal_header jh ON jh.id_jurnal = jd.id_jurnal
                                JOIN master_akun mal ON mal.id_akun = jd.id_akun
                            WHERE
                                jh.tanggal_jurnal >= "'.$start_date.'" 
                                AND jh.tanggal_jurnal <= "'.$end_date.'"
                                AND jh.void = 0
                                AND (
                                    CASE 
                                        WHEN MONTH("'.$start_date.'") = 12 THEN
                                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN mal.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN mal.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                                CASE WHEN mal.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                                        ELSE 
                                            COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN mal.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                            AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                CASE WHEN mal.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                    END
                                )
                        ) jl'), 'jl.id_akun', '=', 'ma.id_akun'
                    )->groupBy('ma.kode_akun');
            
                $secondQuery = DB::table("master_akun AS ma")
                    ->select(
                        'jd.id_jurnal',
                        'jd.id_akun',
                        'ma.kode_akun',
                        'ma.nama_akun',
                        'ma.posisi_debet',
                        DB::raw('0 AS debet'),
                        DB::raw('0 AS credit'),
                        DB::raw('0 AS saldo_debet'),
                        DB::raw('0 AS saldo_credit'),
                        DB::raw('IFNULL(SUM(jd.debet), 0) AS trx_debet'),
                        DB::raw('IFNULL(SUM(jd.credit), 0) AS trx_credit')
                    )->leftJoin('jurnal_detail AS jd', 'jd.id_akun', '=', 'ma.id_akun')
                    ->leftJoin('jurnal_header AS jh', 'jh.id_jurnal', '=', 'jd.id_jurnal')
                    ->where('jh.tanggal_jurnal', '>=', $start_of_the_month)
                    ->where('jh.tanggal_jurnal', '<', $start_date)
                    ->where('jh.void', '=', 0)
                    ->whereRaw(
                        'CASE 
                            WHEN MONTH("'.$start_date.'") = 12 THEN
                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                    CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                            ELSE 
                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                        END'
                    )->groupBy('ma.kode_akun');
            
                if ($id_cabang != "all" && $id_cabang != "") {
                    $mainQuery = $mainQuery->where('ma.id_cabang', '=', $id_cabang);
                    $secondQuery = $secondQuery->where('ma.id_cabang', '=', $id_cabang);
                }
            
                $combinedQuery = $secondQuery->union($mainQuery);

                $results = DB::query()->fromSub($combinedQuery, "fQ")
                    ->select(
                        'id_akun',
                        'kode_akun',
                        'nama_akun',
                        'posisi_debet',
                        DB::raw('SUM(debet) AS debet'),
                        DB::raw('SUM(credit) AS credit'),
                        DB::raw('SUM(saldo_debet) AS saldo_debet'),
                        DB::raw('SUM(saldo_credit) AS saldo_credit'),
                        DB::raw('SUM(trx_debet) AS trx_debet'),
                        DB::raw('SUM(trx_credit) AS trx_credit'),
                        DB::raw('CASE 
                            WHEN IFNULL(posisi_debet, 1) != 0 THEN
                                SUM(saldo_debet) - SUM(saldo_credit) + SUM(trx_debet) - SUM(trx_credit)
                            ELSE
                                SUM(saldo_credit) - SUM(saldo_debet) + SUM(trx_credit) - SUM(trx_debet)
                        END AS saldo_start'),
                        DB::raw('CASE 
                            WHEN IFNULL(posisi_debet, 1) != 0 THEN
                                SUM(saldo_debet) - SUM(saldo_credit) + SUM(trx_debet) - SUM(trx_credit) + SUM(debet) - SUM(credit)
                            ELSE
                                SUM(saldo_credit) - SUM(saldo_debet) + SUM(trx_credit) - SUM(trx_debet) + SUM(credit) - SUM(debet)
                        END AS saldo_balance')
                    )->groupBy("kode_akun")
                    ->havingRaw("debet <> 0 OR credit <> 0 OR saldo_debet <> 0 OR saldo_credit <> 0 OR saldo_balance <> 0")
                    ->orderBy("kode_akun", "ASC");
            } 
            else {
                // Start queries
                $mainQuery = DB::table("master_akun AS ma")
                    ->select(
                        DB::raw('"'.$saldo_date.'" AS tanggal_jurnal'),
                        DB::raw('"-" AS id_jurnal'),
                        DB::raw('"-" AS kode_jurnal'),
                        DB::raw('"-" AS jenis_jurnal'),
                        DB::raw('"-" AS id_transaksi'),
                        DB::raw('"-" AS id_cabang'),
                        DB::raw('"-" AS nama_cabang'),
                        'sb.id_akun',
                        'ma.kode_akun',
                        'ma.nama_akun',
                        'ma.posisi_debet',
                        DB::raw('"Saldo Awal" AS keterangan'),
                        DB::raw('IFNULL(SUM(sb.debet), 0) AS debet'),
                        DB::raw('IFNULL(SUM(sb.credit), 0) AS credit'),
                        DB::raw('IFNULL(SUM(trx.trx_debet), 0) AS trx_debet'),
                        DB::raw('IFNULL(SUM(trx.trx_credit), 0) AS trx_credit')
                    )->leftJoin('saldo_balance as sb', function ($join) use ($month, $year) {
                        $join->on('sb.id_akun', '=', 'ma.id_akun')
                            ->where('sb.bulan', '=', $month)
                            ->where('sb.tahun', '=', $year);
                        }
                    )->leftJoin(
                        DB::raw('
                            (SELECT
                                jh.tanggal_jurnal, 
                                jd.id_jurnal, 
                                jh.kode_jurnal,
                                jh.id_cabang,
                                ma.id_akun, 
                                ma.kode_akun, 
                                IFNULL(SUM(jd.debet), 0) AS trx_debet, 
                                IFNULL(SUM(jd.credit), 0) AS trx_credit
                            FROM
                                jurnal_detail jd
                            JOIN jurnal_header jh ON jh.id_jurnal = jd.id_jurnal
                            JOIN master_akun ma ON ma.id_akun = jd.id_akun
                            JOIN cabang cb ON cb.id_cabang = jh.id_cabang
                            WHERE
                                jh.tanggal_jurnal >= "'.$start_of_the_month.'"
                                AND jh.tanggal_jurnal < "'.$start_date.'"
                                AND jh.void = 0
                                AND (
                                        CASE 
                                            WHEN MONTH("'.$start_date.'") = 12 THEN
                                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                                AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                                    CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                                            ELSE 
                                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                            END
                                )
                            GROUP BY
                                ma.kode_akun
                        ) trx'), 'trx.id_akun', '=', 'ma.id_akun'
                    )->groupBy('ma.kode_akun');

                $secondQuery = DB::table("jurnal_detail AS jd")
                    ->select(
                        'jh.tanggal_jurnal', 
                        'jd.id_jurnal', 
                        'jh.kode_jurnal',
                        'jh.jenis_jurnal',
                        'jh.id_transaksi',
                        'jh.id_cabang', 
                        'cb.nama_cabang',
                        'jd.id_akun', 
                        'ma.kode_akun', 
                        'ma.nama_akun',
                        'ma.posisi_debet', 
                        'jd.keterangan',
                        'jd.debet', 
                        'jd.credit',
                        DB::raw('0 AS trx_debet'),
                        DB::raw('0 AS trx_credit')
                    )->join('jurnal_header AS jh', 'jh.id_jurnal', '=', 'jd.id_jurnal')
                    ->join('master_akun AS ma', 'ma.id_akun', '=', 'jd.id_akun')
                    ->join('cabang AS cb', 'cb.id_cabang', '=', 'jh.id_cabang')
                    ->where('jh.tanggal_jurnal', '>=', $start_date)
                    ->where('jh.tanggal_jurnal', '<=', $end_date)
                    ->where('jh.void', '=', 0)
                    ->whereRaw(
                        '(CASE 
                            WHEN MONTH("'.$start_date.'") = 12 THEN
                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE 
                                    CASE WHEN ma.tipe_akun = 0 THEN "Closing 3%" ELSE "--" END
                            ELSE 
                                COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 1%" ELSE "--" END
                                AND COALESCE(jh.id_transaksi, "") NOT LIKE
                                    CASE WHEN ma.tipe_akun = 1 THEN "Closing 2%" ELSE "--" END
                        END)'
                    );
            
                if ($id_cabang != "all" && $id_cabang != "") {
                    $mainQuery = $mainQuery->where('ma.id_cabang', '=', $id_cabang);
                    $secondQuery = $secondQuery->where('jh.id_cabang', '=', $id_cabang);
                }
                if ($coa != "all" && $coa != "") {
                    if ($id_cabang != "all" && $id_cabang != "") {
                        $mainQuery = $mainQuery->where('ma.id_akun', '=', $coa);
                        $secondQuery = $secondQuery->where('ma.id_akun', '=', $coa);
                    }
                    else {
                        $checkAkun = Akun::where('id_akun', $coa)->first();
                        $kodeAkun = ($checkAkun)?$checkAkun->kode_akun:"--";
                        $mainQuery = $mainQuery->where('ma.kode_akun', '=', $kodeAkun);
                        $secondQuery = $secondQuery->where('ma.kode_akun', '=', $kodeAkun);
                    }
                }

                $combinedQuery = $secondQuery->union($mainQuery);

                $results = DB::query()
                    ->fromSub($combinedQuery, "fQ")
                    ->select(
                        'tanggal_jurnal',
                        'id_jurnal',
                        'kode_jurnal',
                        'jenis_jurnal',
                        'id_transaksi',
                        'id_cabang',
                        'nama_cabang',
                        'id_akun',
                        'kode_akun',
                        'nama_akun',
                        'keterangan',
                        DB::raw('IFNULL(debet, 0) + IFNULL(trx_debet, 0) AS debet'),
                        DB::raw('IFNULL(credit, 0) + IFNULL(trx_credit, 0) AS credit'),
                        'trx_debet',
                        'trx_credit',
                        'posisi_debet'
                    )->orderBy('kode_akun', 'ASC')
                    ->orderBy('tanggal_jurnal', 'ASC');
            }

            // dd(count($groupByQuery->get()));
            $datas = $results->get();

            if ($type == "detail") {
                // Initialize running balance
                $runningBalance = 0;
                $runningCoa = '';
                // Iterate over the results to accumulate the running balance
                foreach ($datas as $key => $row) {
                    if ($runningCoa != $row->kode_akun) {
                        $runningCoa = $row->kode_akun;
                        if ($row->posisi_debet ?? 1 != 0) {
                            $runningBalance = $row->debet - $row->credit;
                        } else {
                            $runningBalance = $row->credit - $row->debet;
                        }
                    } 
                    else {
                        if ($row->posisi_debet ?? 1 != 0) {
                            $runningBalance = $runningBalance + $row->debet - $row->credit;
                        }
                        else {
                            $runningBalance = $runningBalance + $row->credit - $row->debet;
                        }
                    }
                    // Add saldo_baru to the current row
                    $datas[$key]->saldo_balance = $runningBalance;
                }
            }

            $data = [
                "type" => $type,
                "start_date" => $start_date,
                "end_date" => $end_date,
                "cabang" => $nama_cabang,
                "data" => $datas,
            ];
            Log::info("finish export excel");
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
