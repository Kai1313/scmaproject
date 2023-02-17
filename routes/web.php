<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');
Route::get('/dashboard/{user_id?}', 'SessionController@index')->name('dashboard');
Route::get('/logout', 'SessionController@logout')->name('logout');

Route::get('/get-menu/{id}', 'DashboardController@getMenu')->name('get-menu');
// Route::get('/akun', function(){
//     return view('accounting.master.akun');
// });
// Route::get('/slip', function(){
//     return view('accounting.master.slip');
// });

Route::prefix('master-ops/biaya')->group(function () {
    Route::get('/', 'MasterBiayaController@index')->name('master-biaya');
    Route::get('entry/{id?}', 'MasterBiayaController@entry')->name('master-biaya-entry');
    Route::post('save-entry/{id}', 'MasterBiayaController@saveEntry')->name('master-biaya-save-entry');
    Route::post('delete/{id}', 'MasterBiayaController@destroy')->name('master-biaya-delete');
});

Route::prefix('master-ops/wrapper')->group(function () {
    Route::get('/', 'MasterWrapperController@index')->name('master-wrapper');
    Route::get('entry/{id?}', 'MasterWrapperController@entry')->name('master-wrapper-entry');
    Route::post('save-entry/{id}', 'MasterWrapperController@saveEntry')->name('master-wrapper-save-entry');
    Route::post('delete/{id}', 'MasterWrapperController@destroy')->name('master-wrapper-delete');
});

Route::prefix('permintaan-pembelian')->group(function () {
    Route::get('/', 'PurchaseRequestController@index')->name('purchase-request');
    Route::get('entry/{id?}', 'PurchaseRequestController@entry')->name('purchase-request-entry');
    Route::post('save-entry/{id}', 'PurchaseRequestController@saveEntry')->name('purchase-request-save-entry');
    Route::post('delete/{id}', 'PurchaseRequestController@destroy')->name('purchase-request-delete');
    Route::get('auto-werehouse', 'PurchaseRequestController@autoWerehouse')->name('purchase-request-auto-werehouse');
    Route::get('auto-user', 'PurchaseRequestController@autoUser')->name('purchase-request-auto-user');
    Route::get('auto-item', 'PurchaseRequestController@autoItem')->name('purchase-request-auto-item');
    Route::get('auto-satuan', 'PurchaseRequestController@autoSatuan')->name('purchase-request-auto-satuan');
});

Route::prefix('uang-muka-pembelian')->group(function () {
    Route::get('/', 'PurchaseDownPaymentController@index')->name('purchase-down-payment');
    Route::get('entry/{id?}', 'PurchaseDownPaymentController@entry')->name('purchase-down-payment-entry');
    Route::post('save-entry/{id}', 'PurchaseDownPaymentController@saveEntry')->name('purchase-down-payment-save-entry');
    Route::post('delete/{id}', 'PurchaseDownPaymentController@destroy')->name('purchase-down-payment-delete');
    Route::get('auto-po', 'PurchaseDownPaymentController@autoPo')->name('purchase-down-payment-auto-po');
    Route::get('auto-currency', 'PurchaseDownPaymentController@autoCurrency')->name('purchase-down-payment-auto-currency');
    Route::get('count-po', 'PurchaseDownPaymentController@countPo')->name('purchase-down-payment-count-po');
});

// Master
Route::get('/master/slip', 'MasterSlipController@index')->name('master-slip');
Route::get('/master/slip/form/create', 'MasterSlipController@create')->name('master-slip-create');
Route::get('/master/slip/form/edit/{id?}', 'MasterSlipController@edit')->name('master-slip-edit');
Route::get('/master/slip/show/{id?}', 'MasterSlipController@show')->name('master-slip-show');
Route::post('/master/slip/store', 'MasterSlipController@store')->name('master-slip-store');
Route::post('/master/slip/update', 'MasterSlipController@update')->name('master-slip-update');
Route::get('/master/slip/destroy/{id?}', 'MasterSlipController@destroy')->name('master-slip-destroy');
Route::get('/master/slip/populate', 'MasterSlipController@populate')->name('master-slip-populate');
Route::get('/master/slip/export/excel', 'MasterSlipController@export_excel')->name('master-slip-export-excel');
Route::post('/master/slip/copy/data', 'MasterSlipController@copy_data')->name('master-slip-copy-data');

Route::get('/master/coa', 'MasterCoaController@index')->name('master-coa');
Route::get('/master/coa/populate/{cabang?}', 'MasterCoaController@populate')->name('master-coa-populate');
Route::get('/master/coa/form/create', 'MasterCoaController@create')->name('master-coa-create');
Route::get('/master/coa/form/edit/{id}', 'MasterCoaController@edit')->name('master-coa-edit');
Route::get('/master/coa/form/show/{id}', 'MasterCoaController@show')->name('master-coa-show');
Route::post('/master/coa/store', 'MasterCoaController@store')->name('master-coa-store');
Route::post('/master/coa/update/{id}', 'MasterCoaController@update')->name('master-coa-update');
Route::get('/master/coa/destroy/{id}', 'MasterCoaController@destroy')->name('master-coa-destroy');
Route::get('/master/coa/get_header1', 'MasterCoaController@get_header1')->name('master-coa-header1');
Route::get('/master/coa/get_header2', 'MasterCoaController@get_header2')->name('master-coa-header2');
Route::get('/master/coa/get_header3', 'MasterCoaController@get_header3')->name('master-coa-header3');
Route::get('/master/coa/export/excel', 'MasterCoaController@export_excel')->name('master-coa-export-excel');
Route::post('/master/coa/copy/data', 'MasterCoaController@copy_data')->name('master-coa-copy-data');
