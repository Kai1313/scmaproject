<?php

namespace App\Http\Controllers;

use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
use App\Models\Accounting\TrxSaldo;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use App\Models\Master\Pelanggan;
use App\Models\Master\Pemasok;
use App\Models\Master\Setting;
use App\Models\Master\Slip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;

class GeneralLedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (checkUserSession($request, 'general_ledger', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $cabang = Cabang::find(1);
        $data_cabang = Cabang::all();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Umum | List",
            "cabang" => $cabang,
            "data_cabang" => $data_cabang,
        ];

        return view('accounting.journal.general_ledger.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (checkAccessMenu('general_ledger', 'create') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = Cabang::where("status_cabang", 1)->get();
        $data_pelanggan = Pelanggan::all();
        $data_pemasok = Pemasok::all();
        $piutang_dagang = Setting::where("code", "Piutang Dagang")->where("id_cabang", "1")->first();
        $hutang_dagang = Setting::where("code", "Hutang Dagang")->where("id_cabang", "1")->first();
        // dd(json_encode($piutang_dagang));

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Umum | Create",
            "data_cabang" => $data_cabang,
            "data_pelanggan" => $data_pelanggan,
            "data_pemasok" => $data_pemasok,
            "piutang_dagang" => $piutang_dagang,
            "hutang_dagang" => $hutang_dagang,
        ];

        Log::debug(json_encode($request->session()->get('user')));

        return view('accounting.journal.general_ledger.form', $data);
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
            // exit();

            // cek detail
            if (count($request->detail) <= 0) {
                return response()->json([
                    "result" => false,
                    "message" => "Error. There is no detail",
                ]);
            }

            // Init data
            $journalDate = date('Y-m-d', strtotime($request->header[0]["tanggal"]));
            $giroNo = ($request->header[0]["nomor_giro"]) ? $request->header[0]["nomor_giro"] : null;
            $giroDate = ($request->header[0]["tanggal_giro"]) ? date('Y-m-d', strtotime($request->header[0]["tanggal_giro"])) : null;
            $giroDueDate = ($request->header[0]["tanggal_jt_giro"]) ? date('Y-m-d', strtotime($request->header[0]["tanggal_jt_giro"])) : null;
            $slipID = $request->header[0]["slip"];
            $slipGiroID = ($request->header[0]["slip_giro"])?$request->header[0]["slip_giro"] : null;
            $journalType = $request->header[0]["jenis"];
            $cabangID = $request->header[0]["cabang"];
            $noteHeader = $request->header[0]["notes"];
            $userData = $request->session()->get('user');
            $userRecord = $userData->id_pengguna;
            $userModified = $userData->id_pengguna;
            $dateRecord = date('Y-m-d h:i:s');
            $detailData = $request->detail;
            // dd($detailData);

            DB::beginTransaction();
            // Store Header
            $header = new JurnalHeader();
            $header->id_cabang = $cabangID;
            $header->jenis_jurnal = $journalType;
            $header->id_slip = $slipID;
            $header->id_slip2 = $slipGiroID;
            $header->catatan = $noteHeader;
            $header->no_giro = $giroNo;
            $header->tanggal_giro = $giroDate;
            $header->tanggal_giro_jt = $giroDueDate;
            $header->void = 0;
            $header->tanggal_jurnal = $journalDate;
            $header->user_created = $userRecord;
            $header->user_modified = $userModified;
            $header->dt_created = $dateRecord;
            $header->dt_modified = $dateRecord;
            $header->kode_jurnal = $this->generateJournalCode($cabangID, $journalType, $slipID);
            // dd($header);
            if (!$header->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when store Jurnal data on table header",
                ]);
            }

            // Insert trx saldo if jenis_jurnal PG|HG
            if ($journalType == "PG" || $journalType == "HG") {
                $sum = 0;
                foreach ($detailData as $key => $item) {
                    $debet = str_replace('.', '', $item['debet']);
                    $debet = str_replace(',', '.', $debet);

                    $kredit = str_replace('.', '', $item['kredit']);
                    $kredit = str_replace(',', '.', $kredit);
                    $sum += ($journalType == "PG") ? $debet : $kredit;
                }
                $trx_saldo = new TrxSaldo;
                $trx_saldo->tipe_transaksi = ($journalType == "PG") ? "Piutang Giro" : "Hutang Giro";
                $trx_saldo->id_transaksi = $header->kode_jurnal;
                $trx_saldo->tanggal = $journalDate;
                $trx_saldo->total = $sum;
                $trx_saldo->bayar = 0;
                $trx_saldo->sisa = $sum;
                $trx_saldo->id_jurnal = $header->id_jurnal;
                $trx_saldo->id_slip2 = $slipGiroID;
                $trx_saldo->no_giro = $header->no_giro;
                $trx_saldo->tanggal_giro = $header->tanggal_giro;
                $trx_saldo->tanggal_giro_jt = $header->tanggal_giro_jt;
                $trx_saldo->status_giro = 0;
                if (!$trx_saldo->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store trx saldo after table header",
                    ]);
                }
            }

            // Store Detail and Update Saldo Transaksi
            foreach ($detailData as $key => $data) {
                // Store Detail
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
                    $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $debet, $kredit);
                    if (!$update_trx_saldo) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on update saldo transaksi",
                        ]);
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if (checkAccessMenu('general_ledger', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_jurnal_header = JurnalHeader::join('master_slip', 'master_slip.id_slip', 'jurnal_header.id_slip')
            ->join('master_slip as ms2', 'ms2.id_slip', 'jurnal_header.id_slip2')
            ->join('cabang', 'cabang.id_cabang', 'jurnal_header.id_cabang')
            ->where('id_jurnal', $id)
            ->select('jurnal_header.*', 'cabang.kode_cabang', 'cabang.nama_cabang', 'master_slip.kode_slip', 'master_slip.nama_slip', 'ms2.nama_slip as nama_slip2', 'ms2.kode_slip as kode_slip2', DB::raw(
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
            ->select('jurnal_detail.*', 'master_akun.kode_akun', 'master_akun.nama_akun')
            ->get();

        $data_jurnal_header->catatan = str_replace("\n", '<br/>', $data_jurnal_header->catatan);

        foreach ($data_jurnal_detail as $key => $value) {
            $notes = str_replace("\n", '<br/>', $value->keterangan);
            $value->keterangan = $notes;
        }

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Umum | Detail",
            "data_jurnal_header" => $data_jurnal_header,
            "data_jurnal_detail" => $data_jurnal_detail,
        ];

        return view('accounting.journal.general_ledger.detail', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printSlip(Request $request, $id)
    {
        $data_jurnal_header = JurnalHeader::join('master_slip', 'master_slip.id_slip', 'jurnal_header.id_slip')
            ->join('master_akun', 'master_akun.id_akun', 'master_slip.id_akun')
            ->join('cabang', 'cabang.id_cabang', 'jurnal_header.id_cabang')
            ->where('id_jurnal', $id)
            ->select('jurnal_header.*', 'cabang.kode_cabang', 'cabang.nama_cabang', 'master_slip.kode_slip', 'master_slip.nama_slip', 'master_slip.id_akun', 'master_akun.kode_akun', 'master_akun.nama_akun', DB::raw(
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

        $data_jurnal_header->catatan = str_replace("\n", '<br/>', $data_jurnal_header->catatan);

        foreach ($data_jurnal_detail as $key => $value) {
            $notes = str_replace("\n", '<br/>', $value->keterangan);
            $value->keterangan = $notes;
        }

        $data = [
            "data_jurnal_header" => $data_jurnal_header,
            "data_jurnal_detail" => $data_jurnal_detail,
        ];

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
        if (checkAccessMenu('general_ledger', 'edit') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = Cabang::where("status_cabang", 1)->get();
        $data_pelanggan = Pelanggan::all();
        $data_pemasok = Pemasok::all();
        $jurnal_header = JurnalHeader::find($id);
        $jurnal_detail = JurnalDetail::where("id_jurnal", $id)->get();
        $details = [];
        $i = 0;
        foreach ($jurnal_detail as $key => $jurnal) {
            $akun = Akun::find($jurnal->id_akun);
            $trx_id = TrxSaldo::where("id_transaksi", $jurnal->id_transaksi)->first();
            $notes = str_replace("\n", '<br/>', $jurnal->keterangan);
            $details[] = [
                "guid" => (++$i == count($jurnal_detail)) ? "gen" : (($trx_id) ? "trx-" . $trx_id->id : $jurnal->index),
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
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Umum | Edit",
            "data_cabang" => $data_cabang,
            "data_pelanggan" => $data_pelanggan,
            "data_pemasok" => $data_pemasok,
            "jurnal_header" => $jurnal_header,
            "jurnal_detail" => json_encode($details),
            "jurnal_detail_count" => count($details),
        ];
        // dd($details);

        Log::debug(json_encode($request->session()->get('user')));

        return view('accounting.journal.general_ledger.form_edit', $data);
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
            // dd($request->all());

            // cek detail
            if (count($request->detail) <= 0) {
                return response()->json([
                    "result" => false,
                    "message" => "Error. There is no detail",
                ]);
            }

            // Init data
            $journalDate = date('Y-m-d', strtotime($request->header[0]["tanggal"]));
            $giroNo = ($request->header[0]["nomor_giro"]) ? $request->header[0]["nomor_giro"] : null;
            $giroDate = ($request->header[0]["tanggal_giro"]) ? date('Y-m-d', strtotime($request->header[0]["tanggal_giro"])) : null;
            $giroDueDate = ($request->header[0]["tanggal_jt_giro"]) ? date('Y-m-d', strtotime($request->header[0]["tanggal_jt_giro"])) : null;
            $journalID = $request->header[0]["id_jurnal"];
            $slipID = $request->header[0]["slip"];
            $slipGiroID = ($request->header[0]["slip_giro"])?$request->header[0]["slip_giro"] : null;
            $journalType = $request->header[0]["jenis"];
            $cabangID = $request->header[0]["cabang"];
            $noteHeader = $request->header[0]["notes"];
            $userData = $request->session()->get('user');
            $userModified = $userData->id_pengguna;
            $dateModified = date('Y-m-d h:i:s');
            $detailData = $request->detail;

            DB::beginTransaction();

            // Find Header data and delete detail
            $header = JurnalHeader::where("id_jurnal", $journalID)->first();
            // Update saldo transaksi before delete
            $old_details = JurnalDetail::where("id_jurnal", $journalID)->get();
            foreach ($old_details as $key => $detail) {
                $debet = $detail->debet;
                $kredit = $detail->credit;
                $trx_saldo = TrxSaldo::where("id_transaksi", $detail->id_transaksi)->first();
                if ($trx_saldo) {
                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $debet, $kredit);
                    if (!$update_trx_saldo) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on revert saldo transaksi",
                        ]);
                    }
                }
            }
            JurnalDetail::where('id_jurnal', $journalID)->delete();

            // Store Header
            $header->id_cabang = $cabangID;
            $header->tanggal_jurnal = $journalDate;
            $header->jenis_jurnal = $journalType;
            $header->id_slip = $slipID;
            $header->id_slip2 = $slipGiroID;
            $header->catatan = $noteHeader;
            $header->no_giro = $giroNo;
            $header->tanggal_giro = $giroDate;
            $header->tanggal_giro_jt = $giroDueDate;
            $header->user_modified = $userModified;
            $header->dt_modified = $dateModified;
            $header->save();
            if (!$header->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when store Jurnal data on table header",
                ]);
            }

            // Insert trx saldo if jenis_jurnal PG|HG
            if ($journalType == "PG" || $journalType == "HG") {
                $check = TrxSaldo::where("id_jurnal", $journalID)->first();
                $sum = 0;
                foreach ($detailData as $key => $item) {
                    $debet = str_replace('.', '', $item['debet']);
                    $debet = str_replace(',', '.', $debet);
    
                    $kredit = str_replace('.', '', $item['kredit']);
                    $kredit = str_replace(',', '.', $kredit);
                    $sum += ($journalType == "PG") ? $debet : $kredit;
                }
                $trx_saldo = ($check) ? TrxSaldo::where("id_jurnal", $journalID)->first() : new TrxSaldo;
                $trx_saldo->tipe_transaksi = ($journalType == "PG") ? "Piutang Giro" : "Hutang Giro";
                $trx_saldo->id_transaksi = $header->kode_jurnal;
                $trx_saldo->tanggal = $journalDate;
                $trx_saldo->total = $sum;
                $trx_saldo->bayar = 0;
                $trx_saldo->sisa = $sum;
                $trx_saldo->id_jurnal = $header->id_jurnal;
                $trx_saldo->id_slip2 = $slipGiroID;
                $trx_saldo->no_giro = $header->no_giro;
                $trx_saldo->tanggal_giro = $header->tanggal_giro;
                $trx_saldo->tanggal_giro_jt = $header->tanggal_giro_jt;
                $trx_saldo->status_giro = 0;
                if (!$trx_saldo->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store trx saldo after table header",
                    ]);
                }
            }

            // Store Detail
            foreach ($detailData as $key => $data) {
                // Store Detail
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
                    $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $debet, $kredit);
                    if (!$update_trx_saldo) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on update saldo transaksi",
                        ]);
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

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
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

        $data_general_ledger = JurnalHeader::join('master_slip', 'jurnal_header.id_slip', 'master_slip.id_slip')
            ->select('jurnal_header.*', 'master_slip.kode_slip', DB::raw('
                    (CASE
                        WHEN jenis_jurnal = "KK" THEN "Kas Keluar"
                        WHEN jenis_jurnal = "KM" THEN "Kas Masuk"
                        WHEN jenis_jurnal = "BK" THEN "Bank Keluar"
                        WHEN jenis_jurnal = "BM" THEN "Bank Masuk"
                        WHEN jenis_jurnal = "PG" THEN "Piutang Giro"
                        WHEN jenis_jurnal = "HG" THEN "Hutang Giro"
                        WHEN jenis_jurnal = "ME" THEN "Memorial"
                    END) as jenis_name
                '));

        $data_general_ledger_table = DB::table(DB::raw('(' . $data_general_ledger->toSql() . ') as jurnal_header'))
            ->join('jurnal_detail', 'jurnal_detail.id_jurnal', 'jurnal_header.id_jurnal')
            ->where('jurnal_header.void', $void)
            ->where('jurnal_header.jenis_jurnal', '<>', 'ME')
            ->groupBy('jurnal_header.id_jurnal')
            ->select('jurnal_header.*', DB::raw('SUM(jurnal_detail.credit) as jumlah'));
        $data_general_ledger_table = $data_general_ledger_table->where('id_cabang', $cabang);

        if (isset($keyword)) {
            $data_general_ledger_table->where(function ($query) use ($keyword) {
                $query->orWhere('kode_jurnal', 'LIKE', "%$keyword%")
                    ->orWhere('tanggal_jurnal', 'LIKE', "%$keyword%")
                    ->orWhere('jenis_name', 'LIKE', "%$keyword%")
                    ->orWhere('jurnal_header.id_transaksi', 'LIKE', "%$keyword%")
                    ->orWhere('catatan', 'LIKE', "%$keyword%")
                    ->orWhere('kode_slip', 'LIKE', "%$keyword%");
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
            $data_general_ledger_table->orderBy('jurnal_header.dt_modified', 'DESC');
        }

        // pagination
        if ($current_page) {
            $page = $current_page;
            $limit_data = $data_general_ledger_table->count();

            if ($limit) {
                $limit_data = $limit;
            }

            $offset = ($page - 1) * $limit_data;
            if ($offset < 0) {
                $offset = 0;
            }

            $data_general_ledger_table->skip($offset)->take($limit_data);
        }

        $table['draw'] = $draw;
        $table['recordsTotal'] = $data_general_ledger_table->count();
        $table['recordsFiltered'] = $filtered_data->count();
        $table['data'] = $data_general_ledger_table->get();

        return json_encode($table);
    }

    public function void(Request $request, $id)
    {
        if (checkAccessMenu('general_ledger', 'edit') == false) {
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

            DB::beginTransaction();

            // Find Header data
            $header = JurnalHeader::where("id_jurnal", $id)->first();
            $session = $request->session()->get('access');
            if (checkAccessMenu('general_ledger', 'delete') == false) {
                return response()->json([
                    "result" => false,
                    "message" => "Maaf, tidak bisa void jurnal dengan id " . $id . ", anda tidak punya akses!",
                ]);
            }

            // Update saldo transaksi before delete
            $old_details = JurnalDetail::where("id_jurnal", $id)->get();
            foreach ($old_details as $key => $detail) {
                $debet = $detail->debet;
                $kredit = $detail->credit;
                $trx_saldo = TrxSaldo::where("id_transaksi", $detail->id_transaksi)->first();
                if ($trx_saldo) {
                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $debet, $kredit);
                    if (!$update_trx_saldo) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on revert saldo transaksi",
                        ]);
                    }
                }
            }

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
        } 
        catch (\Exception $e) {
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
        if (checkAccessMenu('general_ledger', 'edit') == false) {
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
                    $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $detail->debet, $detail->credit);
                    if (!$update_trx_saldo) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on update saldo transaksi",
                        ]);
                    }
                }
            }

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

    public function generateJournalCode($cabang, $jenis, $slip)
    {
        try {
            $ex = 0;
            do {
                // Init data
                $kodeCabang = Cabang::find($cabang);
                $kodeSlip = Slip::find($slip);
                $prefix = $kodeCabang->kode_cabang . "." . $jenis . "." . $kodeSlip->kode_slip . "." . date("ym");

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

    public function populateTrxSaldo(Request $request)
    {
        // Log::info(json_encode($request->all()));
        // dd($request->all());
        try {
            // Init
            $type = str_replace("_", " ", $request->transaction_type);
            if (str_contains($type, "tolak")) {
                $type = str_replace(" tolak", "", $type);
            }
            $customer = $request->customer;
            $supplier = $request->supplier;
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
            $data_saldo = TrxSaldo::leftJoin("pelanggan", "pelanggan.id_pelanggan", "saldo_transaksi.id_pelanggan")
                ->leftJoin("pemasok", "pemasok.id_pemasok", "saldo_transaksi.id_pemasok")
                ->where("tipe_transaksi", ucwords($type))->where("sisa", "<>", 0);
            if ($customer != "") {
                $data_saldo = $data_saldo->where("saldo_transaksi.id_pelanggan", $customer);
            }
            if ($supplier != "") {
                $data_saldo = $data_saldo->where("saldo_transaksi.id_pemasok", $supplier);
            }
            if ($type == "piutang giro" || $type == "hutang giro") {
                $slip = $request->slip;
                if ($slip != "") {
                    $data_saldo = $data_saldo->where("saldo_transaksi.id_slip2", $slip);
                }
                $data_saldo = $data_saldo->where("saldo_transaksi.tanggal_giro_jt", "<=", date("Y-m-d"))->join("jurnal_header", "jurnal_header.id_jurnal", "saldo_transaksi.id_jurnal")->join("master_slip", "master_slip.id_slip", "jurnal_header.id_slip")->join("master_akun", "master_akun.id_akun", "master_slip.id_akun")->select("saldo_transaksi.*", "pelanggan.nama_pelanggan as nama_pelanggan", "pemasok.nama_pemasok as nama_pemasok", "master_akun.nama_akun as nama_akun", "master_akun.kode_akun as kode_akun", "master_akun.id_akun as id_akun");
            } 
            else {
                $data_saldo = $data_saldo->select("saldo_transaksi.*", "pelanggan.nama_pelanggan as nama_pelanggan", "pemasok.nama_pemasok as nama_pemasok");
            }
            if (isset($keyword)) {
                $data_saldo->where(function ($query) use ($keyword, $type) {
                    $query->orWhere('tanggal', 'LIKE', "%$keyword%")
                        ->orWhere('saldo_transaksi.id_transaksi', 'LIKE', "%$keyword%")
                        ->orWhere('ref_id', 'LIKE', "%$keyword%")
                        ->orWhere('saldo_transaksi.catatan', 'LIKE', "%$keyword%")
                        ->orWhere('saldo_transaksi.id_pelanggan', 'LIKE', "%$keyword%")
                        ->orWhere('pelanggan.nama_pelanggan', 'LIKE', "%$keyword%")
                        ->orWhere('saldo_transaksi.id_pemasok', 'LIKE', "%$keyword%")
                        ->orWhere('dpp', 'LIKE', "%$keyword%")
                        ->orWhere('ppn', 'LIKE', "%$keyword%")
                        ->orWhere('total', 'LIKE', "%$keyword%")
                        ->orWhere('bayar', 'LIKE', "%$keyword%")
                        ->orWhere('sisa', 'LIKE', "%$keyword%");
                    if ($type == "piutang giro" || $type == "hutang giro") {
                        $query->orWhere('tanggal', 'LIKE', "%$keyword%")
                            ->orWhere('saldo_transaksi.no_giro', 'LIKE', "%$keyword%");
                    }
                });
            }
            $filtered_data = $data_saldo->get();

            if ($sort) {
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
                        $data_saldo->orderBy($column, $directon);
                    }
                }
            } else {
                $data_saldo->orderBy('tanggal', 'ASC');
            }

            // Pagination
            if ($current_page) {
                $page = $current_page;
                $limit_data = $data_saldo->count();

                if ($limit) {
                    $limit_data = $limit;
                }

                $offset = ($page - 1) * $limit_data;
                if ($offset < 0) {
                    $offset = 0;
                }

                $data_saldo->skip($offset)->take($limit_data);
            }

            $table['draw'] = $draw;
            $table['recordsTotal'] = $data_saldo->count();
            $table['recordsFiltered'] = $filtered_data->count();
            $table['data'] = $data_saldo->get();
            return json_encode($table);

            // Get transaction saldo
            // switch ($type) {
            //     case 'penjualan':
            //         break;

            //     default:
            //         $result = NULL;
            //         break;
            // }
        } catch (\Exception $e) {
            Log::info("Error when get trx saldo data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "message" => "Error when get trx saldo data",
                "exception" => $e,
            ]);
        }
    }

    public function updateTrxSaldo($trx, $debet, $kredit)
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
                    $trx_saldo->status_giro = ($debet > $debet) ? 1 : 2;
                    break;
                case 'Hutang Giro Tolak':
                    $trx_saldo->bayar = $current_bayar + $debet;
                    $trx_saldo->sisa = $current_sisa - $debet;
                    $trx_saldo->status_giro = ($debet > $kredit) ? 1 : 2;
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
}
