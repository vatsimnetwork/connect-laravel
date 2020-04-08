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

//--------------------------------------------------------------------------
// Public Endpoint
//--------------------------------------------------------------------------
Route::get('/', 'HomeController@index')->name('home');

//--------------------------------------------------------------------------
// Login Endpoint
//--------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', 'Auth\LoginController@login')->name('login');
    // Route::get('/validate', 'LoginController@validateLogin');
});

//--------------------------------------------------------------------------
// Authenticated Endpoint
//--------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

    // Routes here are protected by ConnectAuth.
});
