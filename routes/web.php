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

Route::get('/', 'SessionController@index')->name('welcome');
Route::get('/logout', 'SessionController@logout')->name('logout');

Route::get('/get-menu/{id}', 'DashboardController@getMenu')->name('get-menu');
// Route::get('/akun', function(){
//     return view('accounting.master.akun');
// });
// Route::get('/slip', function(){
//     return view('accounting.master.slip');
// });

Route::prefix('master_biaya')->group(function () {
    Route::get('/index/{user_id?}', 'MasterBiayaController@index')->name('master-biaya');
    Route::get('/entry/{id?}', 'MasterBiayaController@entry')->name('master-biaya-entry');
    Route::get('/view/{id}', 'MasterBiayaController@viewData')->name('master-biaya-view');
    Route::post('/save_entry/{id}', 'MasterBiayaController@saveEntry')->name('master-biaya-save-entry');
    Route::get('/delete/{id}', 'MasterBiayaController@destroy')->name('master-biaya-delete');
});

Route::prefix('master_wrapper')->group(function () {
    Route::get('/index/{user_id?}', 'MasterWrapperController@index')->name('master-wrapper');
    Route::get('/entry/{id?}', 'MasterWrapperController@entry')->name('master-wrapper-entry');
    Route::get('/view/{id}', 'MasterWrapperController@viewData')->name('master-wrapper-view');
    Route::post('/save_entry/{id}', 'MasterWrapperController@saveEntry')->name('master-wrapper-save-entry');
    Route::get('/delete/{id}', 'MasterWrapperController@destroy')->name('master-wrapper-delete');
});

Route::prefix('purchase_requisitions')->group(function () {
    Route::get('/index/{user_id?}', 'PurchaseRequestController@index')->name('purchase-request');
    Route::get('/entry/{id?}', 'PurchaseRequestController@entry')->name('purchase-request-entry');
    Route::get('/view/{id}', 'PurchaseRequestController@viewData')->name('purchase-request-view');
    Route::post('/save_entry/{id}', 'PurchaseRequestController@saveEntry')->name('purchase-request-save-entry');
    Route::get('/delete/{id}', 'PurchaseRequestController@destroy')->name('purchase-request-delete');
    Route::get('/auto_werehouse', 'PurchaseRequestController@autoWerehouse')->name('purchase-request-auto-werehouse');
    Route::get('/auto_item', 'PurchaseRequestController@autoItem')->name('purchase-request-auto-item');
    Route::get('/auto_satuan', 'PurchaseRequestController@autoSatuan')->name('purchase-request-auto-satuan');
    Route::get('/change_status/{id}/{type}', 'PurchaseRequestController@changeStatus')->name('purchase-request-change-status');
    Route::get('/print/{id}', 'PurchaseRequestController@printData')->name('purchase-request-print-data');
});

Route::prefix('uang_muka_pembelian')->group(function () {
    Route::get('/index/{user_id?}', 'PurchaseDownPaymentController@index')->name('purchase-down-payment');
    Route::get('/entry/{id?}', 'PurchaseDownPaymentController@entry')->name('purchase-down-payment-entry');
    Route::get('/view/{id}', 'PurchaseDownPaymentController@viewData')->name('purchase-down-payment-view');
    Route::post('/save_entry/{id}', 'PurchaseDownPaymentController@saveEntry')->name('purchase-down-payment-save-entry');
    Route::get('/delete/{id}', 'PurchaseDownPaymentController@destroy')->name('purchase-down-payment-delete');
    Route::get('/auto_po', 'PurchaseDownPaymentController@autoPo')->name('purchase-down-payment-auto-po');
    Route::get('/count_po', 'PurchaseDownPaymentController@countPo')->name('purchase-down-payment-count-po');
});

Route::prefix('qc_penerimaan_barang')->group(function () {
    Route::get('/index/{user_id?}', 'QcReceiptController@index')->name('qc_receipt');
    Route::get('/entry/{id?}', 'QcReceiptController@entry')->name('qc_receipt-entry');
    Route::post('/save_entry/{id}', 'QcReceiptController@saveEntry')->name('qc_receipt-save-entry');
    Route::get('/auto_purchasing', 'QcReceiptController@autoPurchasing')->name('qc_receipt-auto-purchasing');
    Route::get('/auto-item', 'QcReceiptController@autoItem')->name('qc_receipt-auto-item');
});

Route::prefix('send_to_branch')->group(function () {
    Route::get('/index/{user_id?}', 'SendToBranchController@index')->name('send_to_branch');
    Route::get('/entry/{id?}', 'SendToBranchController@entry')->name('send_to_branch-entry');
    Route::post('/save_entry/{id}', 'SendToBranchController@saveEntry')->name('send_to_branch-save-entry');
    Route::get('/view/{id}', 'SendToBranchController@view')->name('send_to_branch-view');
    Route::get('/delete/{id}', 'SendToBranchController@destroy')->name('send_to_branch-delete');
    Route::get('/auto-qrcode', 'SendToBranchController@autoQRCode')->name('send_to_branch-qrcode');
});

