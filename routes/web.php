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

// NOTE! Since we're optimizing routes, we cant have any CLOSURES in the routes files.
// Returns are nonos

// Route::get('/', function () {
//     return "Laravel version: " . app()->version();
// });

Route::get('/', 'pub\GeneralController@getVer');
