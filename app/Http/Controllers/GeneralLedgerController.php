<?php

namespace App\Http\Controllers;

use App\Models\Accounting\GeneralLedger;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use App\Models\Master\Slip;
use Illuminate\Http\Request;
use DB;
use Log;
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
        $cabang = Cabang::find(1);
        $data_cabang = Cabang::all();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Umum | List",
            "cabang" => $cabang,
            "data_cabang" => $data_cabang
        ];

        if ($request->session()->has('token')) {
            return view('accounting.journal.general_ledger.index', $data);
        } else {
            return view('exceptions.forbidden');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data_cabang = Cabang::where("status_cabang", 1)->get();

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Umum | Create",
            // "data_akun" => $data_akun,
            "data_cabang" => $data_cabang,
            // "data_slip" => $data_slip
        ];

        Log::debug(json_encode($request->session()->get('user')));

        if ($request->session()->has('token')) {
            return view('accounting.journal.general_ledger.form', $data);
        } else {
            return view('exceptions.forbidden');
        }
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
                    "message" => "Error. There is no detail"
                ]);
            }

            // Init data
            $journalDate = date('Y-m-d', strtotime($request->header[0]["tanggal"]));
            $giroDate = ($request->header[0]["tanggal_giro"])?date('Y-m-d', strtotime($request->header[0]["tanggal_giro"])):NULL;
            $giroDueDate = ($request->header[0]["tanggal_jt_giro"])?date('Y-m-d', strtotime($request->header[0]["tanggal_jt_giro"])):NULL;
            $slipID = $request->header[0]["slip"];
            $journalType = $request->header[0]["jenis"];
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
            $header->id_slip = $slipID;
            $header->catatan = $noteHeader;
            $header->tanggal_giro = $giroDate;
            $header->tanggal_giro_jt = $giroDueDate;
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
                    "message" => "Error when store Jurnal data on table header"
                ]);
            }

            // Store Detail and Update
            foreach ($detailData as $data) {
                //Store Detail
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = ($data['guid'] == 'gen') ? count($detailData) + 1 : $data['guid'];
                $detail->id_akun = $data['akun'];
                $detail->keterangan = $data['notes'];
                $detail->debet = str_replace(',', '', $data['debet']);
                $detail->credit = str_replace(',', '', $data['kredit']);
                $detail->user_created = $userRecord;
                $detail->user_modified = $userModified;
                $detail->dt_created = $dateRecord;
                $detail->dt_modified = $dateRecord;
                // dd(json_encode($detail));
                if (!$detail->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail"
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
                "exception" => $e
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
        $data_jurnal_header = JurnalHeader::join('master_slip', 'master_slip.id_slip', 'jurnal_header.id_slip')
            ->join('cabang', 'cabang.id_cabang', 'jurnal_header.id_cabang')
            ->where('id_jurnal', $id)
            ->select('jurnal_header.*', 'cabang.kode_cabang', 'cabang.nama_cabang', 'master_slip.kode_slip', 'master_slip.nama_slip', DB::raw(
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

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Umum | Detail",
            "data_jurnal_header" => $data_jurnal_header,
            "data_jurnal_detail" => $data_jurnal_detail
        ];

        if ($request->session()->has('token')) {
            return view('accounting.journal.general_ledger.detail', $data);
        } else {
            return view('exceptions.forbidden');
        }
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

        $data = [
            "data_jurnal_header" => $data_jurnal_header,
            "data_jurnal_detail" => $data_jurnal_detail
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
        $data_cabang = Cabang::where("status_cabang", 1)->get();
        $jurnal_header = JurnalHeader::find($id);
        $jurnal_detail = JurnalDetail::where("id_jurnal", $id)->get();
        $details = [];
        $i = 0;
        foreach ($jurnal_detail as $key => $jurnal) {
            $akun = Akun::find($jurnal->id_akun);
            $details[] = [
                "guid" => (++$i == count($jurnal_detail)) ? "gen" : $jurnal->index,
                "akun" => $akun->id_akun,
                "nama_akun" => $akun->nama_akun,
                "kode_akun" => $akun->kode_akun,
                "notes" => $jurnal->keterangan,
                "debet" => $jurnal->debet,
                "kredit" => $jurnal->credit
            ];
        }

        $data = [
            "pageTitle" => "SCA Accounting | Transaksi Jurnal Umum | Edit",
            "data_cabang" => $data_cabang,
            "jurnal_header" => $jurnal_header,
            "jurnal_detail" => json_encode($details),
            "jurnal_detail_count" => count($details),
        ];
        // dd($details);

        Log::debug(json_encode($request->session()->get('user')));

        if ($request->session()->has('token')) {
            return view('accounting.journal.general_ledger.form_edit', $data);
        } else {
            return view('exceptions.forbidden');
        }
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
                    "message" => "Error. There is no detail"
                ]);
            }

            // Init data
            $journalDate = date('Y-m-d', strtotime($request->header[0]["tanggal"]));
            $giroDate = ($request->header[0]["tanggal_giro"])?date('Y-m-d', strtotime($request->header[0]["tanggal_giro"])):NULL;
            $giroDueDate = ($request->header[0]["tanggal_jt_giro"])?date('Y-m-d', strtotime($request->header[0]["tanggal_jt_giro"])):NULL;
            $journalID = $request->header[0]["id_jurnal"];
            $slipID = $request->header[0]["slip"];
            $journalType = $request->header[0]["jenis"];
            $cabangID = $request->header[0]["cabang"];
            $noteHeader = $request->header[0]["notes"];
            $userData = $request->session()->get('user');
            $userModified = $userData->id_pengguna;
            $dateModified = date('Y-m-d');
            $detailData = $request->detail;

            DB::beginTransaction();

            // Find Header data and delete detail
            $header = JurnalHeader::where("id_jurnal", $journalID)->first();
            JurnalDetail::where('id_jurnal', $journalID)->delete();

            // Store Header
            $header->id_cabang = $cabangID;
            $header->tanggal_jurnal = $journalDate;
            $header->jenis_jurnal = $journalType;
            $header->id_slip = $slipID;
            $header->catatan = $noteHeader;
            $header->tanggal_giro = $giroDate;
            $header->tanggal_giro_jt = $giroDueDate;
            $header->user_modified = $userModified;
            $header->dt_modified = $dateModified;
            $header->save();
            if (!$header->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when store Jurnal data on table header"
                ]);
            }

            // Store New Detail
            foreach ($detailData as $data) {
                //Store Detail
                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = ($data['guid'] == 'gen') ? count($detailData) + 1 : $data['guid'];
                $detail->id_akun = $data['akun'];
                $detail->keterangan = $data['notes'];
                $detail->debet = str_replace(',', '', $data['debet']);
                $detail->credit = str_replace(',', '', $data['kredit']);
                $detail->user_modified = $userModified;
                $detail->dt_modified = $dateModified;
                if (!$detail->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail"
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
                "exception" => $e
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
                'dir' => $sortDir
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

        Log::debug(json_encode($data_general_ledger_table->get()));

        if (!empty($keyword)) {
            $data_general_ledger->where(function ($query) use ($keyword) {
                $query->orWhere('kode_jurnal', 'LIKE', "%$keyword%")
                    ->orWhere('tanggal_jurnal', 'LIKE', "%$keyword%")
                    ->orWhere('jenis_name', 'LIKE', "%$keyword%")
                    ->orWhere('id_transaksi', 'LIKE', "%$keyword%")
                    ->orWhere('catatan', 'LIKE', "%$keyword%")
                    ->orWhere('jumlah', 'LIKE', "%$keyword%")
                    ->orWhere('kode_slip', 'LIKE', "%$keyword%");
            });
        }

        $filtered_data = $data_general_ledger_table->get();

        if ($sort) {
            if (!is_array($sort)) {
                $message = "Invalid array for parameter sort";
                $data = [
                    'result' => false,
                    'message' => $message
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
            $data_general_ledger_table->orderBy('id_jurnal', 'ASC');
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

            // Update Header Status
            $header->void = 1;
            $header->user_void = $userVoid;
            $header->dt_void = $dateVoid;
            if (!$header->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "message" => "Error when void Jurnal data"
                ]);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Successfully void Jurnal data",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when void Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "message" => "Error when void Jurnal data",
                "exception" => $e
            ]);
        }
    }

    public function active(Request $request, $id)
    {
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
                    "message" => "Error when activate Jurnal data"
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
                "exception" => $e
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
                    $max = (int)substr($check[0]->kode_jurnal, -4);
                    $max += 1;
                    $code = $prefix . "." . sprintf("%04s", $max);
                } 
                else {
                    $code = $prefix . ".0001";
                }
                $ex++;
                if ($ex >= 5) {
                    $code = "error";
                    break;
                }
            } while (JurnalHeader::where("kode_jurnal", $code)->first());
            return $code;
        } 
        catch (\Exception $e) {
            Log::error("Error when generate journal code");
        }
    }
}
