<?php

//--------------------------------------------------------------------------
// You will need the following routes for Connect authentication
//--------------------------------------------------------------------------
Route::get('/login', 'Auth\LoginController@login')->middleware('guest')->name('login');   // Login
Route::get('/logout', 'Auth\LoginController@logout')->middleware('auth')->name('logout'); // Logout
