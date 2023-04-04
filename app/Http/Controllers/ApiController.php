<?php

namespace App\Http\Controllers;

use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
use App\Models\Accounting\TrxSaldo;
use App\Models\Master\Cabang;
use App\Models\Master\Slip;
use App\Models\User;
use App\Models\UserToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $user_id = $request->id_pengguna;

        $user       = User::where('id_pengguna', $user_id)->first();
        $token      = UserToken::where('id_pengguna', $user_id)->where('status_token_pengguna', 1)->whereRaw("waktu_habis_token_pengguna > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", Carbon::now()->format('Y-m-d H:i:s'))->first();

        if ($token) {
            $token = $user->createToken('Token Passport User '. Carbon::now()->format('Y-m-d H:i:s') . '[' . $user->id_pengguna . '] ' .$user->nama_pengguna)->accessToken;
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Login Success",
                "token" => $token
            ], 200);
        } else {
            return response()->json([
                "result" => false,
                "code" => 401,
                "message" => "Error, User has no Authorization"
            ], 401);
        }
    }

    public function profile(Request $request)
    {
        return response()->json([
            'user' => Auth::guard('api')->user()
        ], 200);
    }

    public function logout(Request $request)
    {
        if (Auth::guard('api')->user()) {
            Auth::guard('api')->user()->tokens()->delete();
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Log Out Success"
            ], 200);
        } else {
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error, Log Out Failed"
            ], 401);
        }
    }

    public function journalUangMukaPenjualan(Request $request)
    {
        try {
            // init data
            // header
            $id_transaksi = $request->no_transaksi;
            $tanggal_jurnal = date('Y-m-d', strtotime($request->tanggal));
            $void = $request->void;
            $user_created = $request->user;
            $id_pelanggan = $request->pelanggan;
            $id_cabang = $request->cabang;
            $id_slip = $request->slip;

            $data_pelanggan = DB::table("pelanggan")->where('id_pelanggan', $id_pelanggan)->first();
            $nama_pelanggan = $data_pelanggan->nama_pelanggan;
            $catatan = 'Journal Otomatis Uang Muka Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan;

            $data_slip = Slip::find($id_slip);

            if ($data_slip->jenis_slip == 0) {
                $jurnal_type = 'KM';
                $jurnal_type_detail = 'Kas Masuk';
            } else if ($data_slip->jenis_slip == 1) {
                $jurnal_type = 'BM';
                $jurnal_type_detail = 'Bank Masuk';
            } else {
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error, please use slip Kas Masuk or Bank Masuk"
                ], 400);
            }

            // detail
            $data_akun_uang_muka_penjualan = DB::table('setting')->where('code', 'Uang Muka Penjualan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_uang_muka_penjualan)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Uang Muka Penjualan not found"
                ], 404);
            }

            $data_akun_ppn_keluaran = DB::table('setting')->where('code', 'PPN Keluaran')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_ppn_keluaran)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Keluaran not found"
                ], 404);
            }

            $akun_slip = $data_slip->id_akun;
            $akun_uang_muka_penjualan = $data_akun_uang_muka_penjualan->value2;
            $akun_ppn_keluaran = $data_akun_ppn_keluaran->value2;
            $total = $request->total;
            $uang_muka = $request->uang_muka;
            $nominal_ppn = $request->ppn;

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail = [
                [
                    'akun'          => $akun_slip,
                    'debet'         => $total,
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Uang Muka Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                ],
                [
                    'akun'          => $akun_uang_muka_penjualan,
                    'debet'         => 0,
                    'credit'        => $uang_muka,
                    'keterangan'    => 'Jurnal Otomatis Uang Muka Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                ],
                [
                    'akun'          => $akun_ppn_keluaran,
                    'debet'         => 0,
                    'credit'        => $nominal_ppn,
                    'keterangan'    => 'Jurnal Otomatis PPN Keluaran - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                ],
            ];

            // Find Header data and delete detail
            $header = JurnalHeader::where("id_transaksi", $id_transaksi)->where('void', 0)->first();

            // Begin save
            DB::beginTransaction();
            if (!empty($header) && $header->id_slip == $id_slip) {
                JurnalDetail::where('id_jurnal', $header->id_jurnal)->delete();
                $header->id_cabang = $id_cabang;
                $header->tanggal_jurnal = $tanggal_jurnal;
                $header->void = $void;
                $header->catatan = $catatan;
                $header->user_modified = $user_created;
                $header->dt_modified = date('Y-m-d h:i:s');
            } else {
                if (!empty($header) && $header->id_slip != $id_slip) {
                    $header->void = 1;
                    $header->save();
                }
                $header = new JurnalHeader();
                $header->id_cabang = $id_cabang;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $jurnal_type, $id_slip);
                $header->id_transaksi = $id_transaksi;
                $header->id_slip = $id_slip;
                $header->jenis_jurnal = $jurnal_type;
                $header->tanggal_jurnal = $tanggal_jurnal;
                $header->void = $void;
                $header->catatan = $catatan;
                $header->user_created = $user_created;
                $header->dt_created = date('Y-m-d h:i:s');
                $header->user_modified = $user_created;
                $header->dt_created = date('Y-m-d h:i:s');
            }

            if (!$header->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table header"
                ], 404);
            }

            if (!empty($jurnal_detail)) {
                $index = 1;
                foreach ($jurnal_detail as $jd) {
                    if (($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)) {
                        $detail = new JurnalDetail();
                        $detail->id_jurnal = $header->id_jurnal;
                        $detail->index = $index;
                        $detail->id_akun = $jd['akun'];
                        $detail->debet = $jd['debet'];
                        $detail->credit = $jd['credit'];
                        $detail->keterangan = $jd['keterangan'];
                        $detail->user_created = $user_created;
                        $detail->dt_created = date('Y-m-d h:i:s');
                        $detail->user_modified = $user_created;
                        $detail->dt_modified = date('Y-m-d h:i:s');

                        // variable check
                        $check_balance_debit += $jd['debet'];
                        $check_balance_credit += $jd['credit'];

                        if (!$detail->save()) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "code" => 400,
                                "message" => "Error when store Jurnal data on table detail"
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            // check balance
            if($check_balance_debit != $check_balance_credit){
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance"
                ], 400);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully stored Jurnal data",
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when store Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal data",
                "exception" => $e
            ], 400);
        }
    }

    public function journalUangMukaPembelian(Request $request)
    {
        try {
            // init data
            // header
            $id_transaksi = $request->no_transaksi;
            $tanggal_jurnal = date('Y-m-d', strtotime($request->tanggal));
            $void = $request->void;
            $user_created = $request->user;
            $id_pemasok = $request->pemasok;
            $id_cabang = $request->cabang;
            $id_slip = $request->slip;

            $data_pemasok = DB::table("pemasok")->where('id_pemasok', $id_pemasok)->first();
            $nama_pemasok = $data_pemasok->nama_pemasok;
            $catatan = 'Journal Otomatis Uang Muka Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok;

            $data_slip = Slip::find($id_slip);

            if ($data_slip->jenis_slip == 0) {
                $jurnal_type = 'KK';
                $jurnal_type_detail = 'Kas Keluar';
            } else if ($data_slip->jenis_slip == 1) {
                $jurnal_type = 'BK';
                $jurnal_type_detail = 'Bank Keluar';
            } else {
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error, please use slip Kas Keluar or Bank Keluar"
                ], 400);
            }

            // detail
            $data_akun_uang_muka_pembelian = DB::table('setting')->where('code', 'Uang Muka Pembelian')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_uang_muka_pembelian)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Uang Muka Pembelian not found"
                ], 404);
            }

            $data_akun_ppn_masukan = DB::table('setting')->where('code', 'PPN Masukkan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_ppn_masukan)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Masukkan not found"
                ], 404);
            }

            $akun_slip = $data_slip->id_akun;
            $akun_uang_muka_pembelian = $data_akun_uang_muka_pembelian->value2;
            $akun_ppn_masukan = $data_akun_ppn_masukan->value2;
            $total = $request->total;
            $uang_muka = $request->uang_muka;
            $nominal_ppn = $request->ppn;

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail = [
                [
                    'akun'          => $akun_slip,
                    'debet'         => 0,
                    'credit'        => $total,
                    'keterangan'    => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Uang Muka Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok,
                ],
                [
                    'akun'          => $akun_uang_muka_pembelian,
                    'debet'         => $uang_muka,
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis Uang Muka Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok,
                ],
                [
                    'akun'          => $akun_ppn_masukan,
                    'debet'         => $nominal_ppn,
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis PPN Masukkan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                ],
            ];

            // Find Header data and delete detail
            $header = JurnalHeader::where("id_transaksi", $id_transaksi)->where('void', 0)->first();

            // Begin save
            DB::beginTransaction();
            if (!empty($header) && $header->id_slip == $id_slip) {
                JurnalDetail::where('id_jurnal', $header->id_jurnal)->delete();
                $header->id_cabang = $id_cabang;
                $header->tanggal_jurnal = $tanggal_jurnal;
                $header->void = $void;
                $header->catatan = $catatan;
                $header->user_modified = $user_created;
                $header->dt_modified = date('Y-m-d h:i:s');
            } else {
                if (!empty($header) && $header->id_slip != $id_slip) {
                    $header->void = 1;
                    $header->save();
                }
                $header = new JurnalHeader();
                $header->id_cabang = $id_cabang;
                $header->kode_jurnal = $this->generateJournalCode($id_cabang, $jurnal_type, $id_slip);
                $header->id_transaksi = $id_transaksi;
                $header->id_slip = $id_slip;
                $header->jenis_jurnal = $jurnal_type;
                $header->tanggal_jurnal = $tanggal_jurnal;
                $header->void = $void;
                $header->catatan = $catatan;
                $header->user_created = $user_created;
                $header->dt_created = date('Y-m-d h:i:s');
                $header->user_modified = $user_created;
                $header->dt_created = date('Y-m-d h:i:s');
            }

            if (!$header->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table header"
                ], 400);
            }

            if (!empty($jurnal_detail)) {
                $index = 1;
                foreach ($jurnal_detail as $jd) {
                    if (($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)) {
                        $detail = new JurnalDetail();
                        $detail->id_jurnal = $header->id_jurnal;
                        $detail->index = $index;
                        $detail->id_akun = $jd['akun'];
                        $detail->debet = $jd['debet'];
                        $detail->credit = $jd['credit'];
                        $detail->keterangan = $jd['keterangan'];
                        $detail->user_created = $user_created;
                        $detail->dt_created = date('Y-m-d h:i:s');
                        $detail->user_modified = $user_created;
                        $detail->dt_modified = date('Y-m-d h:i:s');

                        // variable check
                        $check_balance_debit += $jd['debet'];
                        $check_balance_credit += $jd['credit'];

                        if (!$detail->save()) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "code" => 400,
                                "message" => "Error when store Jurnal data on table detail"
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            // check balance
            if($check_balance_debit != $check_balance_credit){
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance"
                ], 400);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully stored Jurnal data",
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when store Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal data",
                "exception" => $e
            ], 400);
        }
    }

    public function journalPenjualan(Request $request)
    {
        try {
            // init data
            // header
            $id_transaksi = $request->no_transaksi;
            $tanggal_jurnal = date('Y-m-d', strtotime($request->tanggal));
            $void = $request->void;
            $user_created = $request->user;
            $id_pelanggan = $request->pelanggan;
            $id_cabang = $request->cabang;
            $id_slip = $request->slip;
            $detail_inventory = array_values($request->detail);

            $data_pelanggan = DB::table("pelanggan")->where('id_pelanggan', $id_pelanggan)->first();
            $nama_pelanggan = $data_pelanggan->nama_pelanggan;
            $catatan_me = 'Journal Otomatis Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan;

            // init setting
            $data_akun_piutang_dagang = DB::table('setting')->where('code', 'Piutang Dagang')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_piutang_dagang)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Piutang Dagang not found"
                ], 404);
            }

            $data_akun_uang_muka_penjualan = DB::table('setting')->where('code', 'Uang Muka Penjualan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_uang_muka_penjualan)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Uang Muka Penjualan not found"
                ], 404);
            }

            $data_akun_ppn_keluaran = DB::table('setting')->where('code', 'PPN Keluaran')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_ppn_keluaran)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Keluaran not found"
                ], 404);
            }

            $data_akun_penjualan = DB::table('setting')->where('code', 'Penjualan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_penjualan)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Penjualan not found"
                ], 404);
            }

            // cek apakah ada saldo_transaksi
            $check_trx_saldo = TrxSaldo::where("id_transaksi", $id_transaksi)->first();
            if(empty($check_trx_saldo)){
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error saldo transaksi belum ada"
                ], 404);
            }


            // detail
            // Memorial
            $akun_piutang_dagang = $data_akun_piutang_dagang->value2;
            $akun_uang_muka_penjualan = $data_akun_uang_muka_penjualan->value2;
            $akun_ppn_keluaran = $data_akun_ppn_keluaran->value2;
            $akun_penjualan = $data_akun_penjualan->value2;
            $total = $request->total;
            $uang_muka = $request->uang_muka;
            $nominal_ppn = $request->ppn;

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail_me = [
                [
                    'akun'          => $akun_uang_muka_penjualan,
                    'debet'         => $uang_muka,
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis Uang Muka Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi'  => null
                ],
                [
                    'akun'          => $akun_piutang_dagang,
                    'debet'         => 0,
                    'credit'        => $uang_muka,
                    'keterangan'    => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi'  => $id_transaksi
                ],
                [
                    'akun'          => $akun_piutang_dagang,
                    'debet'         => ($total + $uang_muka),
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis Penjualan ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi'  => null
                ],
                [
                    'akun'          => $akun_ppn_keluaran,
                    'debet'         => 0,
                    'credit'        => $nominal_ppn,
                    'keterangan'    => 'Jurnal Otomatis PPN Keluaran - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi'  => null
                ],
            ];

            foreach($detail_inventory as $d_inv){
                array_push($jurnal_detail_me, [
                    'akun'          => $akun_penjualan,
                    'debet'         => 0,
                    'credit'        => $d_inv['total'],
                    'keterangan'    => 'Jurnal Otomatis Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan . ' - ' . $d_inv['nama_barang'],
                    'id_transaksi'  => null
                ]);
            }

            // Find Header data and delete detail
            $header_me = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', 'ME')->where('void', 0)->first();

            // Begin save
            DB::beginTransaction();
            if (!empty($header_me)) {
                JurnalDetail::where('id_jurnal', $header_me->id_jurnal)->delete();
                $header_me->id_cabang = $id_cabang;
                $header_me->tanggal_jurnal = $tanggal_jurnal;
                $header_me->void = $void;
                $header_me->catatan = $catatan_me;
                $header_me->user_modified = $user_created;
                $header_me->dt_modified = date('Y-m-d h:i:s');
            } else {
                $header_me = new JurnalHeader();
                $header_me->id_cabang = $id_cabang;
                $header_me->kode_jurnal = $this->generateJournalCode($id_cabang, 'ME');
                $header_me->id_transaksi = $id_transaksi;
                $header_me->jenis_jurnal = 'ME';
                $header_me->tanggal_jurnal = $tanggal_jurnal;
                $header_me->void = $void;
                $header_me->catatan = $catatan_me;
                $header_me->user_created = $user_created;
                $header_me->dt_created = date('Y-m-d h:i:s');
                $header_me->user_modified = $user_created;
                $header_me->dt_created = date('Y-m-d h:i:s');
            }

            if (!$header_me->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table header"
                ], 400);
            }

            if (!empty($jurnal_detail_me)) {
                $index = 1;
                foreach ($jurnal_detail_me as $jd) {
                    if(($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)){
                        $detail_me = new JurnalDetail();
                        $detail_me->id_jurnal = $header_me->id_jurnal;
                        $detail_me->index = $index;
                        $detail_me->id_akun = $jd['akun'];
                        $detail_me->debet = $jd['debet'];
                        $detail_me->credit = $jd['credit'];
                        $detail_me->keterangan = $jd['keterangan'];
                        $detail_me->id_transaksi = $jd['id_transaksi'];
                        $detail_me->user_created = $user_created;
                        $detail_me->dt_created = date('Y-m-d h:i:s');
                        $detail_me->user_modified = $user_created;
                        $detail_me->dt_modified = date('Y-m-d h:i:s');

                        // variable check
                        $check_balance_debit += $jd['debet'];
                        $check_balance_credit += $jd['credit'];

                        if (!$detail_me->save()) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "code" => 400,
                                "message" => "Error when store Jurnal data on table detail"
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            // check balance
            if($check_balance_debit != $check_balance_credit){
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance"
                ], 400);
            }

            if($id_slip != null){
                // init slip
                $data_slip = Slip::find($id_slip);

                if ($data_slip->jenis_slip == 0) {
                    $jurnal_type = 'KM';
                    $jurnal_type_detail = 'Kas Masuk';
                } else if ($data_slip->jenis_slip == 1) {
                    $jurnal_type = 'BM';
                    $jurnal_type_detail = 'Bank Masuk';
                } else {
                    return response()->json([
                        "result" => false,
                        "code" => 400,
                        "message" => "Error, please use slip Kas Masuk or Bank Masuk"
                    ], 400);
                }

                $catatan_pelunasan = 'Journal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan;

                $akun_slip = $data_slip->id_akun;

                $jurnal_detail_pelunasan = [
                    [
                        'akun'          => $akun_slip,
                        'debet'         => $total,
                        'credit'        => 0,
                        'keterangan'    => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                        'id_transaksi'  => null
                    ],
                    [
                        'akun'          => $akun_piutang_dagang,
                        'debet'         => 0,
                        'credit'        => $total,
                        'keterangan'    => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                        'id_transaksi'  => $id_transaksi
                    ]
                ];

                // Find Header data and delete detail
                $header = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', '<>', 'ME')->where('void', 0)->first();

                if (!empty($header) && $header->id_slip == $id_slip) {
                    JurnalDetail::where('id_jurnal', $header->id_jurnal)->delete();
                    $header->id_cabang = $id_cabang;
                    $header->tanggal_jurnal = $tanggal_jurnal;
                    $header->id_slip = $id_slip;
                    $header->void = $void;
                    $header->catatan = $catatan_pelunasan;
                    $header->user_modified = $user_created;
                    $header->dt_modified = date('Y-m-d h:i:s');
                } else {
                    if(!empty($header) && $header->id_slip != $id_slip){
                        $header->void = 1;
                        $header->user_void = $user_created;
                        $header->dt_void = date('Y-m-d h:i:s');
                        $header->save();
                    }
                    $header = new JurnalHeader();
                    $header->id_cabang = $id_cabang;
                    $header->kode_jurnal = $this->generateJournalCode($id_cabang, $jurnal_type, $id_slip);
                    $header->id_transaksi = $id_transaksi;
                    $header->jenis_jurnal = $jurnal_type;
                    $header->id_slip = $id_slip;
                    $header->tanggal_jurnal = $tanggal_jurnal;
                    $header->void = $void;
                    $header->catatan = $catatan_pelunasan;
                    $header->user_created = $user_created;
                    $header->dt_created = date('Y-m-d h:i:s');
                    $header->user_modified = $user_created;
                    $header->dt_created = date('Y-m-d h:i:s');
                }

                if (!$header->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "code" => 400,
                        "message" => "Error when store Jurnal data on table header"
                    ], 400);
                }

                if (!empty($jurnal_detail_pelunasan)) {
                    $index = 1;
                    foreach ($jurnal_detail_pelunasan as $jd) {
                        if(($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)){
                            $detail = new JurnalDetail();
                            $detail->id_jurnal = $header->id_jurnal;
                            $detail->index = $index;
                            $detail->id_akun = $jd['akun'];
                            $detail->debet = $jd['debet'];
                            $detail->credit = $jd['credit'];
                            $detail->keterangan = $jd['keterangan'];
                            $detail->id_transaksi = $jd['id_transaksi'];
                            $detail->user_created = $user_created;
                            $detail->dt_created = date('Y-m-d h:i:s');
                            $detail->user_modified = $user_created;
                            $detail->dt_modified = date('Y-m-d h:i:s');

                            if (!$detail->save()) {
                                DB::rollback();
                                return response()->json([
                                    "result" => false,
                                    "code" => 400,
                                    "message" => "Error when store Jurnal data on table detail"
                                ], 400);
                            }

                            //  Update Saldo Transaksi
                            $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                            if ($trx_saldo) {
                                // cek untuk revert
                                if($trx_saldo->bayar > 0){
                                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                }

                                // update
                                $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                                $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                if (!$update_trx_saldo) {
                                    DB::rollback();
                                    return response()->json([
                                        "result" => false,
                                        "message" => "Error when store Jurnal data on update saldo transaksi"
                                    ]);
                                }
                            }

                            $index++;
                        }
                    }
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully stored Jurnal data",
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when store Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal data",
                "exception" => $e
            ], 400);
        }
    }

    public function journalPembelian(Request $request)
    {
        try {
            // init data
            // header
            $id_transaksi = $request->no_transaksi;
            $tanggal_jurnal = date('Y-m-d', strtotime($request->tanggal));
            $void = $request->void;
            $user_created = $request->user;
            $id_pemasok= $request->pemasok;
            $id_cabang = $request->cabang;
            $id_slip = $request->slip;
            $detail_inventory = array_values($request->detail);

            $data_pemasok = DB::table("pemasok")->where('id_pemasok', $id_pemasok)->first();
            $nama_pemasok = $data_pemasok->nama_pemasok;
            $catatan_me = 'Journal Otomatis Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok;

            // init setting
            $data_akun_hutang_dagang = DB::table('setting')->where('code', 'Hutang Dagang')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_hutang_dagang)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Hutang Dagang not found"
                ], 404);
            }

            $data_akun_uang_muka_pembelian = DB::table('setting')->where('code', 'Uang Muka Pembelian')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_uang_muka_pembelian)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Uang Muka Pembelian not found"
                ], 404);
            }

            $data_akun_ppn_masukkan = DB::table('setting')->where('code', 'PPN Masukkan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_ppn_masukkan)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Masukkan not found"
                ], 404);
            }

            // cek apakah ada saldo_transaksi
            $check_trx_saldo = TrxSaldo::where("id_transaksi", $id_transaksi)->first();
            if(empty($check_trx_saldo)){
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error saldo transaksi belum ada"
                ], 404);
            }


            // detail
            // Memorial
            $akun_hutang_dagang = $data_akun_hutang_dagang->value2;
            $akun_uang_muka_pembelian = $data_akun_uang_muka_pembelian->value2;
            $akun_ppn_masukkan = $data_akun_ppn_masukkan->value2;
            $total = $request->total;
            $uang_muka = $request->uang_muka;
            $nominal_ppn = $request->ppn;

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail_me = [
                [
                    'akun'          => $akun_uang_muka_pembelian,
                    'debet'         => 0,
                    'credit'        => $uang_muka,
                    'keterangan'    => 'Jurnal Otomatis Uang Muka Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi'  => null
                ],
                [
                    'akun'          => $akun_hutang_dagang,
                    'debet'         => $uang_muka,
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi'  => $id_transaksi
                ],
                [
                    'akun'          => $akun_hutang_dagang,
                    'debet'         => 0,
                    'credit'        => ($total + $uang_muka),
                    'keterangan'    => 'Jurnal Otomatis Pembelian ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi'  => null
                ],
                [
                    'akun'          => $akun_ppn_masukkan,
                    'debet'         => $nominal_ppn,
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis PPN Masukkan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi'  => null
                ],
            ];

            foreach($detail_inventory as $d_inv){
                array_push($jurnal_detail_me, [
                    'akun'          => $d_inv['akun_id'],
                    'debet'         => $d_inv['total'],
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis Pembelian Persediaan - ' . $id_transaksi . ' - ' . $nama_pemasok . ' - ' . $d_inv['nama_barang'],
                    'id_transaksi'  => null
                ]);
            }

            // Find Header data and delete detail
            $header_me = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', 'ME')->where('void', 0)->first();

            // Begin save
            DB::beginTransaction();
            if (!empty($header_me)) {
                JurnalDetail::where('id_jurnal', $header_me->id_jurnal)->delete();
                $header_me->id_cabang = $id_cabang;
                $header_me->tanggal_jurnal = $tanggal_jurnal;
                $header_me->void = $void;
                $header_me->catatan = $catatan_me;
                $header_me->user_modified = $user_created;
                $header_me->dt_modified = date('Y-m-d h:i:s');
            } else {
                $header_me = new JurnalHeader();
                $header_me->id_cabang = $id_cabang;
                $header_me->kode_jurnal = $this->generateJournalCode($id_cabang, 'ME');
                $header_me->id_transaksi = $id_transaksi;
                $header_me->jenis_jurnal = 'ME';
                $header_me->tanggal_jurnal = $tanggal_jurnal;
                $header_me->void = $void;
                $header_me->catatan = $catatan_me;
                $header_me->user_created = $user_created;
                $header_me->dt_created = date('Y-m-d h:i:s');
                $header_me->user_modified = $user_created;
                $header_me->dt_created = date('Y-m-d h:i:s');
            }

            if (!$header_me->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table header"
                ], 400);
            }

            if (!empty($jurnal_detail_me)) {
                $index = 1;
                foreach ($jurnal_detail_me as $jd) {
                    if(($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)){
                        $detail_me = new JurnalDetail();
                        $detail_me->id_jurnal = $header_me->id_jurnal;
                        $detail_me->index = $index;
                        $detail_me->id_akun = $jd['akun'];
                        $detail_me->debet = $jd['debet'];
                        $detail_me->credit = $jd['credit'];
                        $detail_me->keterangan = $jd['keterangan'];
                        $detail_me->id_transaksi = $jd['id_transaksi'];
                        $detail_me->user_created = $user_created;
                        $detail_me->dt_created = date('Y-m-d h:i:s');
                        $detail_me->user_modified = $user_created;
                        $detail_me->dt_modified = date('Y-m-d h:i:s');

                        // variable check
                        $check_balance_debit += $jd['debet'];
                        $check_balance_credit += $jd['credit'];

                        if (!$detail_me->save()) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "code" => 400,
                                "message" => "Error when store Jurnal data on table detail"
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            // check balance
            if($check_balance_debit != $check_balance_credit){
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance"
                ], 400);
            }

            if($id_slip != null){
                // init slip
                $data_slip = Slip::find($id_slip);

                if ($data_slip->jenis_slip == 0) {
                    $jurnal_type = 'KK';
                    $jurnal_type_detail = 'Kas Keluar';
                } else if ($data_slip->jenis_slip == 1) {
                    $jurnal_type = 'BK';
                    $jurnal_type_detail = 'Bank Keluar';
                } else {
                    return response()->json([
                        "result" => false,
                        "code" => 400,
                        "message" => "Error, please use slip Kas Keluar or Bank Keluar"
                    ], 400);
                }

                $catatan_pelunasan = 'Journal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok;

                $akun_slip = $data_slip->id_akun;

                $jurnal_detail_pelunasan = [
                    [
                        'akun'          => $akun_slip,
                        'debet'         => 0,
                        'credit'        => $total,
                        'keterangan'    => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                        'id_transaksi'  => null
                    ],
                    [
                        'akun'          => $akun_hutang_dagang,
                        'debet'         => $total,
                        'credit'        => 0,
                        'keterangan'    => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                        'id_transaksi'  => $id_transaksi
                    ]
                ];

                // Find Header data and delete detail
                $header = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', '<>', 'ME')->where('void', 0)->first();

                if (!empty($header) && $header->id_slip == $id_slip) {
                    JurnalDetail::where('id_jurnal', $header->id_jurnal)->delete();
                    $header->id_cabang = $id_cabang;
                    $header->tanggal_jurnal = $tanggal_jurnal;
                    $header->id_slip = $id_slip;
                    $header->void = $void;
                    $header->catatan = $catatan_pelunasan;
                    $header->user_modified = $user_created;
                    $header->dt_modified = date('Y-m-d h:i:s');
                } else {
                    if(!empty($header) && $header->id_slip != $id_slip){
                        $header->void = 1;
                        $header->user_void = $user_created;
                        $header->dt_void = date('Y-m-d h:i:s');
                        $header->save();
                    }
                    $header = new JurnalHeader();
                    $header->id_cabang = $id_cabang;
                    $header->kode_jurnal = $this->generateJournalCode($id_cabang, $jurnal_type, $id_slip);
                    $header->id_transaksi = $id_transaksi;
                    $header->jenis_jurnal = $jurnal_type;
                    $header->id_slip = $id_slip;
                    $header->tanggal_jurnal = $tanggal_jurnal;
                    $header->void = $void;
                    $header->catatan = $catatan_pelunasan;
                    $header->user_created = $user_created;
                    $header->dt_created = date('Y-m-d h:i:s');
                    $header->user_modified = $user_created;
                    $header->dt_created = date('Y-m-d h:i:s');
                }

                if (!$header->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "code" => 400,
                        "message" => "Error when store Jurnal data on table header"
                    ], 400);
                }

                if (!empty($jurnal_detail_pelunasan)) {
                    $index = 1;
                    foreach ($jurnal_detail_pelunasan as $jd) {
                        if(($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)){
                            $detail = new JurnalDetail();
                            $detail->id_jurnal = $header->id_jurnal;
                            $detail->index = $index;
                            $detail->id_akun = $jd['akun'];
                            $detail->debet = $jd['debet'];
                            $detail->credit = $jd['credit'];
                            $detail->keterangan = $jd['keterangan'];
                            $detail->id_transaksi = $jd['id_transaksi'];
                            $detail->user_created = $user_created;
                            $detail->dt_created = date('Y-m-d h:i:s');
                            $detail->user_modified = $user_created;
                            $detail->dt_modified = date('Y-m-d h:i:s');

                            if (!$detail->save()) {
                                DB::rollback();
                                return response()->json([
                                    "result" => false,
                                    "code" => 400,
                                    "message" => "Error when store Jurnal data on table detail"
                                ], 400);
                            }

                            //  Update Saldo Transaksi
                            $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                            if ($trx_saldo) {
                                // cek untuk revert
                                if($trx_saldo->bayar > 0){
                                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                }

                                // update
                                $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                                $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                if (!$update_trx_saldo) {
                                    DB::rollback();
                                    return response()->json([
                                        "result" => false,
                                        "message" => "Error when store Jurnal data on update saldo transaksi"
                                    ]);
                                }
                            }

                            $index++;
                        }
                    }
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully stored Jurnal data",
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when store Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal data",
                "exception" => $e
            ], 400);
        }
    }

    public function journalReturPenjualan(Request $request)
    {
        try {
            // init data
            // header
            $id_transaksi = $request->no_transaksi;
            $tanggal_jurnal = date('Y-m-d', strtotime($request->tanggal));
            $void = $request->void;
            $user_created = $request->user;
            $id_pelanggan = $request->pelanggan;
            $id_cabang = $request->cabang;
            $id_slip = $request->slip;
            $detail_inventory = array_values($request->detail);

            $data_pelanggan = DB::table("pelanggan")->where('id_pelanggan', $id_pelanggan)->first();
            $nama_pelanggan = $data_pelanggan->nama_pelanggan;
            $catatan_me = 'Journal Otomatis Retur Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan;

            // init setting
            $data_akun_piutang_dagang = DB::table('setting')->where('code', 'Piutang Dagang')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_piutang_dagang)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Piutang Dagang not found"
                ], 404);
            }

            $data_akun_ppn_keluaran = DB::table('setting')->where('code', 'PPN Keluaran')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_ppn_keluaran)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Keluaran not found"
                ], 404);
            }

            $data_akun_penjualan = DB::table('setting')->where('code', 'Penjualan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_penjualan)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Penjualan not found"
                ], 404);
            }

            // cek apakah ada saldo_transaksi
            $check_trx_saldo = TrxSaldo::where("id_transaksi", $id_transaksi)->first();
            if(empty($check_trx_saldo)){
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error saldo transaksi belum ada"
                ], 404);
            }

            // detail
            // Memorial
            $akun_piutang_dagang = $data_akun_piutang_dagang->value2;
            $akun_ppn_keluaran = $data_akun_ppn_keluaran->value2;
            $akun_penjualan = $data_akun_penjualan->value2;
            $total = $request->total;
            $nominal_ppn = $request->ppn;

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail_me = [
                [
                    'akun'          => $akun_piutang_dagang,
                    'debet'         => 0,
                    'credit'        => $total,
                    'keterangan'    => 'Jurnal Otomatis Retur Penjualan ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi'  => null
                ],
                [
                    'akun'          => $akun_ppn_keluaran,
                    'debet'         => $nominal_ppn,
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis PPN Keluaran - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi'  => null
                ],
            ];

            foreach($detail_inventory as $d_inv){
                array_push($jurnal_detail_me, [
                    'akun'          => $akun_penjualan,
                    'debet'         => $d_inv['total'],
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis Retur Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan . ' - ' . $d_inv['nama_barang'],
                    'id_transaksi'  => null
                ]);
            }

            // Find Header data and delete detail
            $header_me = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', 'ME')->where('void', 0)->first();

            // Begin save
            DB::beginTransaction();
            if (!empty($header_me)) {
                JurnalDetail::where('id_jurnal', $header_me->id_jurnal)->delete();
                $header_me->id_cabang = $id_cabang;
                $header_me->tanggal_jurnal = $tanggal_jurnal;
                $header_me->void = $void;
                $header_me->catatan = $catatan_me;
                $header_me->user_modified = $user_created;
                $header_me->dt_modified = date('Y-m-d h:i:s');
            } else {
                $header_me = new JurnalHeader();
                $header_me->id_cabang = $id_cabang;
                $header_me->kode_jurnal = $this->generateJournalCode($id_cabang, 'ME');
                $header_me->id_transaksi = $id_transaksi;
                $header_me->jenis_jurnal = 'ME';
                $header_me->tanggal_jurnal = $tanggal_jurnal;
                $header_me->void = $void;
                $header_me->catatan = $catatan_me;
                $header_me->user_created = $user_created;
                $header_me->dt_created = date('Y-m-d h:i:s');
                $header_me->user_modified = $user_created;
                $header_me->dt_created = date('Y-m-d h:i:s');
            }

            if (!$header_me->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table header"
                ], 400);
            }

            if (!empty($jurnal_detail_me)) {
                $index = 1;
                foreach ($jurnal_detail_me as $jd) {
                    if(($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)){
                        $detail_me = new JurnalDetail();
                        $detail_me->id_jurnal = $header_me->id_jurnal;
                        $detail_me->index = $index;
                        $detail_me->id_akun = $jd['akun'];
                        $detail_me->debet = $jd['debet'];
                        $detail_me->credit = $jd['credit'];
                        $detail_me->keterangan = $jd['keterangan'];
                        $detail_me->id_transaksi = $jd['id_transaksi'];
                        $detail_me->user_created = $user_created;
                        $detail_me->dt_created = date('Y-m-d h:i:s');
                        $detail_me->user_modified = $user_created;
                        $detail_me->dt_modified = date('Y-m-d h:i:s');

                        // variable check
                        $check_balance_debit += $jd['debet'];
                        $check_balance_credit += $jd['credit'];

                        if (!$detail_me->save()) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "code" => 400,
                                "message" => "Error when store Jurnal data on table detail"
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            // check balance
            if($check_balance_debit != $check_balance_credit){
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance"
                ], 400);
            }

            if($id_slip != null){
                // init slip
                $data_slip = Slip::find($id_slip);

                if ($data_slip->jenis_slip == 0) {
                    $jurnal_type = 'KM';
                    $jurnal_type_detail = 'Kas Masuk';
                } else if ($data_slip->jenis_slip == 1) {
                    $jurnal_type = 'BM';
                    $jurnal_type_detail = 'Bank Masuk';
                } else {
                    return response()->json([
                        "result" => false,
                        "code" => 400,
                        "message" => "Error, please use slip Kas Masuk or Bank Masuk"
                    ], 400);
                }

                $catatan_pelunasan = 'Journal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan;

                $akun_slip = $data_slip->id_akun;

                $jurnal_detail_pelunasan = [
                    [
                        'akun'          => $akun_slip,
                        'debet'         => 0,
                        'credit'        => $total,
                        'keterangan'    => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                        'id_transaksi'  => null
                    ],
                    [
                        'akun'          => $akun_piutang_dagang,
                        'debet'         => $total,
                        'credit'        => 0,
                        'keterangan'    => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                        'id_transaksi'  => $id_transaksi
                    ]
                ];

                // Find Header data and delete detail
                $header = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', '<>', 'ME')->where('void', 0)->first();

                if (!empty($header) && $header->id_slip == $id_slip) {
                    JurnalDetail::where('id_jurnal', $header->id_jurnal)->delete();
                    $header->id_cabang = $id_cabang;
                    $header->tanggal_jurnal = $tanggal_jurnal;
                    $header->id_slip = $id_slip;
                    $header->void = $void;
                    $header->catatan = $catatan_pelunasan;
                    $header->user_modified = $user_created;
                    $header->dt_modified = date('Y-m-d h:i:s');
                } else {
                    if(!empty($header) && $header->id_slip != $id_slip){
                        $header->void = 1;
                        $header->user_void = $user_created;
                        $header->dt_void = date('Y-m-d h:i:s');
                        $header->save();
                    }
                    $header = new JurnalHeader();
                    $header->id_cabang = $id_cabang;
                    $header->kode_jurnal = $this->generateJournalCode($id_cabang, $jurnal_type, $id_slip);
                    $header->id_transaksi = $id_transaksi;
                    $header->jenis_jurnal = $jurnal_type;
                    $header->id_slip = $id_slip;
                    $header->tanggal_jurnal = $tanggal_jurnal;
                    $header->void = $void;
                    $header->catatan = $catatan_pelunasan;
                    $header->user_created = $user_created;
                    $header->dt_created = date('Y-m-d h:i:s');
                    $header->user_modified = $user_created;
                    $header->dt_created = date('Y-m-d h:i:s');
                }

                if (!$header->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "code" => 400,
                        "message" => "Error when store Jurnal data on table header"
                    ], 400);
                }

                if (!empty($jurnal_detail_pelunasan)) {
                    $index = 1;
                    foreach ($jurnal_detail_pelunasan as $jd) {
                        if(($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)){
                            $detail = new JurnalDetail();
                            $detail->id_jurnal = $header->id_jurnal;
                            $detail->index = $index;
                            $detail->id_akun = $jd['akun'];
                            $detail->debet = $jd['debet'];
                            $detail->credit = $jd['credit'];
                            $detail->keterangan = $jd['keterangan'];
                            $detail->id_transaksi = $jd['id_transaksi'];
                            $detail->user_created = $user_created;
                            $detail->dt_created = date('Y-m-d h:i:s');
                            $detail->user_modified = $user_created;
                            $detail->dt_modified = date('Y-m-d h:i:s');

                            if (!$detail->save()) {
                                DB::rollback();
                                return response()->json([
                                    "result" => false,
                                    "code" => 400,
                                    "message" => "Error when store Jurnal data on table detail"
                                ], 400);
                            }

                            //  Update Saldo Transaksi
                            $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                            if ($trx_saldo) {
                                // cek untuk revert
                                if($trx_saldo->bayar > 0){
                                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                }

                                // update
                                $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                                $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                if (!$update_trx_saldo) {
                                    DB::rollback();
                                    return response()->json([
                                        "result" => false,
                                        "message" => "Error when store Jurnal data on update saldo transaksi"
                                    ]);
                                }
                            }

                            $index++;
                        }
                    }
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully stored Jurnal data",
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when store Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal data",
                "exception" => $e
            ], 400);
        }
    }

    public function journalReturPembelian(Request $request)
    {
        try {
            // init data
            // header
            $id_transaksi = $request->no_transaksi;
            $tanggal_jurnal = date('Y-m-d', strtotime($request->tanggal));
            $void = $request->void;
            $user_created = $request->user;
            $id_pemasok= $request->pemasok;
            $id_cabang = $request->cabang;
            $id_slip = $request->slip;
            $detail_inventory = array_values($request->detail);

            $data_pemasok = DB::table("pemasok")->where('id_pemasok', $id_pemasok)->first();
            $nama_pemasok = $data_pemasok->nama_pemasok;
            $catatan_me = 'Journal Otomatis Retur Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok;

            // init setting
            $data_akun_hutang_dagang = DB::table('setting')->where('code', 'Hutang Dagang')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_hutang_dagang)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Hutang Dagang not found"
                ], 404);
            }

            $data_akun_ppn_masukkan = DB::table('setting')->where('code', 'PPN Masukkan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if(empty($data_akun_ppn_masukkan)){
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Masukkan not found"
                ], 404);
            }

            // cek apakah ada saldo_transaksi
            $check_trx_saldo = TrxSaldo::where("id_transaksi", $id_transaksi)->first();
            if(empty($check_trx_saldo)){
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error saldo transaksi belum ada"
                ], 404);
            }

            // detail
            // Memorial
            $akun_hutang_dagang = $data_akun_hutang_dagang->value2;
            $akun_ppn_masukkan = $data_akun_ppn_masukkan->value2;
            $total = $request->total;
            $uang_muka = $request->uang_muka;
            $nominal_ppn = $request->ppn;

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail_me = [
                [
                    'akun'          => $akun_hutang_dagang,
                    'debet'         => ($total + $uang_muka),
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis Retur Pembelian ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi'  => null
                ],
                [
                    'akun'          => $akun_ppn_masukkan,
                    'debet'         => 0,
                    'credit'        => $nominal_ppn,
                    'keterangan'    => 'Jurnal Otomatis PPN Masukkan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi'  => null
                ],
            ];

            foreach($detail_inventory as $d_inv){
                array_push($jurnal_detail_me, [
                    'akun'          => $d_inv['akun_id'],
                    'debet'         => 0,
                    'credit'        => $d_inv['total'],
                    'keterangan'    => 'Jurnal Otomatis Retur Pembelian Persediaan - ' . $id_transaksi . ' - ' . $nama_pemasok . ' - ' . $d_inv['nama_barang'],
                    'id_transaksi'  => null
                ]);
            }

            // Find Header data and delete detail
            $header_me = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', 'ME')->where('void', 0)->first();

            // Begin save
            DB::beginTransaction();
            if (!empty($header_me)) {
                JurnalDetail::where('id_jurnal', $header_me->id_jurnal)->delete();
                $header_me->id_cabang = $id_cabang;
                $header_me->tanggal_jurnal = $tanggal_jurnal;
                $header_me->void = $void;
                $header_me->catatan = $catatan_me;
                $header_me->user_modified = $user_created;
                $header_me->dt_modified = date('Y-m-d h:i:s');
            } else {
                $header_me = new JurnalHeader();
                $header_me->id_cabang = $id_cabang;
                $header_me->kode_jurnal = $this->generateJournalCode($id_cabang, 'ME');
                $header_me->id_transaksi = $id_transaksi;
                $header_me->jenis_jurnal = 'ME';
                $header_me->tanggal_jurnal = $tanggal_jurnal;
                $header_me->void = $void;
                $header_me->catatan = $catatan_me;
                $header_me->user_created = $user_created;
                $header_me->dt_created = date('Y-m-d h:i:s');
                $header_me->user_modified = $user_created;
                $header_me->dt_created = date('Y-m-d h:i:s');
            }

            if (!$header_me->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table header"
                ], 400);
            }

            if (!empty($jurnal_detail_me)) {
                $index = 1;
                foreach ($jurnal_detail_me as $jd) {
                    if(($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)){
                        $detail_me = new JurnalDetail();
                        $detail_me->id_jurnal = $header_me->id_jurnal;
                        $detail_me->index = $index;
                        $detail_me->id_akun = $jd['akun'];
                        $detail_me->debet = $jd['debet'];
                        $detail_me->credit = $jd['credit'];
                        $detail_me->keterangan = $jd['keterangan'];
                        $detail_me->id_transaksi = $jd['id_transaksi'];
                        $detail_me->user_created = $user_created;
                        $detail_me->dt_created = date('Y-m-d h:i:s');
                        $detail_me->user_modified = $user_created;
                        $detail_me->dt_modified = date('Y-m-d h:i:s');

                        // variable check
                        $check_balance_debit += $jd['debet'];
                        $check_balance_credit += $jd['credit'];

                        if (!$detail_me->save()) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "code" => 400,
                                "message" => "Error when store Jurnal data on table detail"
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            // check balance
            if($check_balance_debit != $check_balance_credit){
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance"
                ], 400);
            }

            if($id_slip != null){
                // init slip
                $data_slip = Slip::find($id_slip);

                if ($data_slip->jenis_slip == 0) {
                    $jurnal_type = 'KK';
                    $jurnal_type_detail = 'Kas Keluar';
                } else if ($data_slip->jenis_slip == 1) {
                    $jurnal_type = 'BK';
                    $jurnal_type_detail = 'Bank Keluar';
                } else {
                    return response()->json([
                        "result" => false,
                        "code" => 400,
                        "message" => "Error, please use slip Kas Keluar or Bank Keluar"
                    ], 400);
                }

                $catatan_pelunasan = 'Journal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok;

                $akun_slip = $data_slip->id_akun;

                $jurnal_detail_pelunasan = [
                    [
                        'akun'          => $akun_slip,
                        'debet'         => $total,
                        'credit'        => 0,
                        'keterangan'    => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                        'id_transaksi'  => null
                    ],
                    [
                        'akun'          => $akun_hutang_dagang,
                        'debet'         => 0,
                        'credit'        => $total,
                        'keterangan'    => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                        'id_transaksi'  => $id_transaksi
                    ]
                ];

                // Find Header data and delete detail
                $header = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', '<>', 'ME')->where('void', 0)->first();

                if (!empty($header) && $header->id_slip == $id_slip) {
                    JurnalDetail::where('id_jurnal', $header->id_jurnal)->delete();
                    $header->id_cabang = $id_cabang;
                    $header->tanggal_jurnal = $tanggal_jurnal;
                    $header->id_slip = $id_slip;
                    $header->void = $void;
                    $header->catatan = $catatan_pelunasan;
                    $header->user_modified = $user_created;
                    $header->dt_modified = date('Y-m-d h:i:s');
                } else {
                    if(!empty($header) && $header->id_slip != $id_slip){
                        $header->void = 1;
                        $header->user_void = $user_created;
                        $header->dt_void = date('Y-m-d h:i:s');
                        $header->save();
                    }
                    $header = new JurnalHeader();
                    $header->id_cabang = $id_cabang;
                    $header->kode_jurnal = $this->generateJournalCode($id_cabang, $jurnal_type, $id_slip);
                    $header->id_transaksi = $id_transaksi;
                    $header->jenis_jurnal = $jurnal_type;
                    $header->id_slip = $id_slip;
                    $header->tanggal_jurnal = $tanggal_jurnal;
                    $header->void = $void;
                    $header->catatan = $catatan_pelunasan;
                    $header->user_created = $user_created;
                    $header->dt_created = date('Y-m-d h:i:s');
                    $header->user_modified = $user_created;
                    $header->dt_created = date('Y-m-d h:i:s');
                }

                if (!$header->save()) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "code" => 400,
                        "message" => "Error when store Jurnal data on table header"
                    ], 400);
                }

                if (!empty($jurnal_detail_pelunasan)) {
                    $index = 1;
                    foreach ($jurnal_detail_pelunasan as $jd) {
                        if(($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)){
                            $detail = new JurnalDetail();
                            $detail->id_jurnal = $header->id_jurnal;
                            $detail->index = $index;
                            $detail->id_akun = $jd['akun'];
                            $detail->debet = $jd['debet'];
                            $detail->credit = $jd['credit'];
                            $detail->keterangan = $jd['keterangan'];
                            $detail->id_transaksi = $jd['id_transaksi'];
                            $detail->user_created = $user_created;
                            $detail->dt_created = date('Y-m-d h:i:s');
                            $detail->user_modified = $user_created;
                            $detail->dt_modified = date('Y-m-d h:i:s');

                            if (!$detail->save()) {
                                DB::rollback();
                                return response()->json([
                                    "result" => false,
                                    "code" => 400,
                                    "message" => "Error when store Jurnal data on table detail"
                                ], 400);
                            }

                            //  Update Saldo Transaksi
                            $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                            if ($trx_saldo) {
                                // cek untuk revert
                                if($trx_saldo->bayar > 0){
                                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                }

                                // update
                                $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                                $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                if (!$update_trx_saldo) {
                                    DB::rollback();
                                    return response()->json([
                                        "result" => false,
                                        "message" => "Error when store Jurnal data on update saldo transaksi"
                                    ]);
                                }
                            }

                            $index++;
                        }
                    }
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully stored Jurnal data",
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when store Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal data",
                "exception" => $e
            ], 400);
        }
    }

    public function voidJournalOtomatis(Request $request){
        try {
            // init data
            // header
            $id_transaksi = $request->no_transaksi;
            $user_void = $request->user;

            // Find Header data and delete detail
            $header_me = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', 'ME')->where('void', 0)->first();
            $header_pelunasan = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', '<>', 'ME')->where('void', 0)->first();

            if(empty($header_me)){
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when void Jurnal data. Journal Memorial transaction " . $id_transaksi . " not found"
                ], 400);
            }

            if(!empty($header_pelunasan)){
                $header_pelunasan = JurnalDetail::join('jurnal_header', 'jurnal_header.id_jurnal', 'jurnal_detail.id_jurnal')
                                    ->where("jurnal_detail.id_transaksi", $id_transaksi)
                                    ->where('jurnal_header.jenis_jurnal', '<>', 'ME')
                                    ->where('jurnal_header.void', 0)
                                    ->first();

                if(!empty($header_pelunasan)){
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "code" => 400,
                        "message" => "Error when void Jurnal data. Transaction " . $id_transaksi . " already paid"
                    ], 400);
                }
            }

            // Begin save
            DB::beginTransaction();
            $header_me->void = 1;
            $header_me->user_void = $user_void;
            $header_me->dt_void = date('Y-m-d h:i:s');

            if (!$header_me->save()) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when void Jurnal data on table header"
                ], 400);
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully void Jurnal data",
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when void Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when void Jurnal data",
                "exception" => $e
            ], 400);
        }
    }

    public function updateTrxSaldo($trx, $debet, $credit)
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
                    $trx_saldo->bayar = $current_bayar + $credit;
                    $trx_saldo->sisa = $current_sisa - $credit;
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
                    $trx_saldo->bayar = $current_bayar + $credit;
                    $trx_saldo->sisa = $current_sisa - $credit;
                    break;
                case 'Piutang Giro':
                    $trx_saldo->bayar = $current_bayar + $credit;
                    $trx_saldo->sisa = $current_sisa - $credit;
                    break;
                case 'Hutang Giro':
                    $trx_saldo->bayar = $current_bayar + $debet;
                    $trx_saldo->sisa = $current_sisa - $debet;
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
        }
        catch (\Exception $e) {
            // DB::rollback();
            Log::error($e);
            return false;
        }
    }

    public function revertTrxSaldo($trx, $debet, $credit)
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
                    $trx_saldo->bayar = $current_bayar - $credit;
                    $trx_saldo->sisa = $current_sisa + $credit;
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
                    $trx_saldo->bayar = $current_bayar - $credit;
                    $trx_saldo->sisa = $current_sisa + $credit;
                    break;
                case 'Piutang Giro':
                    $trx_saldo->bayar = $current_bayar - $credit;
                    $trx_saldo->sisa = $current_sisa + $credit;
                    break;
                case 'Hutang Giro':
                    $trx_saldo->bayar = $current_bayar - $debet;
                    $trx_saldo->sisa = $current_sisa + $debet;
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
        }
        catch (\Exception $e) {
            // DB::rollback();
            Log::error($e);
            return false;
        }
    }

    public function generateJournalCode($cabang, $jenis, $slip = null)
    {
        try {
            $ex = 0;
            do {
                // Init data
                $kodeCabang = Cabang::find($cabang);
                if($slip != null){
                    $kodeSlip = Slip::find($slip);
                    $prefix = $kodeCabang->kode_cabang . "." . $jenis . "." . $kodeSlip->kode_slip . "." . date("ym");
                }else{
                    $prefix = $kodeCabang->kode_cabang . "." . $jenis . "." . date("ym");
                }

                // Check exist
                $check = JurnalHeader::where("kode_jurnal", "LIKE", "$prefix%")->orderBy("kode_jurnal", "DESC")->get();
                if (count($check) > 0) {
                    $max = (int)substr($check[0]->kode_jurnal, -4);
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

    public function productionSupplies(Request $request){
        $production_id = $request->production_id;

        $data_production_supplies = DB::table("produksi_detail")
                                    ->join('barang', 'barang.id_barang', 'produksi_detail.id_barang')
                                    ->join('master_qr_code', 'master_qr_code.kode_batang_master_qr_code', 'produksi_detail.kode_batang_lama_produksi_detail')
                                    ->selectRaw('produksi_detail.id_barang,
                                    ROUND(IFNULL(SUM(produksi_detail.kredit_produksi_detail * master_qr_code.beli_master_qr_code), 0), 2) as beli,
                                    ROUND(IFNULL(SUM(produksi_detail.kredit_produksi_detail * master_qr_code.biaya_beli_master_qr_code), 0), 2) as biaya,
                                    ROUND(IFNULL(SUM(produksi_detail.kredit_produksi_detail * master_qr_code.produksi_master_qr_code), 0), 2) as produksi,
                                    ROUND(IFNULL(SUM(produksi_detail.kredit_produksi_detail * master_qr_code.listrik_master_qr_code), 0), 2) as listrik,
                                    ROUND(IFNULL(SUM(produksi_detail.kredit_produksi_detail * master_qr_code.pegawai_master_qr_code), 0), 2) as pegawai,
                                    barang.akun_persediaan_barang as id_akun')
                                    ->where('produksi_detail.id_produksi', $production_id)
                                    ->groupBy('produksi_detail.id_barang')
                                    ->orderBy('produksi_detail.id_barang', 'ASC')
                                    ->get();

        $data = [];

        foreach($data_production_supplies as $production){
            $data[$production->id_barang] = [
                'total' => ($production->beli + $production->biaya + $production->produksi + $production->listrik + $production->pegawai),
                'id_akun' => $production->id_akun,
            ];
        }

        Log::debug(json_encode($data_production_supplies));
        Log::debug($data);
    }
}
