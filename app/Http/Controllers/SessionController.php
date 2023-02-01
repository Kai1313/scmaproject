<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserToken;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $user_id    = $request->user_id;
        $user       = User::where('id_pengguna', $user_id)->first();
        $token      = UserToken::where('id_pengguna', $user_id)->whereRaw("waktu_habis_token_pengguna > STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s')", Carbon::now()->format('Y-m-d H:i:s'))->first();

        $data = [
            "pageTitle"=>"SCA Accounting | Dashboard",
            "user"=>$user
        ];

        if($token && $request->session()->has('token') == false){
            $request->session()->put('token', $token->nama_token_pengguna);
            $request->session()->put('user', $user);
        }else if($request->session()->has('token')){

        }else{
            $request->session()->flush();
        }
        
        if ($request->session()->has('token')) {
            return view('master', $data);
        } else {
            return view('exceptions.forbidden');
        }
    }
    
    public function logout(Request $request)
    {
        $request->session()->flush();
        return view('goodbye');
    }
}
