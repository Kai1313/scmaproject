<?php

namespace App\Http\Controllers;

use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
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
        if (checkUserSession($request, 'adjustment_ledger', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $cabang = Cabang::find(1);
        $data_cabang = Cabang::all();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Penyesuaian | List",
            "cabang" => $cabang,
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
        if (checkAccessMenu('adjustment_ledger', 'create') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $data_cabang = Cabang::where("status_cabang", 1)->get();
        $data_pelanggan = Pelanggan::all();
        $data_pemasok = Pemasok::all();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Penyesuaian | Create",
            "data_cabang" => $data_cabang,
            "data_pelanggan" => $data_pelanggan,
            "data_pemasok" => $data_pemasok,
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
            $journalDate = date('Y-m-d', strtotime($request->header[0]["tanggal"]));
            $journalType = "ME";
            $cabangID = $request->header[0]["cabang"];
            $noteHeader = $request->header[0]["notes"];
            $userData = $request->session()->get('user');
            $userRecord = $userData->id_pengguna;
            $userModified = $userData->id_pengguna;
            $dateRecord = date('Y-m-d');
            $detailData = $request->detail;
            // dd($detailData);

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
            $header->kode_jurnal = $this->generateJournalCode($cabangID, $journalType);
            // dd($header);
            if (!$header->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when store Jurnal data on table header",
                ]);
            }

            // Store Detail and Update
            foreach ($detailData as $key => $data) {
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
                    $tolak = (str_contains($data["notes"], "GIRO-TOLAK"))?TRUE:FALSE;
                    $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $debet, $kredit, $tolak);
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
        if (checkAccessMenu('adjustment_ledger', 'show') == false) {
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

        $data_jurnal_header->catatan = str_replace("\n", '<br/>', $data_jurnal_header->catatan);

        foreach ($data_jurnal_detail as $key => $value) {
            $notes = str_replace("\n", '<br/>', $value->keterangan);
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
        if (checkAccessMenu('adjustment_ledger', 'edit') == false) {
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
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Penyesuaian | Edit",
            "data_cabang" => $data_cabang,
            "data_pelanggan" => $data_pelanggan,
            "data_pemasok" => $data_pemasok,
            "jurnal_header" => $jurnal_header,
            "jurnal_detail" => json_encode($details),
            "jurnal_detail_count" => count($details),
        ];
        // dd($details);

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
            $journalDate = date('Y-m-d', strtotime($request->header[0]["tanggal"]));
            $journalID = $request->header[0]["id_jurnal"];
            $journalType = "ME";
            $cabangID = $request->header[0]["cabang"];
            $noteHeader = $request->header[0]["notes"];
            $userData = $request->session()->get('user');
            $userModified = $userData->id_pengguna;
            $dateModified = date('Y-m-d');
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
                    $tolak = (str_contains($data["notes"], "GIRO-TOLAK"))?TRUE:FALSE;
                    $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $debet, $kredit, $tolak);
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

        $data_general_ledger = JurnalHeader::select('jurnal_header.*', DB::raw('
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
            ->groupBy('jurnal_header.id_jurnal')
            ->select('jurnal_header.*', DB::raw('SUM(jurnal_detail.credit) as jumlah'));
        $data_general_ledger_table = $data_general_ledger_table->where('id_cabang', $cabang)->where('jenis_jurnal', 'ME');

        if (!empty($keyword)) {
            $data_general_ledger_table->where(function ($query) use ($keyword) {
                $query->orWhere('kode_jurnal', 'LIKE', "%$keyword%")
                    ->orWhere('tanggal_jurnal', 'LIKE', "%$keyword%")
                    ->orWhere('jenis_name', 'LIKE', "%$keyword%")
                    ->orWhere('jurnal_header.id_transaksi', 'LIKE', "%$keyword%")
                    ->orWhere('catatan', 'LIKE', "%$keyword%");
                // ->orWhere('jumlah', 'LIKE', "%$keyword%")
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
        if (checkAccessMenu('adjustment_ledger', 'edit') == false) {
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
            if (checkAccessMenu('adjustment_ledger', 'delete') == false) {
                return response()->json([
                    "result" => false,
                    "message" => "Maaf, tidak bisa void jurnal dengan id " . $id . ", anda tidak punya akses!",
                ]);
            }

            // $session = $request->session()->get('access');

            // if (($request->session()->has('token') && array_key_exists('Transaksi Jurnal Umum', $session)) && $session['Transaksi Jurnal Umum']['delete'] == 1) {

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
        if (checkAccessMenu('adjustment_ledger', 'edit') == false) {
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

    public function updateTrxSaldo($trx, $debet, $kredit, $tolak = FALSE)
    {
        try {
            // DB::beginTransaction();
            $trx_saldo = TrxSaldo::find($trx->id);
            $type = ($tolak)?$trx->tipe_transaksi." Tolak":$trx->tipe_transaksi;
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
            $jurnal_detail = ($jurnal_header)?JurnalDetail::where("id_jurnal", $jurnal_header->id_jurnal)->get():[];
            $details = [];
            $i = 0;
            foreach ($jurnal_detail as $key => $jurnal) {
                $akun = Akun::find($jurnal->id_akun);
                $trx_id = TrxSaldo::where("id_transaksi", $jurnal->id_transaksi)->first();
                Log::info("iterator ".$i);
                Log::info("count ".count($jurnal_detail));
                $details[] = [
                    "guid" => (++$i == count($jurnal_detail)) ? "gen" : (($trx_id) ? "trx-" . $trx_id->id : $jurnal->index),
                    "akun" => $akun->id_akun,
                    "nama_akun" => $akun->nama_akun,
                    "kode_akun" => $akun->kode_akun,
                    "notes" => ($i == count($jurnal_detail))?$jurnal->keterangan." - GIRO-TOLAK":$jurnal->keterangan,
                    "trx" => ($i == count($jurnal_detail))?$ids:$jurnal->id_transaksi,
                    // Dibalik karena giro tolak
                    "debet" => str_replace(".", ",", $jurnal->credit),
                    "kredit" => str_replace(".", ",", $jurnal->debet),
                ];
            }
            return response()->json([
                "result" => TRUE,
                "message" => "Successfully fetched giro tolak",
                "details" => $details
            ]);
        }
        catch (\Exception $e) {
            $message = "Error when get giro tolak record";
            Log::error($message);
            Log::error($e);
            return response()->json([
                "result" => FALSE,
                "message" => $message
            ]);
        }
    }
}
