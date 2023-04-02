<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        // $user_id    = $request->user_id;
        // if($user_id != ''){
        //     $user       = User::where('id_pengguna', $user_id)->first();
        //     $token      = UserToken::where('id_pengguna', $user_id)->whereRaw("waktu_habis_token_pengguna > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", Carbon::now()->format('Y-m-d H:i:s'))->first();

        //     $data = [
        //         "pageTitle" => "SCA Accounting | Dashboard",
        //         "user" => $user
        //     ];

        //     $sql = "SELECT
        //         a.id_pengguna,
        //         a.id_grup_pengguna,
        //         d.id_menu,
        //         d.nama_menu,
        //         c.lihat_akses_menu,
        //         c.tambah_akses_menu,
        //         c.ubah_akses_menu,
        //         c.hapus_akses_menu,
        //         c.cetak_akses_menu
        //     FROM
        //         pengguna a,
        //         grup_pengguna b,
        //         akses_menu c,
        //         menu d
        //     WHERE
        //         a.id_grup_pengguna = b.id_grup_pengguna
        //         AND b.id_grup_pengguna = c.id_grup_pengguna
        //         AND c.id_menu = d.id_menu
        //         AND a.id_pengguna = $user_id
        //         AND d.keterangan_menu = 'Accounting'
        //         AND d.status_menu = 1";
        //     $access = DB::connection('mysql')->select($sql);

        //     $user_access = array();
        //     foreach ($access as $value) {
        //         $user_access[$value->nama_menu] = ['show' => $value->lihat_akses_menu, 'create' => $value->tambah_akses_menu, 'edit' => $value->ubah_akses_menu, 'delete' => $value->hapus_akses_menu, 'print' => $value->cetak_akses_menu];
        //     }

        //     if ($token && $request->session()->has('token') == false) {
        //         $request->session()->put('token', $token->nama_token_pengguna);
        //         $request->session()->put('user', $user);
        //         $request->session()->put('access', $user_access);
        //     } else if ($request->session()->has('token')) {
        //     } else {
        //         $request->session()->flush();
        //     }

        //     if ($request->session()->has('token')) {
        //         return view('master', $data);
        //     } else {
        //         return view('exceptions.forbidden');
        //     }
        // }else{
        //     $request->session()->flush();
        //     return view('exceptions.forbidden');
        // }
        $data = [
            "pageTitle" => "SCA Accounting | Dashboard",
        ];
        return view('master', $data);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->to(env('OLD_URL_ROOT') . '#keluar');
    }
}
