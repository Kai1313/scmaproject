<?php

namespace App\Http\Controllers;

use App\Barang;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
use App\Models\Accounting\TrxSaldo;
use App\Models\Master\Akun;
use App\Models\Master\Cabang;
use App\Models\Master\Setting;
use App\Models\Master\Slip;
use App\Models\Transaction\StokMinimalHitung;
use App\Models\User;
use App\Models\UserToken;
use App\TransactionBalance;
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

        $user = User::where('id_pengguna', $user_id)->first();
        $token = UserToken::where('id_pengguna', $user_id)->where('status_token_pengguna', 1)->whereRaw("waktu_habis_token_pengguna > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", Carbon::now()->format('Y-m-d H:i:s'))->first();
        if ($token) {
            $token = $user->createToken('Token Passport User ' . Carbon::now()->format('Y-m-d H:i:s') . '[' . $user->id_pengguna . '] ' . $user->nama_pengguna)->accessToken;
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Login Success",
                "token" => $token,
            ], 200);
        } else {
            return response()->json([
                "result" => false,
                "code" => 401,
                "message" => "Error, User has no Authorization",
            ], 401);
        }
    }

    public function profile(Request $request)
    {
        return response()->json([
            'user' => Auth::guard('api')->user(),
        ], 200);
    }

    public function logout(Request $request)
    {
        if (Auth::guard('api')->user()) {
            Auth::guard('api')->user()->tokens()->delete();
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Log Out Success",
            ], 200);
        } else {
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error, Log Out Failed",
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
                    "message" => "Error, please use slip Kas Masuk or Bank Masuk",
                ], 400);
            }

            // detail
            $data_akun_uang_muka_penjualan = DB::table('setting')->where('code', 'Uang Muka Penjualan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_uang_muka_penjualan)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Uang Muka Penjualan not found",
                ], 404);
            }

            $data_akun_ppn_keluaran = DB::table('setting')->where('code', 'PPN Keluaran')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_ppn_keluaran)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Keluaran not found",
                ], 404);
            }

            $akun_slip = $data_slip->id_akun;
            $akun_uang_muka_penjualan = $data_akun_uang_muka_penjualan->value2;
            $akun_ppn_keluaran = $data_akun_ppn_keluaran->value2;
            $total = round(floatval($request->total), 2);
            $uang_muka = round(floatval($request->uang_muka), 2);
            $nominal_ppn = round(floatval($request->ppn), 2);

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail = [
                [
                    'akun' => $akun_slip,
                    'debet' => $total,
                    'credit' => 0,
                    'keterangan' => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Uang Muka Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                ],
                [
                    'akun' => $akun_uang_muka_penjualan,
                    'debet' => 0,
                    'credit' => $uang_muka,
                    'keterangan' => 'Jurnal Otomatis Uang Muka Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                ],
                [
                    'akun' => $akun_ppn_keluaran,
                    'debet' => 0,
                    'credit' => $nominal_ppn,
                    'keterangan' => 'Jurnal Otomatis PPN Keluaran - ' . $id_transaksi . ' - ' . $nama_pelanggan,
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
                    "message" => "Error when store Jurnal data on table header",
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
                                "message" => "Error when store Jurnal data on table detail",
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            $check_balance_debit = round($check_balance_debit, 2);
            $check_balance_credit = round($check_balance_credit, 2);
            // check balance
            if ($check_balance_debit != $check_balance_credit) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance. credit: " . $check_balance_credit . ", debet : " . $check_balance_debit,
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
                "exception" => $e,
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

            if (empty($data_slip)) {
                $data_akun_hutang_dagang = DB::table('setting')->where('code', 'Hutang Dagang')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
                if (empty($data_akun_hutang_dagang)) {
                    return response()->json([
                        "result" => false,
                        "code" => 400,
                        "message" => "Error, please use slip Kas Keluar, Bank Keluar, or set up Hutang Dagang setting first",
                    ], 400);
                } else {
                    $data_akun = Akun::find($data_akun_hutang_dagang->value2);
                    if (empty($data_akun)) {
                        return response()->json([
                            "result" => false,
                            "code" => 400,
                            "message" => "Error, can not find id_akun in Hutang Dagang setting",
                        ], 400);
                    } else {
                        $jurnal_type = 'ME';
                        $jurnal_type_detail = 'Memorial';
                        $akun_slip = $data_akun->id_akun;
                    }
                }
            } else {
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
                        "message" => "Error, please use slip Kas Keluar or Bank Keluar",
                    ], 400);
                }
                $akun_slip = $data_slip->id_akun;
            }

            // detail
            $data_akun_uang_muka_pembelian = DB::table('setting')->where('code', 'Uang Muka Pembelian')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_uang_muka_pembelian)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Uang Muka Pembelian not found",
                ], 404);
            }

            $data_akun_ppn_masukan = DB::table('setting')->where('code', 'PPN Masukkan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_ppn_masukan)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Masukkan not found",
                ], 404);
            }

            $akun_uang_muka_pembelian = $data_akun_uang_muka_pembelian->value2;
            $akun_ppn_masukan = $data_akun_ppn_masukan->value2;
            $total = round(floatval($request->total), 2);
            $uang_muka = round(floatval($request->uang_muka), 2);
            $nominal_ppn = round(floatval($request->ppn), 2);

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail = [
                [
                    'akun' => $akun_slip,
                    'debet' => 0,
                    'credit' => $total,
                    'keterangan' => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Uang Muka Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok,
                ],
                [
                    'akun' => $akun_uang_muka_pembelian,
                    'debet' => $uang_muka,
                    'credit' => 0,
                    'keterangan' => 'Jurnal Otomatis Uang Muka Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok,
                ],
                [
                    'akun' => $akun_ppn_masukan,
                    'debet' => $nominal_ppn,
                    'credit' => 0,
                    'keterangan' => 'Jurnal Otomatis PPN Masukkan - ' . $id_transaksi . ' - ' . $nama_pemasok,
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
                    "message" => "Error when store Jurnal data on table header",
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
                                "message" => "Error when store Jurnal data on table detail",
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            $check_balance_debit = round($check_balance_debit, 2);
            $check_balance_credit = round($check_balance_credit, 2);
            // check balance
            if ($check_balance_debit != $check_balance_credit) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance. credit: " . $check_balance_credit . ", debet : " . $check_balance_debit,
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
                "exception" => $e,
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

            $array_inventory = [];
            foreach ($detail_inventory as $detail_inv) {
                if (isset($array_inventory[$detail_inv['id_barang']])) {
                    $array_inventory[$detail_inv['id_barang']]['total'] += $detail_inv['total'];
                } else {
                    $array_inventory[$detail_inv['id_barang']] = [
                        'id_barang' => $detail_inv['id_barang'],
                        'nama_barang' => $detail_inv['nama_barang'],
                        'total' => $detail_inv['total']
                    ];
                }
            }

            // init setting
            $data_akun_piutang_dagang = DB::table('setting')->where('code', 'Piutang Dagang')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_piutang_dagang)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Piutang Dagang not found",
                ], 404);
            }

            $data_akun_uang_muka_penjualan = DB::table('setting')->where('code', 'Uang Muka Penjualan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_uang_muka_penjualan)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Uang Muka Penjualan not found",
                ], 404);
            }

            $data_akun_ppn_keluaran = DB::table('setting')->where('code', 'PPN Keluaran')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_ppn_keluaran)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Keluaran not found",
                ], 404);
            }

            $data_akun_penjualan = DB::table('setting')->where('code', 'Penjualan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_penjualan)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Penjualan not found",
                ], 404);
            }

            // cek apakah ada saldo_transaksi
            $check_trx_saldo = TrxSaldo::where("id_transaksi", $id_transaksi)->first();
            if (empty($check_trx_saldo)) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error saldo transaksi belum ada",
                ], 404);
            }

            // detail
            // Memorial
            $akun_piutang_dagang = $data_akun_piutang_dagang->value2;
            $akun_uang_muka_penjualan = $data_akun_uang_muka_penjualan->value2;
            $akun_ppn_keluaran = $data_akun_ppn_keluaran->value2;
            $akun_penjualan = $data_akun_penjualan->value2;
            $total = round(floatval($request->total), 2);
            $uang_muka = round(floatval($request->uang_muka), 2);
            $nominal_ppn = round(floatval($request->ppn), 2);

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail_me = [
                [
                    'akun' => $akun_piutang_dagang,
                    'debet' => round(($total + $uang_muka), 2),
                    'credit' => 0,
                    'keterangan' => 'Jurnal Otomatis Penjualan ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi' => null,
                ],
                [
                    'akun' => $akun_ppn_keluaran,
                    'debet' => 0,
                    'credit' => $nominal_ppn,
                    'keterangan' => 'Jurnal Otomatis PPN Keluaran - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi' => null,
                ],
            ];

            if (isset($uang_muka) && $uang_muka > 0) {
                array_push($jurnal_detail_me, [
                    'akun' => $akun_uang_muka_penjualan,
                    'debet' => $uang_muka,
                    'credit' => 0,
                    'keterangan' => 'Jurnal Otomatis Uang Muka Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi' => null,
                ]);

                array_push($jurnal_detail_me, [
                    'akun' => $akun_piutang_dagang,
                    'debet' => 0,
                    'credit' => $uang_muka,
                    'keterangan' => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi' => $id_transaksi,
                ]);
            }

            foreach ($array_inventory as $d_inv) {
                $barang = Barang::find($d_inv['id_barang']);
                if ($id_cabang == 1) {
                    $akun_penjualan_barang = $barang->id_akun_penjualan;
                } else {
                    $format_akun = 'id_akun_penjualan' . $id_cabang;
                    $akun_penjualan_barang = $barang->$format_akun;
                }

                if ($akun_penjualan_barang == null) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail. Akun Penjualan Barang " . $barang->kode_barang . ' - ' . $barang->nama_barang . ' can not null.',
                    ]);
                } else {
                    $data_akun_penjualan_barang = Akun::find($akun_penjualan_barang);
                    if (empty($data_akun_penjualan_barang)) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail. Akun Penjualan Barang " . $barang->kode_barang . ' - ' . $barang->nama_barang . ' not found.',
                        ]);
                    }
                }

                array_push($jurnal_detail_me, [
                    'akun' => $akun_penjualan_barang,
                    'debet' => 0,
                    'credit' => round(floatval($d_inv['total']), 2),
                    'keterangan' => 'Jurnal Otomatis Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan . ' - ' . $d_inv['nama_barang'],
                    'id_transaksi' => null,
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
                    "message" => "Error when store Jurnal data on table header",
                ], 400);
            }

            if (!empty($jurnal_detail_me)) {
                $index = 1;
                foreach ($jurnal_detail_me as $jd) {
                    if (($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)) {
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
                                "message" => "Error when store Jurnal data on table detail",
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            $check_balance_debit = round($check_balance_debit, 2);
            $check_balance_credit = round($check_balance_credit, 2);
            // check balance
            if ($check_balance_debit != $check_balance_credit) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance. credit: " . $check_balance_credit . ", debet : " . $check_balance_debit,
                ], 400);
            }

            if ($id_slip != null) {
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
                        "message" => "Error, please use slip Kas Masuk or Bank Masuk",
                    ], 400);
                }

                $catatan_pelunasan = 'Journal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan;

                $akun_slip = $data_slip->id_akun;

                $jurnal_detail_pelunasan = [
                    [
                        'akun' => $akun_slip,
                        'debet' => $total,
                        'credit' => 0,
                        'keterangan' => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                        'id_transaksi' => null,
                    ],
                    [
                        'akun' => $akun_piutang_dagang,
                        'debet' => 0,
                        'credit' => $total,
                        'keterangan' => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                        'id_transaksi' => $id_transaksi,
                    ],
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
                    if (!empty($header) && $header->id_slip != $id_slip) {
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
                        "message" => "Error when store Jurnal data on table header",
                    ], 400);
                }

                if (!empty($jurnal_detail_pelunasan)) {
                    $index = 1;
                    foreach ($jurnal_detail_pelunasan as $jd) {
                        if (($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)) {
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
                                    "message" => "Error when store Jurnal data on table detail",
                                ], 400);
                            }

                            //  Update Saldo Transaksi
                            $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                            if ($trx_saldo) {
                                // cek untuk revert
                                if ($trx_saldo->bayar > 0) {
                                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                }

                                // update
                                $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                                $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                if (!$update_trx_saldo) {
                                    DB::rollback();
                                    return response()->json([
                                        "result" => false,
                                        "message" => "Error when store Jurnal data on update saldo transaksi",
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
                "exception" => $e,
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
            $id_pemasok = $request->pemasok;
            $id_cabang = $request->cabang;
            $id_slip = $request->slip;
            $detail_inventory = array_values($request->detail);

            $data_pemasok = DB::table("pemasok")->where('id_pemasok', $id_pemasok)->first();
            $nama_pemasok = $data_pemasok->nama_pemasok;
            $catatan_me = 'Journal Otomatis Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok;

            // init setting
            $data_akun_hutang_dagang = DB::table('setting')->where('code', 'Hutang Dagang')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_hutang_dagang)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Hutang Dagang not found",
                ], 404);
            }

            $data_akun_uang_muka_pembelian = DB::table('setting')->where('code', 'Uang Muka Pembelian')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_uang_muka_pembelian)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Uang Muka Pembelian not found",
                ], 404);
            }

            $data_akun_ppn_masukkan = DB::table('setting')->where('code', 'PPN Masukkan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_ppn_masukkan)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Masukkan not found",
                ], 404);
            }

            // cek apakah ada saldo_transaksi
            $check_trx_saldo = TrxSaldo::where("id_transaksi", $id_transaksi)->first();
            if (empty($check_trx_saldo)) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error saldo transaksi belum ada",
                ], 404);
            }

            // detail
            // Memorial
            $akun_hutang_dagang = $data_akun_hutang_dagang->value2;
            $akun_uang_muka_pembelian = $data_akun_uang_muka_pembelian->value2;
            $akun_ppn_masukkan = $data_akun_ppn_masukkan->value2;
            $total = round(floatval($request->total), 2);
            $uang_muka = round(floatval($request->uang_muka), 2);
            $nominal_ppn = round(floatval($request->ppn), 2);
            $discount = round(floatval($request->discount), 2);

            if (isset($discount) && $discount > 0) {
                $data_akun_potongan_pembelian = DB::table('setting')->where('code', 'Potongan Pembelian')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
                if (empty($data_akun_potongan_pembelian)) {
                    return response()->json([
                        "result" => false,
                        "code" => 404,
                        "message" => "Error, Setting Potongan Pembelian not found",
                    ], 404);
                }

                $akun_potongan_pembelian = $data_akun_potongan_pembelian->value2;
            }

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail_me = [
                [
                    'akun' => $akun_hutang_dagang,
                    'debet' => 0,
                    'credit' => round(($total + $uang_muka), 2),
                    'keterangan' => 'Jurnal Otomatis Pembelian ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi' => null,
                ],
                [
                    'akun' => $akun_ppn_masukkan,
                    'debet' => $nominal_ppn,
                    'credit' => 0,
                    'keterangan' => 'Jurnal Otomatis PPN Masukkan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi' => null,
                ],
            ];

            if (isset($discount) && $discount > 0) {
                array_push($jurnal_detail_me, [
                    'akun' => $akun_potongan_pembelian,
                    'debet' => 0,
                    'credit' => $discount,
                    'keterangan' => 'Jurnal Otomatis Potongan Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi' => null,
                ]);
            }

            if (isset($uang_muka) && $uang_muka > 0) {
                array_push($jurnal_detail_me, [
                    'akun' => $akun_uang_muka_pembelian,
                    'debet' => 0,
                    'credit' => $uang_muka,
                    'keterangan' => 'Jurnal Otomatis Uang Muka Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi' => null,
                ]);

                array_push($jurnal_detail_me, [
                    'akun' => $akun_hutang_dagang,
                    'debet' => $uang_muka,
                    'credit' => 0,
                    'keterangan' => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi' => $id_transaksi,
                ]);
            }

            foreach ($detail_inventory as $d_inv) {
                array_push($jurnal_detail_me, [
                    'akun' => $d_inv['akun_id'],
                    'debet' => round(floatval($d_inv['total']), 2),
                    'credit' => 0,
                    'keterangan' => 'Jurnal Otomatis Pembelian Persediaan - ' . $id_transaksi . ' - ' . $nama_pemasok . ' - ' . $d_inv['nama_barang'],
                    'id_transaksi' => null,
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
                    "message" => "Error when store Jurnal data on table header",
                ], 400);
            }

            if (!empty($jurnal_detail_me)) {
                $index = 1;
                foreach ($jurnal_detail_me as $jd) {
                    if (($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)) {
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
                                "message" => "Error when store Jurnal data on table detail",
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            $check_balance_debit = round($check_balance_debit, 2);
            $check_balance_credit = round($check_balance_credit, 2);
            // check balance
            if ($check_balance_debit != $check_balance_credit) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance. credit: " . $check_balance_credit . ", debet : " . $check_balance_debit,
                ], 400);
            }

            if ($id_slip != null) {
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
                        "message" => "Error, please use slip Kas Keluar or Bank Keluar",
                    ], 400);
                }

                $catatan_pelunasan = 'Journal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok;

                $akun_slip = $data_slip->id_akun;

                $jurnal_detail_pelunasan = [
                    [
                        'akun' => $akun_slip,
                        'debet' => 0,
                        'credit' => $total,
                        'keterangan' => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                        'id_transaksi' => null,
                    ],
                    [
                        'akun' => $akun_hutang_dagang,
                        'debet' => $total,
                        'credit' => 0,
                        'keterangan' => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                        'id_transaksi' => $id_transaksi,
                    ],
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
                    if (!empty($header) && $header->id_slip != $id_slip) {
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
                        "message" => "Error when store Jurnal data on table header",
                    ], 400);
                }

                if (!empty($jurnal_detail_pelunasan)) {
                    $index = 1;
                    foreach ($jurnal_detail_pelunasan as $jd) {
                        if (($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)) {
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
                                    "message" => "Error when store Jurnal data on table detail",
                                ], 400);
                            }

                            //  Update Saldo Transaksi
                            $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                            if ($trx_saldo) {
                                // cek untuk revert
                                if ($trx_saldo->bayar > 0) {
                                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                }

                                // update
                                $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                                $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                if (!$update_trx_saldo) {
                                    DB::rollback();
                                    return response()->json([
                                        "result" => false,
                                        "message" => "Error when store Jurnal data on update saldo transaksi",
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
                "exception" => $e,
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
            if (empty($data_akun_piutang_dagang)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Piutang Dagang not found",
                ], 404);
            }

            $data_akun_ppn_keluaran = DB::table('setting')->where('code', 'PPN Keluaran')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_ppn_keluaran)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Keluaran not found",
                ], 404);
            }

            $data_akun_penjualan = DB::table('setting')->where('code', 'Penjualan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_penjualan)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Penjualan not found",
                ], 404);
            }

            // cek apakah ada saldo_transaksi
            $check_trx_saldo = TrxSaldo::where("id_transaksi", $id_transaksi)->first();
            if (empty($check_trx_saldo)) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error saldo transaksi belum ada",
                ], 404);
            }

            // detail
            // Memorial
            $akun_piutang_dagang = $data_akun_piutang_dagang->value2;
            $akun_ppn_keluaran = $data_akun_ppn_keluaran->value2;
            $akun_penjualan = $data_akun_penjualan->value2;
            $total = round(floatval($request->total), 2);
            $nominal_ppn = round(floatval($request->ppn), 2);

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail_me = [
                [
                    'akun' => $akun_piutang_dagang,
                    'debet' => 0,
                    'credit' => $total,
                    'keterangan' => 'Jurnal Otomatis Retur Penjualan ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi' => null,
                ],
                [
                    'akun' => $akun_ppn_keluaran,
                    'debet' => $nominal_ppn,
                    'credit' => 0,
                    'keterangan' => 'Jurnal Otomatis PPN Keluaran - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                    'id_transaksi' => null,
                ],
            ];

            foreach ($detail_inventory as $d_inv) {
                $barang = Barang::find($d_inv['id_barang']);
                if ($id_cabang == 1) {
                    $akun_retur_penjualan_barang = $barang->id_akun_retur_penjualan;
                } else {
                    $format_akun = 'id_akun_retur_penjualan' . $id_cabang;
                    $akun_retur_penjualan_barang = $barang->$format_akun;
                }

                if ($akun_retur_penjualan_barang == null) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "message" => "Error when store Jurnal data on table detail. Akun Retur Penjualan Barang " . $barang->kode_barang . ' - ' . $barang->nama_barang . ' can not null.',
                    ]);
                } else {
                    $data_akun_penjualan_barang = Akun::find($akun_retur_penjualan_barang);
                    if (empty($data_akun_penjualan_barang)) {
                        DB::rollback();
                        return response()->json([
                            "result" => false,
                            "message" => "Error when store Jurnal data on table detail. Akun Retur Penjualan Barang " . $barang->kode_barang . ' - ' . $barang->nama_barang . ' not found.',
                        ]);
                    }
                }

                array_push($jurnal_detail_me, [
                    'akun' => $akun_retur_penjualan_barang,
                    'debet' => round(floatval($d_inv['total']), 2),
                    'credit' => 0,
                    'keterangan' => 'Jurnal Otomatis Retur Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan . ' - ' . $d_inv['nama_barang'],
                    'id_transaksi' => null,
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
                    "message" => "Error when store Jurnal data on table header",
                ], 400);
            }

            if (!empty($jurnal_detail_me)) {
                $index = 1;
                foreach ($jurnal_detail_me as $jd) {
                    if (($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)) {
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
                                "message" => "Error when store Jurnal data on table detail",
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            $check_balance_debit = round($check_balance_debit, 2);
            $check_balance_credit = round($check_balance_credit, 2);
            // check balance
            if ($check_balance_debit != $check_balance_credit) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance. credit: " . $check_balance_credit . ", debet : " . $check_balance_debit,
                ], 400);
            }

            if ($id_slip != null) {
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
                        "message" => "Error, please use slip Kas Masuk or Bank Masuk",
                    ], 400);
                }

                $catatan_pelunasan = 'Journal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan;

                $akun_slip = $data_slip->id_akun;

                $jurnal_detail_pelunasan = [
                    [
                        'akun' => $akun_slip,
                        'debet' => 0,
                        'credit' => $total,
                        'keterangan' => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                        'id_transaksi' => null,
                    ],
                    [
                        'akun' => $akun_piutang_dagang,
                        'debet' => $total,
                        'credit' => 0,
                        'keterangan' => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                        'id_transaksi' => $id_transaksi,
                    ],
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
                    if (!empty($header) && $header->id_slip != $id_slip) {
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
                        "message" => "Error when store Jurnal data on table header",
                    ], 400);
                }

                if (!empty($jurnal_detail_pelunasan)) {
                    $index = 1;
                    foreach ($jurnal_detail_pelunasan as $jd) {
                        if (($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)) {
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
                                    "message" => "Error when store Jurnal data on table detail",
                                ], 400);
                            }

                            //  Update Saldo Transaksi
                            $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                            if ($trx_saldo) {
                                // cek untuk revert
                                if ($trx_saldo->bayar > 0) {
                                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                }

                                // update
                                $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                                $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                if (!$update_trx_saldo) {
                                    DB::rollback();
                                    return response()->json([
                                        "result" => false,
                                        "message" => "Error when store Jurnal data on update saldo transaksi",
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
                "exception" => $e,
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
            $id_pemasok = $request->pemasok;
            $id_cabang = $request->cabang;
            $id_slip = $request->slip;
            $detail_inventory = array_values($request->detail);

            $data_pemasok = DB::table("pemasok")->where('id_pemasok', $id_pemasok)->first();
            $nama_pemasok = $data_pemasok->nama_pemasok;
            $catatan_me = 'Journal Otomatis Retur Pembelian - ' . $id_transaksi . ' - ' . $nama_pemasok;

            // init setting
            $data_akun_hutang_dagang = DB::table('setting')->where('code', 'Hutang Dagang')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_hutang_dagang)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting Hutang Dagang not found",
                ], 404);
            }

            $data_akun_ppn_masukkan = DB::table('setting')->where('code', 'PPN Masukkan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_ppn_masukkan)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Masukkan not found",
                ], 404);
            }

            // cek apakah ada saldo_transaksi
            $check_trx_saldo = TrxSaldo::where("id_transaksi", $id_transaksi)->first();
            if (empty($check_trx_saldo)) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error saldo transaksi belum ada",
                ], 404);
            }

            // detail
            // Memorial
            $akun_hutang_dagang = $data_akun_hutang_dagang->value2;
            $akun_ppn_masukkan = $data_akun_ppn_masukkan->value2;
            $total = $request->total;
            $uang_muka = round(floatval($request->uang_muka), 2);
            $nominal_ppn = round(floatval($request->ppn), 2);

            // Check balance
            $check_balance_debit = 0;
            $check_balance_credit = 0;

            $jurnal_detail_me = [
                [
                    'akun' => $akun_hutang_dagang,
                    'debet' => round(($total + $uang_muka), 2),
                    'credit' => 0,
                    'keterangan' => 'Jurnal Otomatis Retur Pembelian ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi' => null,
                ],
                [
                    'akun' => $akun_ppn_masukkan,
                    'debet' => 0,
                    'credit' => $nominal_ppn,
                    'keterangan' => 'Jurnal Otomatis PPN Masukkan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                    'id_transaksi' => null,
                ],
            ];

            foreach ($detail_inventory as $d_inv) {
                array_push($jurnal_detail_me, [
                    'akun' => $d_inv['akun_id'],
                    'debet' => 0,
                    'credit' => round(floatval($d_inv['total']), 2),
                    'keterangan' => 'Jurnal Otomatis Retur Pembelian Persediaan - ' . $id_transaksi . ' - ' . $nama_pemasok . ' - ' . $d_inv['nama_barang'],
                    'id_transaksi' => null,
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
                    "message" => "Error when store Jurnal data on table header",
                ], 400);
            }

            if (!empty($jurnal_detail_me)) {
                $index = 1;
                foreach ($jurnal_detail_me as $jd) {
                    if (($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)) {
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
                                "message" => "Error when store Jurnal data on table detail",
                            ], 400);
                        }

                        $index++;
                    }
                }
            }

            $check_balance_debit = round($check_balance_debit, 2);
            $check_balance_credit = round($check_balance_credit, 2);
            // check balance
            if ($check_balance_debit != $check_balance_credit) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal data on table detail. Credit & debet not balance. credit: " . $check_balance_credit . ", debet : " . $check_balance_debit,
                ], 400);
            }

            if ($id_slip != null) {
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
                        "message" => "Error, please use slip Kas Keluar or Bank Keluar",
                    ], 400);
                }

                $catatan_pelunasan = 'Journal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok;

                $akun_slip = $data_slip->id_akun;

                $jurnal_detail_pelunasan = [
                    [
                        'akun' => $akun_slip,
                        'debet' => $total,
                        'credit' => 0,
                        'keterangan' => 'Jurnal Otomatis ' . $jurnal_type_detail . ' Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                        'id_transaksi' => null,
                    ],
                    [
                        'akun' => $akun_hutang_dagang,
                        'debet' => 0,
                        'credit' => $total,
                        'keterangan' => 'Jurnal Otomatis Pelunasan - ' . $id_transaksi . ' - ' . $nama_pemasok,
                        'id_transaksi' => $id_transaksi,
                    ],
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
                    if (!empty($header) && $header->id_slip != $id_slip) {
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
                        "message" => "Error when store Jurnal data on table header",
                    ], 400);
                }

                if (!empty($jurnal_detail_pelunasan)) {
                    $index = 1;
                    foreach ($jurnal_detail_pelunasan as $jd) {
                        if (($jd['debet'] > 0 && $jd['credit'] == 0) || ($jd['debet'] == 0 && $jd['credit'] > 0)) {
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
                                    "message" => "Error when store Jurnal data on table detail",
                                ], 400);
                            }

                            //  Update Saldo Transaksi
                            $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                            if ($trx_saldo) {
                                // cek untuk revert
                                if ($trx_saldo->bayar > 0) {
                                    $update_trx_saldo = $this->revertTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                }

                                // update
                                $trx_saldo = TrxSaldo::where("id_transaksi", $jd["id_transaksi"])->first();
                                $update_trx_saldo = $this->updateTrxSaldo($trx_saldo, $jd['debet'], $jd['credit']);
                                if (!$update_trx_saldo) {
                                    DB::rollback();
                                    return response()->json([
                                        "result" => false,
                                        "message" => "Error when store Jurnal data on update saldo transaksi",
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
                "exception" => $e,
            ], 400);
        }
    }

    public function voidJournalOtomatis(Request $request)
    {
        try {
            // init data
            // header
            $id_transaksi = $request->no_transaksi;
            $user_void = $request->user;

            // Find Header data and delete detail
            $header_me = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', 'ME')->where('void', 0)->first();
            $header_pelunasan = JurnalHeader::where("id_transaksi", $id_transaksi)->where('jenis_jurnal', '<>', 'ME')->where('void', 0)->first();

            if (empty($header_me)) {
                DB::rollback();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when void Jurnal data. Journal Memorial transaction " . $id_transaksi . " not found",
                ], 400);
            }

            if (!empty($header_pelunasan)) {
                $header_pelunasan = JurnalDetail::join('jurnal_header', 'jurnal_header.id_jurnal', 'jurnal_detail.id_jurnal')
                    ->where("jurnal_detail.id_transaksi", $id_transaksi)
                    ->where('jurnal_header.jenis_jurnal', '<>', 'ME')
                    ->where('jurnal_header.void', 0)
                    ->first();

                if (!empty($header_pelunasan)) {
                    DB::rollback();
                    return response()->json([
                        "result" => false,
                        "code" => 400,
                        "message" => "Error when void Jurnal data. Transaction " . $id_transaksi . " already paid",
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
                    "message" => "Error when void Jurnal data on table header",
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
                "exception" => $e,
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
                if ($slip != null) {
                    $kodeSlip = Slip::find($slip);
                    $prefix = $kodeCabang->kode_cabang . "." . $jenis . "." . $kodeSlip->kode_slip . "." . date("ym");
                } else {
                    $prefix = $kodeCabang->kode_cabang . "." . $jenis . "." . date("ym");
                }

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

    public function transactionBalance(Request $req)
    {
        //param : tipe_transaksi,id_transaksi,tanggal,ref_id,catatan,target(supplier/customer),dpp,ppn,uang_muka,biaya,payment_status,discount,branch_id
        $data = TransactionBalance::where('id_cabang', $req->id_cabang)
            ->where('id_transaksi', $req->id_transaksi)
            ->where('tipe_transaksi', $req->tipe_transaksi)
            ->first();
        try {
            DB::beginTransaction();

            $total = (handleNull($req->dpp) + handleNull($req->ppn) - handleNull($req->uang_muka) + handleNull($req->biaya));
            $array = [
                'tanggal' => $req->tanggal,
                'tipe_pembayaran' => $req->tipe_pembayaran,
                'ref_id' => $req->ref_id,
                'catatan' => $req->catatan,
                'id_pelanggan' => $req->id_pelanggan,
                'id_pemasok' => $req->id_pemasok,
                'dpp' => handleNull($req->dpp),
                'ppn' => handleNull($req->ppn),
                'uang_muka' => handleNull($req->uang_muka),
                'biaya' => handleNull($req->biaya),
                'total' => $total,
                'discount' => isset($req->discount) ? handleNull($req->discount) : 0,
                'id_cabang' => $req->id_cabang,
            ];

            $newTipePembayaran = $req->tipe_pembayaran;
            if (!$data) {
                $data = new transactionBalance;
                $payment = $newTipePembayaran == '1' ? $total : 0;
                $remaining = $newTipePembayaran == '1' ? 0 : ($total - $payment);

                $array['tipe_transaksi'] = $req->tipe_transaksi;
                $array['id_transaksi'] = $req->id_transaksi;
                $array['bayar'] = $payment;
                $array['sisa'] = $remaining;
            } else {
                $payment = $data->bayar;
                $remaining = $newTipePembayaran == '1' ? 0 : ($total - $payment);
                $array['sisa'] = $remaining;
            }

            $data->fill($array);
            $data->save();

            DB::commit();
            return response()->json([
                "result" => true,
                "message" => "Data berhasil disimpan",
            ], 200);
        } catch (\Exception $th) {
            DB::rollback();
            return response()->json([
                "result" => false,
                "message" => "Data gagal disimpan",
            ], 500);
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
            $nominal_listrik = $data['nominal_listrik']; // Diisi dengan data nominal listrik (setting)
            $nominal_gaji = $data['nominal_gaji']; // Diisi dengan data nominal gaji (setting)
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
                $header->user_created = $userRecord;
                $header->user_modified = $userModified;
                if (empty($jurnal_header)) {
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
                $detail->keterangan = "Biaya Listrik - " . $daya_mesin . ' Watt - ' . $kwh_listrik . ' kWh - WPH ' . $nominal_listrik;
                $detail->id_transaksi = "Biaya Listrik";
                $detail->debet = 0;
                $detail->credit = floatval($biaya_listrik);
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
                $detail->keterangan = "Biaya Operator Produksi - " . $jumlah_pegawai . ' Orang - ' . $tenaga_kerja . ' Menit - GPM ' . $nominal_gaji;
                $detail->id_transaksi = "Biaya Operator";
                $detail->debet = 0;
                $detail->credit = floatval($biaya_operator);
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
                    $total_debet += $detail->debet;
                    $total_credit += $detail->credit;
                    $index++;
                }

                // pembulatan
                if ($total_debet != $total_credit) {
                    $selisih = $total_credit - $total_debet;
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

    public function productionSupplies($production_id)
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
                                    barang.id_akun as id_akun')
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
                'akun' => $production->id_akun,
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

    public function productionCost($production_id, $id_cabang)
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

        // init beban listrik dan pegawai
        $beban_listrik = round($data_production_cost->kwh_beban_produksi, 2);
        $beban_pegawai = round(($data_production_cost->tenaga_kerja_beban_produksi * $data_production_cost->listrik_beban_produksi), 2);
        $jumlah_pegawai = round(($data_production_cost->tenaga_kerja_beban_produksi), 2);
        $daya_mesin = round($data_production_cost->daya, 2);

        // cari nominal biaya listrik dan gaji dari table setting
        $setting_nominal_listrik = DB::table('setting')
            ->where('code', 'Nominal Biaya Listrik')
            ->where('tipe', 1)
            ->where('id_cabang', $id_cabang)
            ->first();

        $setting_nominal_gaji = DB::table('setting')
            ->where('code', 'Nominal Biaya Gaji')
            ->where('tipe', 1)
            ->where('id_cabang', $id_cabang)
            ->first();

        // init nominal listrik dan gaji
        $nominal_listrik = $setting_nominal_listrik->value2;
        $nominal_gaji = $setting_nominal_gaji->value2;

        // hitung biaya listrik dan pegawai
        $biaya_listrik = round(($beban_listrik * $nominal_listrik), 2);
        $biaya_operator = round(($beban_pegawai * $nominal_gaji), 2);

        // cari data produksi detail untuk melakukan update beban biaya
        // $data_production_detail = DB::table("produksi_detail")
        //                             ->join('master_qr_code', 'master_qr_code.kode_batang_master_qr_code', 'produksi_detail.kode_batang_lama_produksi_detail')
        //                             ->where('produksi_detail.id_produksi', $id_hasil_produksi)
        //                             ->get();

        // data return biaya listrik dan pegawai
        $data = [
            'biaya_listrik' => $biaya_listrik,
            'kwh_listrik' => $beban_listrik,
            'daya_mesin' => $daya_mesin,
            'biaya_operator' => $biaya_operator,
            'tenaga_kerja' => $beban_pegawai,
            'jumlah_pegawai' => $jumlah_pegawai,
            'nominal_listrik' => $nominal_listrik,
            'nominal_gaji' => $nominal_gaji,
        ];

        return $data;
    }

    public function productionResults($production_id, $total_supplies, $biaya_listrik, $biaya_operator)
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
        $harga_listrik = round(($biaya_listrik / $total_kredit_produksi), 2);
        $harga_pegawai = round(($biaya_operator / $total_kredit_produksi), 2);

        // update beban biaya dari tiap produksi detail
        foreach ($data_production_results as $production) {
            DB::table("master_qr_code")
                ->where('id_barang', $production->id_barang)
                ->where('kode_batang_master_qr_code', $production->kode_batang_produksi_detail)
                ->update([
                    'produksi_master_qr_code' => $harga_produksi,
                    'listrik_master_qr_code' => $harga_listrik,
                    'pegawai_master_qr_code' => $harga_pegawai,
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
                                                barang.id_akun,
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

    public function journalHpp(Request $request)
    {
        try {
            DB::beginTransaction();
            $no_transaksi = $request->no_transaksi;
            $id_cabang = $request->id_cabang;
            $void = $request->void;

            $data_produksi = DB::table('produksi')->where('nama_produksi', $no_transaksi)->first();

            if (empty($data_produksi)) {
                DB::rollBack();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal Hpp data. Please re-check no_transaksi, Produksi " . $no_transaksi . " not found ",
                ], 400);
            }

            $id_produksi = $data_produksi->id_produksi;

            // tahap 1
            $data_production_supplies = $this->productionSupplies($id_produksi);

            if ($data_production_supplies == false) {
                DB::rollBack();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal Hpp data. Data Produksi " . $no_transaksi . " not found ",
                ], 400);
            }

            // tahap 2 dan 3
            $data_production_cost = $this->productionCost($id_produksi, $id_cabang);

            if ($data_production_cost == false) {
                DB::rollBack();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal Hpp data. Data Beban Produksi " . $no_transaksi . " not found ",
                ], 400);
            }

            $total_supplies = $data_production_supplies['total_supplies'];
            $biaya_listrik = $data_production_cost['biaya_listrik'];
            $biaya_operator = $data_production_cost['biaya_operator'];

            // tahap 4
            $data_production_results = $this->productionResults($id_produksi, $total_supplies, $biaya_listrik, $biaya_operator);

            // init data jurnal
            $data_production = DB::table('produksi')->where('id_produksi', $id_produksi)->first();

            $id_transaksi = $data_production->nama_produksi;
            $data_pemakaian = $data_production_supplies['data_supplies'];
            $data_hasil = $data_production_results['data_results'];
            $user_data = Auth::guard('api')->user();

            if (count($data_hasil) < 1) {
                DB::rollBack();
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal Hpp data. Data Hasil Produksi empty",
                ], 400);
            }

            $data = [
                'id_transaksi' => $id_transaksi,
                'cabang' => $id_cabang,
                'data_pemakaian' => $data_pemakaian,
                'biaya_listrik' => $biaya_listrik,
                'biaya_operator' => $biaya_operator,
                'data_hasil' => $data_hasil,
                'user_data' => $user_data,
                'void' => $void,
            ];

            // tahap 5
            $store_data = $this->storeHppJournal($data);
            if ($store_data) {
                return response()->json([
                    "result" => true,
                    "code" => 200,
                    "message" => "Successfully stored Jurnal Hpp data",
                ], 200);
            } else {
                return response()->json([
                    "result" => false,
                    "code" => 400,
                    "message" => "Error when store Jurnal Hpp data",
                ], 400);
            }
        } catch (\Throwable $th) {
            dd(json_encode($th));
        }

        $id_produksi = $data_produksi->id_produksi;

        // tahap 1
        $data_production_supplies = $this->productionSupplies($id_produksi);

        if ($data_production_supplies == false) {
            DB::rollBack();
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal Hpp data. Data Produksi " . $no_transaksi . " not found ",
            ], 400);
        }

        // tahap 2 dan 3
        $data_production_cost = $this->productionCost($id_produksi, $id_cabang);

        if ($data_production_cost == false) {
            DB::rollBack();
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal Hpp data. Data Beban Produksi " . $no_transaksi . " not found ",
            ], 400);
        }

        $total_supplies = $data_production_supplies['total_supplies'];
        $biaya_listrik = $data_production_cost['biaya_listrik'];
        $biaya_operator = $data_production_cost['biaya_operator'];
        $kwh_listrik = $data_production_cost['kwh_listrik'];
        $daya_mesin = $data_production_cost['daya_mesin'];
        $tenaga_kerja = $data_production_cost['tenaga_kerja'];
        $jumlah_pegawai = $data_production_cost['jumlah_pegawai'];
        $nominal_listrik = $data_production_cost['nominal_listrik'];
        $nominal_gaji = $data_production_cost['nominal_gaji'];

        // tahap 4
        $data_production_results = $this->productionResults($id_produksi, $total_supplies, $biaya_listrik, $biaya_operator);

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
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal Hpp data. Data Hasil Produksi empty",
            ], 400);
        }

        $data = [
            'id_transaksi' => $id_transaksi,
            'cabang' => $id_cabang,
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
            'void' => $void,
            'note' => $id_transaksi . ' ==> ' . $id_transaksi_hasil_produksi,
            'tanggal_hasil_produksi' => $tanggal_hasil_produksi,
        ];

        // tahap 5
        $store_data = $this->storeHppJournal($data);

        if ($store_data) {
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully stored Jurnal Hpp data",
            ], 200);
        } else {
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal Hpp data",
            ], 400);
        }
    }

    public function getPemakaian($tanggal_closing)
    {
        $data_pemakaian = DB::table("pemakaian_header as head")
            ->join('pemakaian_detail as det', 'head.id_pemakaian', 'det.id_pemakaian')
            ->join('barang as good', 'good.id_barang', 'det.id_barang')
            ->join('master_qr_code as qr', 'qr.kode_batang_master_qr_code', 'det.kode_batang')
            ->selectRaw('head.kode_pemakaian,
                det.id_barang,
                good.id_akun,
                ROUND(SUM(jumlah),2) as jumlah,
                ROUND(SUM(ROUND(qr.listrik_master_qr_code * det.jumlah, 2) + ROUND(qr.pegawai_master_qr_code * det.jumlah, 2) + ROUND(qr.produksi_master_qr_code * det.jumlah, 2) + ROUND(qr.beli_master_qr_code * det.jumlah, 2) + ROUND(qr.biaya_beli_master_qr_code * det.jumlah, 2)), 2) as total')
            ->where('head.tanggal', $tanggal_closing)
            ->orderBy('head.id_pemakaian', 'ASC')
            ->groupBy('det.id_pemakaian', 'det.id_barang')
            ->get();

        $data_results = [];

        foreach ($data_pemakaian as $pemakaian) {
            array_push($data_results, [
                'nama_transaksi' => $pemakaian->kode_pemakaian,
                'akun' => $pemakaian->id_akun,
                'notes' => $pemakaian->id_barang,
                'debet' => 0,
                'kredit' => round($pemakaian->total, 2),
            ]);
        }

        return $data_results;
    }

    public function jurnalClosingPemakaian(Request $request)
    {
        DB::beginTransaction();
        $id_cabang = $request->id_cabang;
        $tanggal = $request->periode_closing;

        // Pemakaian
        $data_pemakaian = $this->getPemakaian($tanggal);

        $data_hasil = $data_pemakaian;
        $user_data = Auth::guard('api')->user();

        $data = [
            'cabang' => $id_cabang,
            'data_hasil' => $data_hasil,
            'user_data' => $user_data,
        ];

        // dd($data);

        $store_data = $this->storeClosingJournalPemakaian($data);

        if ($store_data) {
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully stored Jurnal Closing Pemakaian data",
            ], 200);
        } else {
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal Closing Pemakaian data",
            ], 400);
        }
    }

    public function storeClosingJournalPemakaian($data)
    {
        try {
            // Init Data
            $hasil = $data['data_hasil']; // Diisi dengan data hasil
            $journalDate = date('Y-m-d');
            $journalDate = date("Y-m-t", strtotime($journalDate));
            $journalType = "ME";
            $cabangID = $data['cabang'];
            $noteHeader = "";
            $userData = $data['user_data'];
            $userRecord = $userData->id_pengguna;
            $userModified = $userData->id_pengguna;
            $dateRecord = date('Y-m-d H:i:s');

            $get_akun_hpp_pemakaian = Setting::where("id_cabang", $cabangID)->where("code", "HPP Pemakaian")->first();

            $header = new JurnalHeader();
            $header->id_cabang = $cabangID;
            $header->jenis_jurnal = $journalType;
            $header->id_transaksi = null;
            $header->catatan = $noteHeader;
            $header->void = 0;
            $header->tanggal_jurnal = $journalDate;
            $header->user_created = $userRecord;
            $header->user_modified = $userModified;
            $header->dt_created = $dateRecord;
            $header->dt_modified = $dateRecord;
            $header->kode_jurnal = $this->generateJournalCode($cabangID, $journalType);
            if (!$header->save()) {
                DB::rollback();
                Log::error("Error when storing journal header.");
                return false;
            }

            Log::debug($header);

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
                $detail->user_created = $userRecord;
                $detail->user_modified = $userModified;
                $detail->dt_created = $dateRecord;
                $detail->dt_modified = $dateRecord;
                if (!$detail->save()) {
                    DB::rollback();
                    Log::error("Error when storing journal detail.");
                    return false;
                }
                Log::debug($detail);

                $total_debet += $detail->debet;
                $total_credit += $detail->credit;
                $list_transaksi .= $value['nama_transaksi'] . ';';
                $index++;
            }

            $list_transaksi = substr_replace($list_transaksi, "", -1);
            $list_transaksi = explode(';', $list_transaksi);
            $list_transaksi = array_unique($list_transaksi);

            $transaksi = '';
            foreach ($list_transaksi as $key => $value) {
                $transaksi .= $value;
                if ($key < count($list_transaksi) - 1) {
                    $transaksi .= ', ';
                }
            }

            // pembulatan
            if ($total_debet != $total_credit) {
                $selisih = $total_credit - $total_debet;

                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $index;
                $detail->id_akun = 'Test';
                // $detail->id_akun = $get_akun_hpp_pemakaian->value2;
                $detail->keterangan = "Pembulatan Pemakaian Barang " . $transaksi;
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
                    Log::error("Error when storing journal detail pembulatan.");
                    return false;
                }
                Log::debug($detail);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            $message = "Error when storing Journal Closing Pemakaian";
            Log::error($message);
            Log::error($e);
            return false;
        }
    }

    public function getReturJual($tanggal_closing)
    {
        $data_retur_jual = DB::table("retur_penjualan as head")
            ->join('retur_penjualan_detail as det', 'head.id_retur_penjualan', 'det.id_retur_penjualan')
            ->join('barang as good', 'good.id_barang', 'det.id_barang')
            ->join('master_qr_code as qr', 'qr.kode_batang_master_qr_code', 'det.kode_batang_retur_penjualan_detail')
            ->selectRaw('det.nama_retur_penjualan_detail,
                det.id_barang,
                good.id_akun,
                ROUND(SUM(jumlah_retur_penjualan_detail),2) as jumlah_retur_penjualan_detail,
                ROUND(SUM(ROUND(qr.listrik_master_qr_code * det.jumlah_retur_penjualan_detail, 2) + ROUND(qr.pegawai_master_qr_code * det.jumlah_retur_penjualan_detail, 2) + ROUND(qr.produksi_master_qr_code * det.jumlah_retur_penjualan_detail, 2) + ROUND(qr.beli_master_qr_code * det.jumlah_retur_penjualan_detail, 2) + ROUND(qr.biaya_beli_master_qr_code * det.jumlah_retur_penjualan_detail, 2)), 2) as total')
            ->where('head.tanggal_retur_penjualan', $tanggal_closing)
            ->orderBy('head.id_retur_penjualan', 'ASC')
            ->groupBy('det.id_retur_penjualan', 'det.id_barang')
            ->get();

        $data_results = [];

        foreach ($data_retur_jual as $retur_jual) {
            array_push($data_results, [
                'nama_transaksi' => $retur_jual->nama_retur_penjualan_detail,
                'akun' => $retur_jual->id_akun,
                'notes' => $retur_jual->id_barang,
                'debet' => round($retur_jual->total, 2),
                'kredit' => 0,
            ]);
        }

        return $data_results;
    }

    public function jurnalClosingReturJual(Request $request)
    {
        DB::beginTransaction();
        $id_cabang = $request->id_cabang;
        $tanggal = $request->periode_closing;

        // Retur jual
        $data_retur_jual = $this->getReturJual($tanggal);

        $data_hasil = $data_retur_jual;
        $user_data = Auth::guard('api')->user();

        $data = [
            'cabang' => $id_cabang,
            'data_hasil' => $data_hasil,
            'user_data' => $user_data,
        ];

        // dd($data);

        $store_data = $this->storeClosingJournalReturJual($data);

        if ($store_data) {
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully stored Jurnal Closing Retur Jual data",
            ], 200);
        } else {
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal Closing Retur Jual data",
            ], 400);
        }
    }

    public function storeClosingJournalReturJual($data)
    {
        try {
            // Init Data
            $hasil = $data['data_hasil']; // Diisi dengan data hasil
            $journalDate = date('Y-m-d');
            $journalDate = date("Y-m-t", strtotime($journalDate));
            $journalType = "ME";
            $cabangID = $data['cabang'];
            $noteHeader = "";
            $userData = $data['user_data'];
            $userRecord = $userData->id_pengguna;
            $userModified = $userData->id_pengguna;
            $dateRecord = date('Y-m-d H:i:s');

            $get_akun_hpp_retur_jual = Setting::where("id_cabang", $cabangID)->where("code", "HPP Retur Penjualan")->first();

            $header = new JurnalHeader();
            $header->id_cabang = $cabangID;
            $header->jenis_jurnal = $journalType;
            $header->id_transaksi = null;
            $header->catatan = $noteHeader;
            $header->void = 0;
            $header->tanggal_jurnal = $journalDate;
            $header->user_created = $userRecord;
            $header->user_modified = $userModified;
            $header->dt_created = $dateRecord;
            $header->dt_modified = $dateRecord;
            $header->kode_jurnal = $this->generateJournalCode($cabangID, $journalType);
            if (!$header->save()) {
                DB::rollback();
                Log::error("Error when storing journal header.");
                return false;
            }

            Log::debug($header);

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
                $detail->user_created = $userRecord;
                $detail->user_modified = $userModified;
                $detail->dt_created = $dateRecord;
                $detail->dt_modified = $dateRecord;
                if (!$detail->save()) {
                    DB::rollback();
                    Log::error("Error when storing journal detail.");
                    return false;
                }
                Log::debug($detail);

                $total_debet += $detail->debet;
                $total_credit += $detail->credit;
                $list_transaksi .= $value['nama_transaksi'] . ';';
                $index++;
            }

            $list_transaksi = substr_replace($list_transaksi, "", -1);
            $list_transaksi = explode(';', $list_transaksi);
            $list_transaksi = array_unique($list_transaksi);

            $transaksi = '';
            foreach ($list_transaksi as $key => $value) {
                $transaksi .= $value;
                if ($key < count($list_transaksi) - 1) {
                    $transaksi .= ', ';
                }
            }

            // pembulatan
            if ($total_debet != $total_credit) {
                $selisih = $total_credit - $total_debet;

                $detail = new JurnalDetail();
                $detail->id_jurnal = $header->id_jurnal;
                $detail->index = $index;
                $detail->id_akun = 'Test';
                // $detail->id_akun = $get_akun_hpp_retur_jual->value2;
                $detail->keterangan = "Pembulatan Persediaan jurnal penjualan " . $transaksi;
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
                    Log::error("Error when storing journal detail pembulatan.");
                    return false;
                }
                Log::debug($detail);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            $message = "Error when storing Journal Closing Retur Jual";
            Log::error($message);
            Log::error($e);
            return false;
        }
    }

    public function storeFcmToken(Request $request)
    {
        $siscaToken = $request->token;
        $fcmToken = $request->fcm_token;

        if ($siscaToken == '' || $fcmToken == '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pastikan parameter yang dibutuhkan harus lengkap',
            ], 500);
        }
        try {
            $dataToken = \App\Models\UserToken::where('nama_token_pengguna', $siscaToken)->first();
            if (!$dataToken) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token sisca tidak ditemukan',
                ], 500);
            }

            $dataToken->fcm_token = $fcmToken;
            $dataToken->save();

            return response()->json([
                'status' => 'success',
                'message' => 'FCM token berhasil disimpan',
            ], 200);
        } catch (\Exception $th) {
            Log::error($th);
            return response()->json([
                'status' => 'error',
                'message' => 'FCM token gagal disimpan',
            ], 500);
        }
    }

    public function stokmin(Request $request)
    {
        $id_cabang = $request->id_cabang;
        $setting = Setting::where("id_cabang", $id_cabang)->where("code", 'like', "Stok Min %")->select('code', 'value1', 'value2')->get()->toArray();
        $settingValue1 = array_column($setting, 'value1', 'code');
        $setting = array_column($setting, 'value2', 'code');

        $settingBrgArr = json_decode($settingValue1['Stok Min Khusus'], true);
        $settingBrgArr = array_column($settingBrgArr, 'stokMin', 'id_barang');
        $today = Carbon::today();
        $setting['penj_sampai'] = $today->toDateString();
        $setting['penj_dari'] = $today->subMonths(intval($setting['Stok Min Range']))->toDateString();
        session(['stokMin' => $setting]);
        \DB::unprepared(\DB::raw("DROP TEMPORARY TABLE IF EXISTS tTotalPenjualanInfo"));
        \DB::insert(\DB::raw("CREATE TEMPORARY TABLE tTotalPenjualanInfo(id_barang int(11) NOT NULL,nama_barang varchar(200), total_jual decimal(15,6),
        total_jual_per_bulan decimal(15,6),plus_persen decimal(15,6),per_bulan_plus_persen decimal(15,6),avg_prorate double,
        pemakaian_per_barang_jadi double)"));
        self::getSalesWithProrate($request->id, $id_cabang, 1);
        $debug = false;
        if (!empty($request->debug) && $request->debug == true) {
            $debug = \DB::table('tTotalPenjualanInfo AS ttp')->get();
        }
        $total = \DB::table('tTotalPenjualanInfo AS ttp')
            ->select(\DB::raw('SUM(ttp.pemakaian_per_barang_jadi)'))->value('total');
        $barang = \App\Barang::find($request->id);
        if ($barang->keterangan_barang == '1') {
            $total *= floatval($setting['Stok Min Import']);
        } else {
            $total *= floatval($setting['Stok Min Lokal']);
        }
        $respon = [
            'total' => round($total, 4),
        ];
        $setting['Stok Min Khusus'] = null;
        $setting['stok_min_hitung'] = $respon['total'];
        if (array_key_exists($request->id, $settingBrgArr)) {
            $setting['Stok Min Khusus'] = $settingBrgArr[$request->id];
            if ($respon['total'] < $setting['Stok Min Khusus']) {
                $respon['total'] = $setting['Stok Min Khusus'];
            }
        }
        if ($debug !== false) {
            $respon['debug'] = $debug;
        }
        self::storeStokMinHitung($setting, $request->id, $id_cabang, $respon['total']);
        return response()->json($respon);
    }

    private function storeStokMinHitung($setting, $id_barang, $id_cabang, $jumlah)
    {
        $bulan = date('m');
        $tahun = date('Y');
        try {
            StokMinimalHitung::updateOrInsert(
                [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'id_barang' => $id_barang,
                    'id_cabang' => $id_cabang,
                ],
                [
                    'jumlah' => $jumlah,
                    'range' => $setting['Stok Min Range'],
                    'persen' => $setting['Stok Min Persen'],
                    'lokal' => $setting['Stok Min Lokal'],
                    'import' => $setting['Stok Min Import'],
                    'stok_min_khusus' => $setting['Stok Min Khusus'],
                    'stok_min_hitung' => $setting['stok_min_hitung'],
                    'penj_dari' => $setting['penj_dari'],
                    'penj_sampai' => $setting['penj_sampai'],
                ]
            );
            $stokHeader = DB::table('stok_minimal_hitung')->where([
                'bulan' => $bulan,
                'tahun' => $tahun,
                'id_barang' => $id_barang,
                'id_cabang' => $id_cabang,
            ])->first();
            if (!empty($stokHeader)) {
                DB::table('stok_minimal_hitung_detil')->where('stok_minimal_hitung_id', $stokHeader->id)->delete();
                DB::table('stok_minimal_hitung_detil')
                    ->insert(
                        DB::table('tTotalPenjualanInfo')
                            ->selectRaw("{$stokHeader->id} as stok_minimal_hitung_id,id_barang,nama_barang,total_jual,total_jual_per_bulan,plus_persen,per_bulan_plus_persen,avg_prorate,pemakaian_per_barang_jadi")
                            ->get()->map(function ($item) {
                                return (array) $item;
                            })->toArray()
                    );
            }
        } catch (\Exception $e) {
            $message = "Error when storing stok minimal hitung";
            Log::error($message);
            Log::error($e);
        }
    }

    private function getSalesWithProrate($id_barang, $id_cabang, $value)
    {
        $childsub = \DB::table('bom_detail AS bd')
            ->select(
                'b.id_barang',
                'b.keterangan_bom',
                'brg.nama_barang',
                \DB::raw('SUM(bd.jumlah_bom_detail) AS total_pemakaian'),
                'b.jumlah_bom',
                \DB::raw('(SUM(bd.jumlah_bom_detail)/b.jumlah_bom) AS prorate')
            )
            ->join('bom AS b', 'bd.id_bom', '=', 'b.id_bom')
            ->join('barang AS brg', 'brg.id_barang', '=', 'b.id_barang')
            ->where(DB::raw("bd.id_barang = $id_barang and b.status_bom = 1"))
            ->groupBy('bd.id_barang', 'b.id_bom');
        $child = \DB::table(\DB::raw("({$childsub->toSql()}) as a"))
            ->select('a.*', \DB::raw('AVG(a.prorate) AS avg_prorate'))
            ->groupBy('a.id_barang')->get();

        if (empty($child)) {
            return;
        }
        $stokMin = session('stokMin');

        $jual = \DB::table('penjualan AS p')
            ->select(
                'brg.nama_barang',
                \DB::raw('SUM(if(pd.id_satuan_barang != 6,pd.jumlah_penjualan_detail * pd.sg_penjualan_detail,pd.jumlah_penjualan_detail)) AS total_jual'),
                \DB::raw('(SUM(if(pd.id_satuan_barang != 6,pd.jumlah_penjualan_detail * pd.sg_penjualan_detail,pd.jumlah_penjualan_detail))/' . intval($stokMin['Stok Min Range']) . ') AS total_jual_per_bulan')
            )
            ->join('penjualan_detail AS pd', 'pd.id_penjualan', '=', 'p.id_penjualan')
            ->join('barang AS brg', 'brg.id_barang', '=', 'pd.id_barang')
            ->where('pd.id_barang', $id_barang)
            ->where('p.id_cabang', $id_cabang)
            ->whereRaw('p.tanggal_penjualan BETWEEN "' . $stokMin['penj_dari'] . '" AND "' . $stokMin['penj_sampai'] . '"')->get()->toArray();
        if (!empty($jual[0]->total_jual)) {
            $persen = floatval($jual[0]->total_jual_per_bulan) * (floatval($stokMin['Stok Min Persen']) / 100);
            $plusPersen = floatval($jual[0]->total_jual_per_bulan) + $persen;
            $pemakaian = $plusPersen * $value;
            \DB::table('tTotalPenjualanInfo')->insert([
                "id_barang" => $id_barang,
                "nama_barang" => $jual[0]->nama_barang,
                "total_jual" => $jual[0]->total_jual,
                "total_jual_per_bulan" => $jual[0]->total_jual_per_bulan,
                "plus_persen" => $persen,
                "per_bulan_plus_persen" => $plusPersen,
                "avg_prorate" => $value,
                "pemakaian_per_barang_jadi" => $pemakaian,
            ]);
        }

        foreach ($child as $itemChild) {
            if (strpos(strtolower($itemChild->keterangan_bom), 'blending') || $itemChild->id_barang == $id_barang) {
                continue;
            }
            $new_val = $value * floatval($itemChild->avg_prorate);
            self::getSalesWithProrate($itemChild->id_barang, $id_cabang, $new_val);
        }
    }
}