Route::prefix('received_from_branch')->group(function () {
    Route::get('/index/{user_id?}', 'ReceivedFromBranchController@index')->name('received_from_branch');
    Route::get('/entry/{id?}', 'ReceivedFromBranchController@entry')->name('received_from_branch-entry');
    Route::post('/save_entry/{id}', 'ReceivedFromBranchController@saveEntry')->name('received_from_branch-save-entry');
    Route::get('/view/{id}', 'ReceivedFromBranchController@view')->name('received_from_branch-view');
    Route::get('/delete/{id}', 'ReceivedFromBranchController@destroy')->name('received_from_branch-delete');
});

// Master
Route::get('/master/slip/index/{user_id?}', 'MasterSlipController@index')->name('master-slip');
Route::get('/master/slip/form/create', 'MasterSlipController@create')->name('master-slip-create');
Route::get('/master/slip/form/edit/{id?}', 'MasterSlipController@edit')->name('master-slip-edit');
Route::get('/master/slip/show/{id?}', 'MasterSlipController@show')->name('master-slip-show');
Route::post('/master/slip/store', 'MasterSlipController@store')->name('master-slip-store');
Route::post('/master/slip/update', 'MasterSlipController@update')->name('master-slip-update');
Route::get('/master/slip/destroy/{id?}', 'MasterSlipController@destroy')->name('master-slip-destroy');
Route::get('/master/slip/populate', 'MasterSlipController@populate')->name('master-slip-populate');
Route::get('/master/slip/export/excel', 'MasterSlipController@export_excel')->name('master-slip-export-excel');
Route::post('/master/slip/copy/data', 'MasterSlipController@copy_data')->name('master-slip-copy-data');
Route::get('/master/slip/get_by_cabang/{id_cabang?}/{id_slip?}', 'MasterSlipController@getSlipByCabang')->name('master-slip-get-by-cabang');

Route::get('/master/coa/index/{user_id?}', 'MasterCoaController@index')->name('master-coa');
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
Route::get('/master/coa/get_by_cabang/{id_cabang?}', 'MasterCoaController@getCoaByCabang')->name('master-coa-get-by-cabang');
Route::get('/master/coa/get_data/{id?}', 'MasterCoaController@getCoa')->name('master-coa-get-data');

Route::get('/master/setting/get_pelunasan/{id?}', 'MasterSettingController@getSettingPelunasan')->name('master-setting-get-pelunasan');

// Transaction
// Jurnal Umum
Route::get('/transaction/general_ledger/index/{user_id?}', 'GeneralLedgerController@index')->name('transaction-general-ledger');
Route::get('/transaction/general_ledger/form/create', 'GeneralLedgerController@create')->name('transaction-general-ledger-create');
Route::get('/transaction/general_ledger/form/edit/{id?}', 'GeneralLedgerController@edit')->name('transaction-general-ledger-edit');
Route::get('/transaction/general_ledger/show/{id?}', 'GeneralLedgerController@show')->name('transaction-general-ledger-show');
Route::post('/transaction/general_ledger/store', 'GeneralLedgerController@store')->name('transaction-general-ledger-store');
Route::post('/transaction/general_ledger/update', 'GeneralLedgerController@update')->name('transaction-general-ledger-update');
Route::get('/transaction/general_ledger/populate', 'GeneralLedgerController@populate')->name('transaction-general-ledger-populate');
Route::get('/transaction/general_ledger/populate-transaction', 'GeneralLedgerController@populateTrxSaldo')->name('transaction-general-ledger-populate-transaction');
Route::get('/transaction/general_ledger/print/{id?}', 'GeneralLedgerController@printSlip')->name('transaction-general-ledger-print');
Route::get('/transaction/general_ledger/void/{id?}', 'GeneralLedgerController@void')->name('transaction-general-ledger-void');
Route::get('/transaction/general_ledger/active/{id?}', 'GeneralLedgerController@active')->name('transaction-general-ledger-active');

// Jurnal Penyesuaian
Route::get('/transaction/adjustment_ledger/index/{user_id?}', 'AdjustmentLedgerController@index')->name('transaction-adjustment-ledger');
Route::get('/transaction/adjustment_ledger/form/create', 'AdjustmentLedgerController@create')->name('transaction-adjustment-ledger-create');
Route::get('/transaction/adjustment_ledger/form/edit/{id?}', 'AdjustmentLedgerController@edit')->name('transaction-adjustment-ledger-edit');
Route::get('/transaction/adjustment_ledger/show/{id?}', 'AdjustmentLedgerController@show')->name('transaction-adjustment-ledger-show');
Route::post('/transaction/adjustment_ledger/store', 'AdjustmentLedgerController@store')->name('transaction-adjustment-ledger-store');
Route::post('/transaction/adjustment_ledger/update', 'AdjustmentLedgerController@update')->name('transaction-adjustment-ledger-update');
Route::get('/transaction/adjustment_ledger/populate', 'AdjustmentLedgerController@populate')->name('transaction-adjustment-ledger-populate');
Route::get('/transaction/adjustment_ledger/print/{id?}', 'AdjustmentLedgerController@printSlip')->name('transaction-adjustment-ledger-print');
Route::get('/transaction/adjustment_ledger/void/{id?}', 'AdjustmentLedgerController@void')->name('transaction-adjustment-ledger-void');
Route::get('/transaction/adjustment_ledger/active/{id?}', 'AdjustmentLedgerController@active')->name('transaction-adjustment-ledger-active');
