<?php

use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $user = User::where('id_pengguna', $user_id)->first();
        $token = UserToken::where('id_pengguna', $user_id)->where('status_token_pengguna', 1)->whereRaw("waktu_habis_token_pengguna > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", \Carbon\Carbon::now()->format('Y-m-d H:i:s'))->first();

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

        $aksesCabang = DB::table('pengguna')->select('cabang.id_cabang', 'nama_cabang', 'gudang.id_gudang', 'nama_gudang', 'kode_cabang', 'kode_gudang')
            ->join('akses_gudang', 'pengguna.id_grup_pengguna', '=', 'akses_gudang.id_grup_pengguna')
            ->join('gudang', 'akses_gudang.id_gudang', '=', 'gudang.id_gudang')
            ->join('cabang', 'gudang.id_cabang', '=', 'cabang.id_cabang')
            ->where('status_akses_gudang', 1)->where('status_cabang', 1)->where('status_gudang', 1)
            ->where('id_pengguna', $user_id)
            ->get();

        $arrayCabang = [];
        foreach ($aksesCabang as $ac) {
            if (isset($arrayCabang[$ac->id_cabang])) {
                $arrayCabang[$ac->id_cabang]['gudang'][] = ['id' => $ac->id_gudang, 'text' => $ac->kode_gudang . ' - ' . $ac->nama_gudang];
            } else {
                $arrayCabang[$ac->id_cabang] = [
                    'id' => $ac->id_cabang,
                    'text' => $ac->kode_cabang . ' - ' . $ac->nama_cabang,
                    'gudang' => [
                        ['id' => $ac->id_gudang, 'text' => $ac->kode_gudang . ' - ' . $ac->nama_gudang],
                    ],
                ];
            }
        }

        $arrayCabang = array_values($arrayCabang);
        if ($token && session()->has('token') == false) {
            session()->put('token', $token->nama_token_pengguna);
            session()->put('user', $user);
            session()->put('access', $arrayMenuAccess);
            session()->put('access_cabang', $arrayCabang);
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

function replaceMessage($array, $message)
{
    foreach ($array as $key => $val) {
        $message = str_replace($key, $val, $message);
    }

    return $message;
}

function getCabangForReport()
{
    $array = [];
    $cabang = session()->get('access_cabang');
    $tempGudang = [];
    if (count($cabang) > 1) {
        foreach ($cabang as $ca) {
            foreach ($ca['gudang'] as $g) {
                $tempGudang[] = $g;
            }
        }
        $array[] = ['id' => implode(',', array_column($cabang, 'id')), 'text' => 'Semua Cabang', 'gudang' => $tempGudang];
    }

    foreach ($cabang as $c) {
        $array[] = $c;
    }

    return $array;
}

function getPemasokForReport()
{
    $pelanggan = DB::table('pemasok')
        ->select('id_pemasok','kode_pemasok', 'nama_pemasok')
        ->get();
    $array[] = ['id' => 'all', 'text' => 'Semua Pemasok'];
    foreach ($pelanggan as $ca) {
        $array[] = ['id'=>$ca->id_pemasok,'text'=> '('.$ca->kode_pemasok.') '.$ca->nama_pemasok];
    }
    return $array;
}

function getPelangganForReport()
{
    $pelanggan = DB::table('pelanggan')
        ->select('id_pelanggan','kode_pelanggan', 'nama_pelanggan')
        ->get();
        $array[] = ['id' => 'all', 'text' => 'Semua Pelanggan'];
    foreach ($pelanggan as $ca) {
        $array[] = ['id'=>$ca->id_pelanggan,'text'=> '('.$ca->kode_pelanggan.') '.$ca->nama_pelanggan];
    }
    return $array;
}

function formatNumber($number, $prefix = 0)
{
    $number = number_format($number, 4, ',', '.');
    $explode = explode(',', $number);
    $koma = '';
    if (count($explode) > 1) {
        $reverse = (int) strrev($explode[1]);
        $koma = (string) $reverse > 0 ? strrev($reverse) : '';
    }

    if ($prefix > 0) {
        if (strlen($koma) < $prefix) {
            $sisa = $prefix - strlen($koma);
            for ($i = 0; $i < $sisa; $i++) {
                $koma .= '0';
            }
        } else {
            $newNumber = normalizeNumber($explode[0]) . '.' . $koma;
            return number_format($newNumber, 2, ',', '.');
        }
    }

    if ($koma != '') {
        $koma = ',' . $koma;
    }

    return $explode[0] . $koma;
}

function getSetting($code, $key = 'value1')
{
    $data = \DB::table('setting')->where('code', $code)->value($key);

    return $data;
}

function getCabang()
{
    $user_id = session()->get('user')->id_pengguna;
    $cabang = DB::table('pengguna')
        ->selectRaw('DISTINCT
            gudang.id_cabang,
            cabang.nama_cabang,
            cabang.kode_cabang ')
        ->join('akses_gudang', 'akses_gudang.id_grup_pengguna', 'pengguna.id_grup_pengguna')
        ->join('gudang', 'gudang.id_gudang', 'akses_gudang.id_gudang')
        ->join('cabang', 'cabang.id_cabang', 'gudang.id_cabang')
        ->where('id_pengguna', $user_id)
        ->where('cabang.status_cabang', 1)
        ->where('akses_gudang.status_akses_gudang', 1)
        ->get();

    return $cabang;
}
