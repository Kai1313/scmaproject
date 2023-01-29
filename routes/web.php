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

use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/{user_id?}', 'SessionController@index');
Route::get('/logout', 'SessionController@logout');

Route::get('/get-menu/{id}', 'DashboardController@getMenu')->name('get-menu');
Route::get('/akun', function(){
    return view('accounting.master.akun');
});
Route::get('/slip', function(){
    return view('accounting.master.slip');
});
