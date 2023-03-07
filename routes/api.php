<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/jurnal_otomatis')->group(function () {
    Route::post('/uangmuka_penjualan', 'ApiController@journalUangMukaPenjualan')->name('jurnal-otomatis-uangmuka-penjualan');
    Route::post('/uangmuka_pembelian', 'ApiController@journalUangMukaPembelian')->name('jurnal-otomatis-uangmuka-pembelian');
});
