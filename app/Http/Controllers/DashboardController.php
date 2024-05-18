<?php

namespace App\Http\Controllers;

use App\Menu;
use Illuminate\Http\Request;
use Log;

class DashboardController extends Controller
{
    public function getMenu($id_group_pengguna){
        try {
            $data_menu = Menu::join('akses_menu', 'akses_menu.id_menu', '=', 'menu.id_menu')
                ->where('akses_menu.id_grup_pengguna', $id_group_pengguna)
                ->get();

            $data = [
                'result' => true,
                'data' => $data_menu
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            $data = [
                'result' => false
            ];

            return response()->json($data);
        }
    }
}
