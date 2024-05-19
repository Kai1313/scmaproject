<?php

namespace App\Http\Controllers;

use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
use App\Models\Accounting\Periode;
use App\Models\Accounting\StockCorrectionHeader;
use App\Models\Accounting\TrxSaldo;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use App\Models\Master\Pelanggan;
use App\Models\Master\Pemasok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;

class AdjustmentLedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (checkUserSession($request, 'transaction/adjustment_ledger', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        // $cabang = Cabang::find(1);
        $data_cabang = getCabang();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Penyesuaian | List",
            // "cabang" => $cabang,
            "data_cabang" => $data_cabang,
        ];

        return view('accounting.journal.adjusting_journal.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (checkAccessMenu('transaction/adjustment_ledger', 'create') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = getCabang();
        $data_pelanggan = Pelanggan::all();
        $data_pemasok = Pemasok::all();
        $userSession = $request->session()->get('user');

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Penyesuaian | Create",
            "data_cabang" => $data_cabang,
            "data_pelanggan" => $data_pelanggan,
            "data_pemasok" => $data_pemasok,
            "user_id" => $userSession->id_pengguna,
        ];

        Log::debug(json_encode($request->session()->get('user')));

        return view('accounting.journal.adjusting_journal.form', $data);
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
            Log::info("Store Jurnal Data");
            // dd($request->all());

            // cek detail
            if (count($request->detail) <= 0) {
                return response()->json([
                    "result" => false,
                    "message" => "Error. There is no detail",
                ]);
            }

            // Init data
            $userData = $request->session()->get('user');
            if (!$userData) {
                Log::info("session expired");
                if (checkUserSession($request, 'transaction/adjustment_ledger', 'create') == false) {
                    return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
                }
                $userData = $request->session()->get('user');
            }
            $journalDate = date('Y-m-d', strtotime($request->header[0]["tanggal"]));
            $journalType = "ME";
            $cabangID = $request->header[0]["cabang"];
            $noteHeader = $request->header[0]["notes"];
            $userRecord = $userData->id_pengguna;
            $userModified = $userData->id_pengguna;
            $dateRecord = date('Y-m-d');
            $detailData = $request->detail;
            // dd($detailData);

            // Check periode close
            $period = Periode::checkPeriod($journalDate);
            if ($period) {
                return response()->json([
                    "result" => false,
                    "message" => "Period close, cannot save with this date",
                ]);
            }

            DB::beginTransaction();
            // Store Header
            $header = new JurnalHeader();
            $header->id_cabang = $cabangID;
            $header->jenis_jurnal = $journalType;
            $header->catatan = $noteHeader;
            $header->void = 0;
            $header->tanggal_jurnal = $journalDate;
            $header->user_created = $userRecord;
            $header->user_modified = $userModified;
            $header->dt_created = $dateRecord;
            $header->dt_modified = $dateRecord;
            $header->kode_jurnal = JurnalHeader::generateJournalCode($cabangID, $journalType);
            // dd($header);
            if ($header->kode_jurnal == "error") {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when store Jurnal data on table header. Kode Jurnal result is error",
                ]);
            }
            if (!$header->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when store Jurnal data on table header",
                ]);
            }

            // Store Detail and Update
            foreach ($detailData as $key => $data) {
                // Check if detail master akun has differenct cabang with header
                $checkAkun = Akun::find($data["akun"]);
                if ($checkAkun) {
                    if ($checkAkun->id_cabang != $cabangID) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail. Detail has different cabang on the account " . $checkAkun->nama_akun,
                        ]);
                    }
                }

                //Store Detail
                $debet = str_replace('.', '', $data['debet']);
                $debet = str_replace(',', '.', $debet);

                $kredit = str_replace('.', '', $data['kredit']);
                $kredit = str_replace(',', '.', $kredit);

                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = ($data['guid'] == 'gen') ? count($detailData) + 1 : $key + 1;
                $detail->id_akun = $data['akun'];
                $detail->keterangan = $data['notes'];
                $detail->id_transaksi = $data['trx'];
                $detail->debet = $debet;
                $detail->credit = $kredit;
                $detail->user_created = $userRecord;
                $detail->user_modified = $userModified;
                $detail->dt_created = $dateRecord;
                $detail->dt_modified = $dateRecord;
                // dd(json_encode($detail));
                if (!$detail->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail",
                    ]);
                }

                // Update Saldo Transaksi
                $trx_saldo = TrxSaldo::where("id_transaksi", $data["trx"])->first();
                if ($trx_saldo) {
                    $tolak = (str_contains($data["notes"], "GIRO-TOLAK")) ? true : false;
                    $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $debet, $kredit, $tolak);
                    if (!$update_trx_saldo) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on update saldo transaksi",
                        ]);
                    }
                    if ($tolak) {
                        // Cek if journal have another trx
                        $jouDetail = JurnalDetail::where("id_jurnal", $trx_saldo->id_jurnal)->get();
                        if ($jouDetail) {
                            foreach ($jouDetail as $key => $detail) {
                                if ($detail->id_transaksi != "") {
                                    $trx_saldo_detail = TrxSaldo::where("id_transaksi", $detail->id_transaksi)->first();
                                    if ($trx_saldo_detail) {
                                        $update_trx_saldo_detail = $this->revertTrxSaldo($trx_saldo_detail, $detail->debet, $detail->credit);
                                        if (!$update_trx_saldo_detail) {
                                            DB::rollback();
                                            return response()->json([
                                                "result" => false,
                                                "message" => "Error when update saldo trnasaksi piutang giro tolak on revert saldo transaksi",
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // Check kode jurnal
            $check = JurnalHeader::where("id_jurnal", "!=", $header->id_jurnal)->where("kode_jurnal", $header->kode_jurnal)->get();
            Log::info("checking");
            Log::info(count($check));
            if (count($check) > 0) {
                Log::info("Jurnal header update kode jurnal");
                Log::info(count($check));
                Log::info(json_encode($check));
                $newHeader = JurnalHeader::find($header->id_jurnal);
                $newHeader->kode_jurnal = $this->generateJournalCode($cabangID, $journalType);
                if (!$newHeader->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data update kode jurnal on table header",
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully stored Jurnal data",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when store Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "message" => "Error when store Jurnal data",
                "exception" => $e,
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if (checkAccessMenu('transaction/adjustment_ledger', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_jurnal_header = JurnalHeader::join('cabang', 'cabang.id_cabang', 'jurnal_header.id_cabang')
            ->where('id_jurnal', $id)
            ->select('jurnal_header.*', 'cabang.kode_cabang', 'cabang.nama_cabang', DB::raw(
                '(CASE
                    WHEN jenis_jurnal = "KK" THEN "Kas Keluar"
                    WHEN jenis_jurnal = "KM" THEN "Kas Masuk"
                    WHEN jenis_jurnal = "BK" THEN "Bank Keluar"
                    WHEN jenis_jurnal = "BM" THEN "Bank Masuk"
                    WHEN jenis_jurnal = "PG" THEN "Piutang Giro"
                    WHEN jenis_jurnal = "HG" THEN "Hutang Giro"
                    WHEN jenis_jurnal = "ME" THEN "Memorial"
                END) as jenis_name'
            ))
            ->first();

        $data_jurnal_detail = JurnalDetail::join('master_akun', 'master_akun.id_akun', 'jurnal_detail.id_akun')
            ->where('id_jurnal', $id)
            ->orderBy('index', 'ASC')
            ->select('jurnal_detail.*', 'master_akun.kode_akun', 'master_akun.nama_akun')
            ->get();

        $data_jurnal_header->catatan = str_replace("\n", '<br>', $data_jurnal_header->catatan);

        foreach ($data_jurnal_detail as $key => $value) {
            $notes = str_replace("\n", '<br>', $value->keterangan);
            $value->keterangan = $notes;
        }

        // Get shortcut link
        $shortcutLink = $this->getShortcutLink($data_jurnal_header->id_transaksi);

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Umum | Detail",
            "data_jurnal_header" => $data_jurnal_header,
            "data_jurnal_detail" => $data_jurnal_detail,
            "shortcutLink" => $shortcutLink,
        ];

        return view('accounting.journal.adjusting_journal.detail', $data);
    }

    public function printSlip(Request $request, $id)
    {
        $data_jurnal_header = JurnalHeader::leftjoin('master_slip', 'master_slip.id_slip', 'jurnal_header.id_slip')
            ->join('cabang', 'cabang.id_cabang', 'jurnal_header.id_cabang')
            ->where('id_jurnal', $id)
            ->select('jurnal_header.*', 'cabang.kode_cabang', 'cabang.nama_cabang', 'master_slip.kode_slip', 'master_slip.nama_slip', 'master_slip.id_akun', DB::raw(
                '(CASE
                    WHEN jenis_jurnal = "KK" THEN "Kas Keluar"
                    WHEN jenis_jurnal = "KM" THEN "Kas Masuk"
                    WHEN jenis_jurnal = "BK" THEN "Bank Keluar"
                    WHEN jenis_jurnal = "BM" THEN "Bank Masuk"
                    WHEN jenis_jurnal = "PG" THEN "Piutang Giro"
                    WHEN jenis_jurnal = "HG" THEN "Hutang Giro"
                    WHEN jenis_jurnal = "ME" THEN "Memorial"
                END) as jenis_name'
            ))
            ->first();

        $data_jurnal_detail = JurnalDetail::join('master_akun', 'master_akun.id_akun', 'jurnal_detail.id_akun')
            ->join('jurnal_header', 'jurnal_header.id_jurnal', 'jurnal_detail.id_jurnal')
            ->where('jurnal_header.id_jurnal', $id)
            ->whereRaw('
                CASE
                    WHEN jenis_jurnal IN ("KK", "BK", "PG") THEN jurnal_detail.credit = 0
                    WHEN jenis_jurnal IN ("KM", "BM", "HG") THEN jurnal_detail.debet = 0
                    ELSE jurnal_detail.credit >= 0
                END
            ')
            ->select('jurnal_detail.*', 'master_akun.kode_akun', 'master_akun.nama_akun')
            ->get();

        $data_jurnal_header->catatan = str_replace("\n", '<br>', $data_jurnal_header->catatan);

        foreach ($data_jurnal_detail as $key => $value) {
            $notes = str_replace("\n", '<br>', $value->keterangan);
            $value->keterangan = $notes;
        }

        $data = [
            "data_jurnal_header" => $data_jurnal_header,
            "data_jurnal_detail" => $data_jurnal_detail,
        ];

        // dd($data);

        // return view('accounting.journal.general_ledger.print', $data);

        $pdf = PDF::loadView('accounting.journal.general_ledger.print', $data);
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream('printJurnal_' . $data_jurnal_header->kode_jurnal . '.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if (checkAccessMenu('transaction/adjustment_ledger', 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = getCabang();
        $data_pelanggan = Pelanggan::all();
        $data_pemasok = Pemasok::all();
        $userSession = $request->session()->get('user');
        $jurnal_header = JurnalHeader::find($id);
        $jurnal_detail = JurnalDetail::where("id_jurnal", $id)->get();
        $details = [];
        $i = 0;
        foreach ($jurnal_detail as $key => $jurnal) {
            $akun = Akun::find($jurnal->id_akun);
            $trx_id = TrxSaldo::where("id_transaksi", $jurnal->id_transaksi)->first();
            $notes = str_replace("\n", '<br>', $jurnal->keterangan);
            $details[] = [
                // "guid" => (++$i == count($jurnal_detail)) ? "gen" : (($trx_id) ? "trx-" . $trx_id->id : $jurnal->index),
                "guid" => (++$i == count($jurnal_detail) && $trx_id) ? "trx-" . $trx_id->id : $jurnal->index,
                "akun" => $akun->id_akun,
                "nama_akun" => $akun->nama_akun,
                "kode_akun" => $akun->kode_akun,
                "notes" => $notes,
                "trx" => $jurnal->id_transaksi,
                "debet" => $jurnal->debet,
                "kredit" => $jurnal->credit,
            ];
        }

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Penyesuaian | Edit",
            "data_cabang" => $data_cabang,
            "data_pelanggan" => $data_pelanggan,
            "data_pemasok" => $data_pemasok,
            "jurnal_header" => $jurnal_header,
            "jurnal_detail" => json_encode($details),
            "jurnal_detail_count" => count($details),
            "user_id" => $userSession->id_pengguna,
        ];
        // dd($data);

        // Check periode close
        $period = Periode::checkPeriod($jurnal_header->tanggal_jurnal);
        if ($period) {
            if (checkUserSession($request, 'general_ledger', 'show') == false) {
                return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
            }

            // $cabang = Cabang::find(1);

            $data = [
                "pageTitle" => "SCA Accounting | Transaksi Jurnal Penyesuaian | List",
                // "cabang" => $cabang,
                "data_cabang" => $data_cabang,
                "closePeriod" => $period,
            ];

            return view('accounting.journal.adjusting_journal.index', $data);
        }

        Log::debug(json_encode($request->session()->get('user')));

        return view('accounting.journal.adjusting_journal.form_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            Log::info("Update Jurnal Data");
            // Log::debug($request->all());
            // exit();

            // cek detail
            if (count($request->detail) <= 0) {
                return response()->json([
                    "result" => false,
                    "message" => "Error. There is no detail",
                ]);
            }

            // Init data
            $userData = $request->session()->get('user');
            if (!$userData) {
                Log::info("session expired");
                if (checkUserSession($request, 'transaction/adjustment_ledger', 'edit') == false) {
                    return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
                }
                $userData = $request->session()->get('user');
            }
            $journalDate = date('Y-m-d', strtotime($request->header[0]["tanggal"]));
            $journalID = $request->header[0]["id_jurnal"];
            $journalType = "ME";
            $cabangID = $request->header[0]["cabang"];
            $noteHeader = $request->header[0]["notes"];
            $userModified = $userData->id_pengguna;
            $dateModified = date('Y-m-d');
            $detailData = $request->detail;

            // Check periode close
            $period = Periode::checkPeriod($journalDate);
            if ($period) {
                return response()->json([
                    "result" => false,
                    "message" => "Period close, cannot update this transaction",
                ]);
            }

            DB::beginTransaction();

            // Find Header data and delete detail
            $header = JurnalHeader::where("id_jurnal", $journalID)->first();
            // Update saldo transaksi before delete
            $old_details = JurnalDetail::where("id_jurnal", $journalID)->get();
            foreach ($old_details as $key => $detail) {
                $tolak = (str_contains($detail->keterangan, "GIRO-TOLAK")) ? true : false;
                $debet = $detail->debet;
                $kredit = $detail->credit;
                $trx_saldo = TrxSaldo::where("id_transaksi", $detail->id_transaksi)->first();
                if ($trx_saldo) {
                    Log::info("Masuk sini2");
                    Log::info(json_encode($trx_saldo));
                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $debet, $kredit);
                    if (!$update_trx_saldo) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on revert saldo transaksi",
                        ]);
                    }
                }
                if ($tolak) {
                    // Cek if journal have another trx
                    $jouDetail = JurnalDetail::where("id_jurnal", $journalID)->get();
                    if ($jouDetail) {
                        foreach ($jouDetail as $key => $detail) {
                            if ($detail->id_transaksi != "" && !str_contains($detail->keterangan, "GIRO-TOLAK")) {
                                $trx_saldo_detail = TrxSaldo::where("id_transaksi", $detail->id_transaksi)->first();
                                if ($trx_saldo_detail) {
                                    $update_trx_saldo_detail = $this->updateTrxSaldo($trx_saldo_detail, $detail->credit, $detail->debet);
                                    if (!$update_trx_saldo_detail) {
                                        DB::rollback();
                                        return response()->json([
                                            "result" => false,
                                            "message" => "Error when update saldo trnasaksi piutang giro tolak on revert saldo transaksi",
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            JurnalDetail::where('id_jurnal', $journalID)->delete();

            // Store Header
            $header->id_cabang = $cabangID;
            $header->tanggal_jurnal = $journalDate;
            $header->jenis_jurnal = $journalType;
            $header->catatan = $noteHeader;
            $header->user_modified = $userModified;
            $header->dt_modified = $dateModified;
            if (!$header->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when store Jurnal data on table header",
                ]);
            }

            // Store New Detail
            foreach ($detailData as $key => $data) {
                // Check if detail master akun has differenct cabang with header
                $checkAkun = Akun::find($data["akun"]);
                if ($checkAkun) {
                    if ($checkAkun->id_cabang != $cabangID) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail. Detail has different cabang on the account " . $checkAkun->nama_akun,
                        ]);
                    }
                }

                //Store Detail
                $debet = str_replace('.', '', $data['debet']);
                $debet = str_replace(',', '.', $debet);

                $kredit = str_replace('.', '', $data['kredit']);
                $kredit = str_replace(',', '.', $kredit);

                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = ($data['guid'] == 'gen') ? count($detailData) + 1 : $key + 1;
                $detail->id_akun = $data['akun'];
                $detail->keterangan = $data['notes'];
                $detail->id_transaksi = $data['trx'];
                $detail->debet = $debet;
                $detail->credit = $kredit;
                $detail->user_modified = $userModified;
                $detail->dt_modified = $dateModified;
                if (!$detail->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail",
                    ]);
                }

                //  Update Saldo Transaksi
                $trx_saldo = TrxSaldo::where("id_transaksi", $data["trx"])->first();
                if ($trx_saldo) {
                    $tolak = (str_contains($data["notes"], "GIRO-TOLAK")) ? true : false;
                    $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $debet, $kredit, $tolak);
                    if (!$update_trx_saldo) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on update saldo transaksi",
                        ]);
                    }
                    if ($tolak) {
                        // Cek if journal have another trx
                        $jouDetail = JurnalDetail::where("id_jurnal", $trx_saldo->id_jurnal)->get();
                        if ($jouDetail) {
                            foreach ($jouDetail as $key => $detail) {
                                if ($detail->id_transaksi != "") {
                                    $trx_saldo_detail = TrxSaldo::where("id_transaksi", $detail->id_transaksi)->first();
                                    if ($trx_saldo_detail) {
                                        $update_trx_saldo_detail = $this->revertTrxSaldo($trx_saldo_detail, $detail->debet, $detail->credit);
                                        if (!$update_trx_saldo_detail) {
                                            DB::rollback();
                                            return response()->json([
                                                "result" => false,
                                                "message" => "Error when update saldo trnasaksi piutang giro tolak on revert saldo transaksi",
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully stored Jurnal data",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when store Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "message" => "Error when store Jurnal data",
                "exception" => $e,
            ]);
        }
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
        $cabang = $request->cabang;
        $void = $request->void;
        $startDate = $request->startDate ?? date('Y-m-d');
        $endDate = $request->endDate ?? date('Y-m-t');
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

        // $data_general_ledger = JurnalHeader::leftJoin('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')->select('jurnal_header.*', DB::raw('GROUP_CONCAT(jurnal_detail.id_transaksi SEPARATOR \', \') AS concat_id_transaksi'), DB::raw('
        //             (CASE
        //                 WHEN jenis_jurnal = "KK" THEN "Kas Keluar"
        //                 WHEN jenis_jurnal = "KM" THEN "Kas Masuk"
        //                 WHEN jenis_jurnal = "BK" THEN "Bank Keluar"
        //                 WHEN jenis_jurnal = "BM" THEN "Bank Masuk"
        //                 WHEN jenis_jurnal = "PG" THEN "Piutang Giro"
        //                 WHEN jenis_jurnal = "HG" THEN "Hutang Giro"
        //                 WHEN jenis_jurnal = "ME" THEN "Memorial"
        //             END) as jenis_name
        //         '))->groupBy('jurnal_header.id_jurnal');

        // $data_general_ledger_table = DB::table(DB::raw('(' . $data_general_ledger->toSql() . ') as jurnal_header'))
        //     ->join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
        //     ->where('jurnal_header.void', $void)
        //     ->where('jenis_jurnal', 'ME')
        //     ->where('id_cabang', $cabang)
        //     ->groupBy('jurnal_header.id_jurnal', 'jurnal_header.tanggal_jurnal')
        //     ->select('jurnal_header.*', DB::raw('SUM(jurnal_detail.credit) as jumlah'));

        $data_general_ledger = JurnalDetail::join("jurnal_header", "jurnal_header.id_jurnal", "jurnal_detail.id_jurnal")
            ->leftJoin("master_slip", "master_slip.id_slip", "jurnal_header.id_slip")
            ->where("jurnal_header.void", $void)
            ->where("jurnal_header.jenis_jurnal", "ME")
            ->where("jurnal_header.id_cabang", $cabang)
            ->where("jurnal_header.tanggal_jurnal", ">=", $startDate)
            ->where("jurnal_header.tanggal_jurnal", "<=", $endDate)
            ->groupBy("jurnal_header.kode_jurnal")
            ->select("jurnal_header.*", "master_slip.kode_slip", DB::raw('CASE
                WHEN jurnal_header.jenis_jurnal = "KK" THEN "Kas Keluar"
                WHEN jurnal_header.jenis_jurnal = "KM" THEN "Kas Masuk"
                WHEN jurnal_header.jenis_jurnal = "BK" THEN "Bank Keluar"
                WHEN jurnal_header.jenis_jurnal = "BM" THEN "Bank Masuk"
                WHEN jurnal_header.jenis_jurnal = "PG" THEN "Piutang Giro"
                WHEN jurnal_header.jenis_jurnal = "HG" THEN "Hutang Giro"
                WHEN jurnal_header.jenis_jurnal = "ME" THEN "Memorial"
                END AS jenis_name'), DB::raw('GROUP_CONCAT(jurnal_detail.id_transaksi ORDER BY jurnal_detail.id_transaksi SEPARATOR \', \') AS concat_id_transaksi'), DB::raw('SUM(jurnal_detail.credit) AS jumlah'));

        $data_general_ledger_table = DB::table(DB::raw('(' . $data_general_ledger->toSql() . ') as jurnal_header'))
            ->mergeBindings($data_general_ledger->getQuery())
            ->select("jurnal_header.*");

        if (!empty($keyword)) {
            $data_general_ledger_table->where(function ($query) use ($keyword) {
                $query->orWhere('jurnal_header.kode_jurnal', 'LIKE', "%$keyword%")
                    ->orWhere('jurnal_header.tanggal_jurnal', 'LIKE', "%$keyword%")
                    ->orWhere('jenis_name', 'LIKE', "%$keyword%")
                    // ->orWhere('jurnal_header.id_transaksi', 'LIKE', "%$keyword%")
                    ->orWhere('concat_id_transaksi', 'LIKE', "%$keyword%")
                    ->orWhere('jurnal_header.catatan', 'LIKE', "%$keyword%");
            });
        }

        $filtered_data = $data_general_ledger_table->get();

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
                    $data_general_ledger_table->orderBy($column, $directon);
                }
            }
        } else {
            $data_general_ledger_table->orderBy('jurnal_header.id_jurnal', 'DESC');
        }

        // pagination
        if ($current_page) {
            $page = $current_page;
            $limit_data = $limit;

            if ($limit) {
                $limit_data = $limit;
            }

            $offset = ($page - 1) * $limit_data;
            if ($offset < 0) {
                $offset = 0;
            }

            $data_general_ledger_table->skip($offset)->take($limit_data);
        }
        $dataTable = $data_general_ledger_table->get();
        $table['draw'] = $draw;
        $table['recordsTotal'] = count($dataTable);
        $table['recordsFiltered'] = count($filtered_data);
        $table['data'] = $dataTable;

        return json_encode($table);
    }

    public function void(Request $request, $id)
    {
        if (checkAccessMenu('transaction/adjustment_ledger', 'edit') == false) {
            return response()->json([
                "result" => false,
                "message" => "Error when void Jurnal data, user has no access!",
            ]);
        }

        try {
            Log::info("Void Jurnal Data");
            // exit();

            // Init data
            $userData = $request->session()->get('user');
            $userVoid = $userData->id_pengguna;
            $dateVoid = date('Y-m-d');

            // Find Header data
            $header = JurnalHeader::where("id_jurnal", $id)->first();
            // Check periode close
            $period = Periode::checkPeriod($header->tanggal_jurnal);
            if ($period) {
                return response()->json([
                    "result" => false,
                    "message" => "Period close, cannot void this transaction",
                ]);
            }

            DB::beginTransaction();

            // Find Header data
            $header = JurnalHeader::where("id_jurnal", $id)->first();
            if (checkAccessMenu('transaction/adjustment_ledger', 'delete') == false) {
                return response()->json([
                    "result" => false,
                    "message" => "Maaf, tidak bisa void jurnal dengan id " . $id . ", anda tidak punya akses!",
                ]);
            }

            // Update saldo transaksi before delete
            $old_details = JurnalDetail::where("id_jurnal", $id)->get();
            foreach ($old_details as $key => $detail) {
                $tolak = (str_contains($detail->keterangan, "GIRO-TOLAK")) ? true : false;
                $debet = $detail->debet;
                $kredit = $detail->credit;
                $trx_saldo = TrxSaldo::where("id_transaksi", $detail->id_transaksi)->first();
                if ($trx_saldo) {
                    // Log::info(json_encode($trx_saldo));
                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $debet, $kredit);
                    if (!$update_trx_saldo) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on revert saldo transaksi",
                        ]);
                    }
                }
                if ($tolak) {
                    // Cek if journal have another trx
                    $jouDetail = JurnalDetail::where("id_jurnal", $id)->get();
                    if ($jouDetail) {
                        foreach ($jouDetail as $key => $detail) {
                            if ($detail->id_transaksi != "" && !str_contains($detail->keterangan, "GIRO-TOLAK")) {
                                $trx_saldo_detail = TrxSaldo::where("id_transaksi", $detail->id_transaksi)->first();
                                if ($trx_saldo_detail) {
                                    $update_trx_saldo_detail = $this->updateTrxSaldo($trx_saldo_detail, $detail->credit, $detail->debet);
                                    if (!$update_trx_saldo_detail) {
                                        DB::rollback();
                                        return response()->json([
                                            "result" => false,
                                            "message" => "Error when update saldo trnasaksi piutang giro tolak on revert saldo transaksi",
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Update Header Status
            $header->void = 1;
            $header->user_void = $userVoid;
            $header->dt_void = $dateVoid;
            if (!$header->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when void Jurnal data",
                ]);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully void Jurnal data",
            ]);
            // } else {
            //     return response()->json([
            //         "result" => false,
            //         "message" => "Maaf, tidak bisa void jurnal dengan id " . $id . ", anda tidak punya akses!",
            //     ]);
            // }
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when void Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "message" => "Error when void Jurnal data",
                "exception" => $e,
            ]);
        }
    }

    public function active(Request $request, $id)
    {
        if (checkAccessMenu('transaction/adjustment_ledger', 'edit') == false) {
            return response()->json([
                "result" => false,
                "message" => "Error when activate Jurnal data, user has no access!",
            ]);
        }

        try {
            Log::info("Activate Jurnal Data");
            // exit();

            // Init data
            $userData = $request->session()->get('user');
            $userVoid = $userData->id_pengguna;
            $dateVoid = date('Y-m-d');

            DB::beginTransaction();

            // Find Header data
            $header = JurnalHeader::where("id_jurnal", $id)->first();

            // Update Header Status
            $header->void = 0;
            $header->user_void = $userVoid;
            $header->dt_void = $dateVoid;
            if (!$header->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when activate Jurnal data",
                ]);
            }
            $data_detail = JurnalDetail::where("id_jurnal", $id)->get();
            foreach ($data_detail as $key => $detail) {
                //  Update Saldo Transaksi
                $trx_saldo = TrxSaldo::where("id_transaksi", $detail->id_transaksi)->first();
                if ($trx_saldo) {
                    $tolak = (str_contains($detail->keterangan, "GIRO-TOLAK")) ? true : false;
                    $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $detail->debet, $detail->credit, $tolak);
                    if (!$update_trx_saldo) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on update saldo transaksi",
                        ]);
                    }
                    if ($tolak) {
                        // Cek if journal have another trx
                        $jouDetail = JurnalDetail::where("id_jurnal", $trx_saldo->id_jurnal)->get();
                        if ($jouDetail) {
                            foreach ($jouDetail as $key => $detail) {
                                if ($detail->id_transaksi != "") {
                                    $trx_saldo_detail = TrxSaldo::where("id_transaksi", $detail->id_transaksi)->first();
                                    if ($trx_saldo_detail) {
                                        $update_trx_saldo_detail = $this->revertTrxSaldo($trx_saldo_detail, $detail->debet, $detail->credit);
                                        if (!$update_trx_saldo_detail) {
                                            DB::rollback();
                                            return response()->json([
                                                "result" => false,
                                                "message" => "Error when update saldo trnasaksi piutang giro tolak on revert saldo transaksi",
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            };

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully activate Jurnal data",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when activate Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "message" => "Error when activate Jurnal data",
                "exception" => $e,
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

    public function updateTrxSaldo($trx, $debet, $kredit, $tolak = false)
    {
        try {
            // DB::beginTransaction();
            $trx_saldo = TrxSaldo::find($trx->id);
            $type = ($tolak) ? $trx->tipe_transaksi . " Tolak" : $trx->tipe_transaksi;
            $current_total = $trx->total;
            $current_bayar = $trx->bayar;
            $current_sisa = $trx->sisa;
            switch ($type) {
                case 'Penjualan':
                    $trx_saldo->bayar = $current_bayar + $kredit;
                    $trx_saldo->sisa = $current_sisa - $kredit;
                    break;
                case 'Retur Penjualan':
                    $trx_saldo->bayar = $current_bayar + $debet;
                    $trx_saldo->sisa = $current_sisa - $debet;
                    break;
                case 'Pembelian':
                    $trx_saldo->bayar = $current_bayar + $debet;
                    $trx_saldo->sisa = $current_sisa - $debet;
                    break;
                case 'Uang Muka Pembelian':
                    $trx_saldo->bayar = $current_bayar + $debet;
                    $trx_saldo->sisa = $current_sisa - $debet;
                    break;
                case 'Uang Muka Penjualan':
                    $trx_saldo->bayar = $current_bayar + $kredit;
                    $trx_saldo->sisa = $current_sisa - $kredit;
                    break;
                case 'Retur Pembelian':
                    $trx_saldo->bayar = $current_bayar + $kredit;
                    $trx_saldo->sisa = $current_sisa - $kredit;
                    break;
                case 'Piutang Giro':
                    $trx_saldo->bayar = $current_bayar + $kredit;
                    $trx_saldo->sisa = $current_sisa - $kredit;
                    $trx_saldo->status_giro = ($kredit > $debet) ? 1 : 2;
                    break;
                case 'Hutang Giro':
                    $trx_saldo->bayar = $current_bayar + $debet;
                    $trx_saldo->sisa = $current_sisa - $debet;
                    $trx_saldo->status_giro = ($debet > $kredit) ? 1 : 2;
                    break;
                case 'Piutang Giro Tolak':
                    $trx_saldo->bayar = $current_bayar + $kredit;
                    $trx_saldo->sisa = $current_sisa - $kredit;
                    $trx_saldo->status_giro = 2;
                    break;
                case 'Hutang Giro Tolak':
                    $trx_saldo->bayar = $current_bayar + $debet;
                    $trx_saldo->sisa = $current_sisa - $debet;
                    $trx_saldo->status_giro = 2;
                    break;

                default:
                    // DB::rollback();
                    return false;
                    break;
            }
            if (!$trx_saldo->save()) {
                // DB::rollback();
                return false;
            }
            return true;
            // DB::commit();
        } catch (\Exception $e) {
            // DB::rollback();
            Log::error($e);
            return false;
        }
    }

    public function revertTrxSaldo($trx, $debet, $kredit)
    {
        try {
            // DB::beginTransaction();
            $trx_saldo = TrxSaldo::find($trx->id);
            $type = $trx->tipe_transaksi;
            $current_total = $trx->total;
            $current_bayar = $trx->bayar;
            $current_sisa = $trx->sisa;
            switch ($type) {
                case 'Penjualan':
                    $trx_saldo->bayar = $current_bayar - $kredit;
                    $trx_saldo->sisa = $current_sisa + $kredit;
                    break;
                case 'Retur Penjualan':
                    $trx_saldo->bayar = $current_bayar - $debet;
                    $trx_saldo->sisa = $current_sisa + $debet;
                    break;
                case 'Pembelian':
                    $trx_saldo->bayar = $current_bayar - $debet;
                    $trx_saldo->sisa = $current_sisa + $debet;
                    break;
                case 'Uang Muka Pembelian':
                    $trx_saldo->bayar = $current_bayar - $debet;
                    $trx_saldo->sisa = $current_sisa + $debet;
                    break;
                case 'Uang Muka Penjualan':
                    $trx_saldo->bayar = $current_bayar - $kredit;
                    $trx_saldo->sisa = $current_sisa + $kredit;
                    break;
                case 'Retur Pembelian':
                    $trx_saldo->bayar = $current_bayar - $kredit;
                    $trx_saldo->sisa = $current_sisa + $kredit;
                    break;
                case 'Piutang Giro':
                    $trx_saldo->bayar = $current_bayar - $kredit;
                    $trx_saldo->sisa = $current_sisa + $kredit;
                    $trx_saldo->status_giro = 0;
                    break;
                case 'Hutang Giro':
                    $trx_saldo->bayar = $current_bayar - $debet;
                    $trx_saldo->sisa = $current_sisa + $debet;
                    $trx_saldo->status_giro = 0;
                    break;
                case 'Piutang Giro Tolak':
                    $trx_saldo->bayar = $current_bayar - $kredit;
                    $trx_saldo->sisa = $current_sisa + $kredit;
                    $trx_saldo->status_giro = 0;
                    break;
                case 'Hutang Giro Tolak':
                    $trx_saldo->bayar = $current_bayar - $debet;
                    $trx_saldo->sisa = $current_sisa + $debet;
                    $trx_saldo->status_giro = 0;
                    break;

                default:
                    // DB::rollback();
                    return false;
                    break;
            }
            if (!$trx_saldo->save()) {
                // DB::rollback();
                return false;
            }
            return true;
            // DB::commit();
        } catch (\Exception $e) {
            // DB::rollback();
            Log::error($e);
            return false;
        }
    }

    public function getGiroReject(Request $request, $ids)
    {
        try {
            $id = $ids;
            $jurnal_header = JurnalHeader::where("kode_jurnal", $ids)->first();
            $jurnal_detail = ($jurnal_header) ? JurnalDetail::where("id_jurnal", $jurnal_header->id_jurnal)->get() : [];
            $details = [];
            $i = 0;
            foreach ($jurnal_detail as $key => $jurnal) {
                $akun = Akun::find($jurnal->id_akun);
                $trx_id = TrxSaldo::where("id_transaksi", $jurnal->id_transaksi)->first();
                Log::info("iterator " . $i);
                Log::info("count " . count($jurnal_detail));
                $details[] = [
                    "guid" => (++$i == count($jurnal_detail)) ? "gen" : (($trx_id) ? "trx-" . $trx_id->id : $jurnal->index),
                    "akun" => $akun->id_akun,
                    "nama_akun" => $akun->nama_akun,
                    "kode_akun" => $akun->kode_akun,
                    "notes" => ($i == count($jurnal_detail)) ? $jurnal->keterangan . " - GIRO-TOLAK" : $jurnal->keterangan,
                    "trx" => ($i == count($jurnal_detail)) ? $ids : $jurnal->id_transaksi,
                    // Dibalik karena giro tolak
                    "debet" => str_replace(".", ",", $jurnal->credit),
                    "kredit" => str_replace(".", ",", $jurnal->debet),
                ];
            }
            return response()->json([
                "result" => true,
                "message" => "Successfully fetched giro tolak",
                "details" => $details,
            ]);
        } catch (\Exception $e) {
            $message = "Error when get giro tolak record";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => false,
                "message" => $message,
            ]);
        }
    }

    public function getShortcutLink($code)
    {
        try {
            // Get first part of code
            $code = str_replace("Closing ", "", $code);
            $explode = explode("-", $code);
            switch ($explode[0]) {
                case 'KR':
                    // Stock Correction
                    $getId = StockCorrectionHeader::where("nama_koreksi_stok", $code)->first();
                    $shortcutLink = ($getId) ? "https://vps.scasda.my.id/v2/#koreksi_stok&data_master=$getId->id_koreksi_stok" : null;
                    break;

                default:
                    $shortcutLink = null;
                    break;
            }

            return $shortcutLink;
        } catch (\Exception $e) {
            Log::error("Error when get reference shortcut link");
            return null;
        }
    }
}
