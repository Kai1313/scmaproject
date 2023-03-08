<?php

namespace App\Http\Controllers;

use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
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
            $token = $user->createToken('Token Passport User [' . $user->id_pengguna . '] ' .$user->nama_pengguna)->accessToken;
            $response = [
                'token' => $token
            ];
            return response($response, 200);
        } else {
            $response = ["message" => 'User does not exist'];
            return response($response, 500);
        }
    }

    public function profile(Request $request)
    {
        return response()->json([
            'user' => Auth::guard('api')->user()
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::guard('api')->user()) {
            Auth::guard('api')->user()->tokens()->delete();
            return 'You are logged out';
        } else {
            return 'Logged out failed';
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
                ]);
            }

            // detail
            $data_akun_uang_muka_penjualan = DB::table('setting')->where('id', 'UM Penjualan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_uang_muka_penjualan)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting UM Penjualan not found"
                ]);
            }

            $data_akun_ppn_keluaran = DB::table('setting')->where('id', 'PPN Keluaran')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_ppn_keluaran)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Keluaran not found"
                ]);
            }

            $akun_slip = $data_slip->id_akun;
            $akun_uang_muka_penjualan = $data_akun_uang_muka_penjualan->value1;
            $akun_ppn_keluaran = $data_akun_ppn_keluaran->value1;
            $total = $request->total;
            $uang_muka = $request->uang_muka;
            $nominal_ppn = $request->ppn;

            $jurnal_detail = [
                [
                    'akun'          => $akun_slip,
                    'debet'         => $total,
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis ' . $jurnal_type_detail . ' UM Penjualan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
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
                ]);
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

                        Log::debug(json_encode($detail));

                        if (!$detail->save()) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "code" => 400,
                                "message" => "Error when store Jurnal data on table detail"
                            ]);
                        }

                        $index++;
                    }
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully stored Jurnal data",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when store Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal data",
                "exception" => $e
            ]);
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
            $id_pelanggan = $request->pelanggan;
            $id_cabang = $request->cabang;
            $id_slip = $request->slip;

            $data_pelanggan = DB::table("pelanggan")->where('id_pelanggan', $id_pelanggan)->first();
            $nama_pelanggan = $data_pelanggan->nama_pelanggan;
            $catatan = 'Journal Otomatis Uang Muka Pembelian - ' . $id_transaksi . ' - ' . $nama_pelanggan;

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
                ]);
            }

            // detail
            $data_akun_uang_muka_pembelian = DB::table('setting')->where('id', 'UM Pembelian')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_uang_muka_pembelian)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting UM Pembelian not found"
                ]);
            }

            $data_akun_ppn_masukan = DB::table('setting')->where('id', 'PPN Masukan')->where('tipe', 2)->where('id_cabang', $id_cabang)->first();
            if (empty($data_akun_ppn_masukan)) {
                return response()->json([
                    "result" => false,
                    "code" => 404,
                    "message" => "Error, Setting PPN Masukan not found"
                ]);
            }

            $akun_slip = $data_slip->id_akun;
            $akun_uang_muka_pembelian = $data_akun_uang_muka_pembelian->value1;
            $akun_ppn_masukan = $data_akun_ppn_masukan->value1;
            $total = $request->total;
            $uang_muka = $request->uang_muka;
            $nominal_ppn = $request->ppn;

            $jurnal_detail = [
                [
                    'akun'          => $akun_slip,
                    'debet'         => $total,
                    'credit'        => 0,
                    'keterangan'    => 'Jurnal Otomatis ' . $jurnal_type_detail . ' UM Pembelian - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                ],
                [
                    'akun'          => $akun_uang_muka_pembelian,
                    'debet'         => 0,
                    'credit'        => $uang_muka,
                    'keterangan'    => 'Jurnal Otomatis Uang Muka Pembelian - ' . $id_transaksi . ' - ' . $nama_pelanggan,
                ],
                [
                    'akun'          => $akun_ppn_masukan,
                    'debet'         => 0,
                    'credit'        => $nominal_ppn,
                    'keterangan'    => 'Jurnal Otomatis PPN Masukan - ' . $id_transaksi . ' - ' . $nama_pelanggan,
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
                ]);
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

                        Log::debug(json_encode($detail));

                        if (!$detail->save()) {
                            DB::rollback();
                            return response()->json([
                                "result" => false,
                                "code" => 400,
                                "message" => "Error when store Jurnal data on table detail"
                            ]);
                        }

                        $index++;
                    }
                }
            }

            DB::commit();
            return response()->json([
                "result" => true,
                "code" => 200,
                "message" => "Successfully stored Jurnal data",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Error when store Jurnal data");
            Log::info($e);
            return response()->json([
                "result" => false,
                "code" => 400,
                "message" => "Error when store Jurnal data",
                "exception" => $e
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
}
