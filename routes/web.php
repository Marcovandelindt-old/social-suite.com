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

Route::get(
    '/',
    function () {
        return view('welcome');
    }
);

Auth::routes();

Route::get('/home', 'HomeController@index')
    ->name('home');

/**
 * Twitter Routes
 */
Route::get('/twitter', 'TwitterController@index')
    ->name('twitter.index')->middleware('auth');

Route::get('/twitter/authenticate', 'TwitterController@getAuthenticate')
    ->name('twitter.authenticate')->middleware('auth');

Route::post('twitter/authenticate', 'TwitterController@postAuthenticate')
    ->name('twitter.authenticate.post')->middleware('auth');

Route::get('twitter/schedule', 'TwitterController@getSchedule')
    ->name('twitter.schedule')->middleware('auth');