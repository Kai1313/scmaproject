<?php

function normalizeNumber($number = 0)
{
    if (strpos($number, ',')) {
        $number = str_replace(',', '.', str_replace('.', '', $number));
    } else {
        $number = str_replace('.', '', $number);
    }

    return $number;
}

function handleNull($number)
{
    return $number ? $number : 0;
}

function checkUserSession($request, $alias_menu, $type)
{
    $user_id = $request->user_id;
    if ($user_id != '' && session()->has('token') == false || session()->has('token') == true) {
        if (session()->has('token') == true) {
            $user_id = session()->get('user')->id_pengguna;
        }

        $user = \App\Models\User::where('id_pengguna', $user_id)->first();
        $token = \App\Models\UserToken::where('id_pengguna', $user_id)->where('status_token_pengguna', 1)->whereRaw("waktu_habis_token_pengguna > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", \Carbon\Carbon::now()->format('Y-m-d H:i:s'))->first();

        $idGroup = $user->id_grup_pengguna;
        $menu_access = DB::table('menu')
            ->select(
                'menu.id_menu',
                'kepala_menu',
                'alias_menu',
                'lihat_akses_menu as show',
                'tingkatan_menu',
                'nama_menu',
                'tambah_akses_menu as create',
                'ubah_akses_menu as edit',
                'hapus_akses_menu as delete',
                'cetak_akses_menu as print'
            )
            ->leftJoin('akses_menu', 'menu.id_menu', '=', 'akses_menu.id_menu')
            ->where('akses_menu.id_grup_pengguna', $idGroup)
            ->where('lihat_akses_menu', '1')
            ->where('alias_menu', 'not like', '%detail')
            ->get();

        $arrayMenuAccess = [];
        foreach ($menu_access as $menu) {
            $arrayMenuAccess[$menu->alias_menu] = $menu;
        }

        if ($token && session()->has('token') == false) {
            session()->put('token', $token->nama_token_pengguna);
            session()->put('user', $user);
            session()->put('access', $arrayMenuAccess);
        } else if (session()->has('token')) {
        } else {
            session()->flush();
        }

        return checkAccessMenu($alias_menu, $type);
    } else {
        session()->flush();
        return '0';
    }
}

function checkAccessMenu($alias_menu = 'home', $type = 'show')
{
    $datas = session()->get('access') ? session()->get('access') : [];
    $hasToken = session()->has('token');
    foreach ($datas as $data) {
        if ($hasToken == true && $data->alias_menu == $alias_menu && $data->{$type} == 1) {
            return '1';
        }
    }

    return '0';
}
