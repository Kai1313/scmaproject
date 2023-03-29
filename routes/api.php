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

Route::post('login', 'ApiController@login');

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('profile', 'ApiController@profile');
    Route::post('logout', 'ApiController@logout');

    Route::prefix('/jurnal_otomatis')->group(function () {
        Route::post('/uangmuka_penjualan', 'ApiController@journalUangMukaPenjualan')->name('jurnal-otomatis-uangmuka-penjualan');
        Route::post('/uangmuka_pembelian', 'ApiController@journalUangMukaPembelian')->name('jurnal-otomatis-uangmuka-pembelian');
        Route::post('/penjualan', 'ApiController@journalPenjualan')->name('jurnal-otomatis-penjualan');
        Route::post('/pembelian', 'ApiController@journalPembelian')->name('jurnal-otomatis-pembelian');
        Route::post('/retur_penjualan', 'ApiController@journalReturPenjualan')->name('jurnal-otomatis-retur-penjualan');
        Route::post('/retur_pembelian', 'ApiController@journalReturPembelian')->name('jurnal-otomatis-retur-pembelian');
        Route::post('/void_jurnal_otomatis', 'ApiController@voidJournalOtomatis')->name('jurnal-otomatis-void');
    });
});

