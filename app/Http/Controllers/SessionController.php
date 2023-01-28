<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserToken;
use App\Models\User;

use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $user_id    = $request->user_id;
        $user       = User::where('id_pengguna', $user_id)->first();
        $token      = UserToken::where('id_pengguna', $user_id)->first();

        if($user && $request->session()->has('token') == false){
            $request->session()->put('token', $token->nama_token_pengguna);
            $request->session()->put('user', $user);
        }
        
        if ($request->session()->has('token')) {
            return view('master')->with('user', $user);
        } else {
            return view('exceptions.forbidden');
        }
    }
    
    public function logout(Request $request)
    {
        $request->session()->flush();
    }
}
