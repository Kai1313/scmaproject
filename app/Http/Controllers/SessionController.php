<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            "pageTitle" => "SCA Accounting | Dashboard",
        ];
        return view('master', $data);
    }

    public function logout(Request $request)
    {
        $data = [
            "pageTitle" => "SCA Accounting | Dashboard",
        ];
        $request->session()->flush();
        // return view('master', $data);
        return redirect()->to(env('OLD_URL_ROOT') . '#keluar');
    }
}
