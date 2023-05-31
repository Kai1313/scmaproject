<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
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

    public function tesFirebase()
    {
        $url = "https://fcm.googleapis.com/fcm/send";
        $tokensender = env('FIREBASE_SERVER_KEY');

        $fcmToken = \DB::table('token_pengguna')->where('fcm_token', '!=', null)
            ->where('status_token_pengguna', 1)
            ->where('waktu_habis_token_pengguna', '>=', date('Y-m-d H:i:s'))
            ->pluck('fcm_token');

        $message = [
            'data' => [
                'body' => 'pesan berhasil ditampilkan',
                'title' => 'Judul pesan firebase',
            ],
        ];

        $fieldToken = ['registration_ids' => $fcmToken];

        $client = new Client();
        $req = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'key=' . $tokensender,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode(array_merge($fieldToken, $message)),
        ]);
        return response()->json($req);
    }
}
