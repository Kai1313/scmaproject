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

Route::get('/dashboard/{user_id?}', 'SessionController@index');
Route::get('/logout', 'SessionController@logout');

Route::get('/get-menu/{id}', 'DashboardController@getMenu')->name('get-menu');
Route::get('/akun', function () {
    return view('accounting.master.akun');
});
Route::get('/slip', function () {
    return view('accounting.master.slip');
});

Route::prefix('master-biaya')->group(function () {
    Route::get('/', 'MasterBiayaController@index')->name('master-biaya-page');
    Route::get('entry/{id?}', 'MasterBiayaController@entry')->name('master-biaya-entry');
    Route::post('save-entry/{id}', 'MasterBiayaController@saveEntry')->name('master-biaya-save-entry');
    Route::post('delete/{id}', 'MasterBiayaController@destroy')->name('master-biaya-delete');
});

Route::prefix('master-wrapper')->group(function () {
    Route::get('/', 'MasterWrapperController@index')->name('master-wrapper-page');
    Route::get('entry/{id?}', 'MasterWrapperController@entry')->name('master-wrapper-entry');
    Route::post('save-entry/{id}', 'MasterWrapperController@saveEntry')->name('master-wrapper-save-entry');
    Route::post('delete/{id}', 'MasterWrapperController@destroy')->name('master-wrapper-delete');
});

// Master
Route::get('/master/slip', 'MasterSlipController@index')->name('master-slip');
Route::get('/master/slip/form/create', 'MasterSlipController@create')->name('master-slip-create');
Route::get('/master/slip/form/edit/{id}', 'MasterSlipController@edit')->name('master-slip-edit');
Route::get('/master/slip/form/show/{id}', 'MasterSlipController@show')->name('master-slip-show');
Route::post('/master/slip/store', 'MasterSlipController@store')->name('master-slip-store');
Route::post('/master/slip/update', 'MasterSlipController@update')->name('master-slip-update');
Route::get('/master/slip/destroy/{id}', 'MasterSlipController@destroy')->name('master-slip-destroy');
Route::get('/master/coa', 'MasterCoaController@index')->name('master-coa');

Route::get('/master/coa/form/create', 'MasterCoaController@create')->name('master-coa-create');
Route::get('/master/coa/form/edit/{id}', 'MasterCoaController@edit')->name('master-coa-edit');
Route::get('/master/coa/form/show/{id}', 'MasterCoaController@show')->name('master-coa-show');
Route::post('/master/coa/store', 'MasterCoaController@store')->name('master-coa-store');
Route::post('/master/coa/update', 'MasterCoaController@update')->name('master-coa-update');
Route::get('/master/coa/destroy/{id}', 'MasterCoaController@destroy')->name('master-coa-destroy');
