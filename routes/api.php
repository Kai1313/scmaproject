<?php

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
Route::post('store_fcm_token', 'ApiController@storeFcmToken');

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('profile', 'ApiController@profile');
    Route::post('logout', 'ApiController@logout');

    Route::post('transaction-balance', 'ApiController@transactionBalance')->name('transaction-balance');
    Route::post('minimal-stok', 'ApiController@stokmin')->name('minimal-stok');

    Route::prefix('/jurnal_otomatis')->group(function () {
        Route::post('/uangmuka_penjualan', 'ApiController@journalUangMukaPenjualan')->name('jurnal-otomatis-uangmuka-penjualan');
        Route::post('/uangmuka_pembelian', 'ApiController@journalUangMukaPembelian')->name('jurnal-otomatis-uangmuka-pembelian');
        Route::post('/penjualan', 'ApiController@journalPenjualan')->name('jurnal-otomatis-penjualan');
        Route::post('/penjualan_asset', 'ApiController@journalPenjualanAsset')->name('jurnal-otomatis-penjualan-asset');
        Route::post('/pembelian', 'ApiController@journalPembelian')->name('jurnal-otomatis-pembelian');
        Route::post('/retur_penjualan', 'ApiController@journalReturPenjualan')->name('jurnal-otomatis-retur-penjualan');
        Route::post('/retur_pembelian', 'ApiController@journalReturPembelian')->name('jurnal-otomatis-retur-pembelian');
        Route::post('/void_jurnal_otomatis', 'ApiController@voidJournalOtomatis')->name('jurnal-otomatis-void');
        Route::post('/jurnal_hpp', 'ApiController@journalHpp')->name('jurnal-otomatis-hpp');
        Route::post('/jurnal_closing_pemakaian', 'ApiController@jurnalClosingPemakaian')->name('jurnal-otomatis-closing-pemakaian');
        Route::post('/jurnal_closing_retur_jual', 'ApiController@jurnalClosingReturJual')->name('jurnal-otomatis-closing-retur-jual');
    });

    require __DIR__ . '/penyusutan/penyusutan.php';
});
