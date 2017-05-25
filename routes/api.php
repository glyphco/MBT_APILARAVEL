<?php

use Illuminate\Http\Request;

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

Route::middleware('jwt.auth')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::middleware('jwt.auth')->get('/userinfo', 'UserinfoController@userinfo');
// Route::middleware('jwt.auth')->get('/me', 'UserinfoController@userinfo');

Route::get('/', function () {
    return app()->version();
});

//ONLY Tokened Visitors beyond this point!

Route::group(['middleware' => ['jwt.auth', 'cors']], function () {

//debug and general
    Route::get('/userinfo', 'UserinfoController@userinfo');
    Route::get('/me', 'UserinfoController@userinfo');

    Route::get('/test', function () {
        var_dump(\Auth::user());
        $attributes = array_pluck(\Auth::user()->getAbilities()->toArray(), 'name');
        //$attributes = array_pluck(\Auth::user()->get, 'name');
        $attributes = \Auth::user()->getAbilities();
        var_dump($attributes);
    });

//Venues

    Route::get('/venue', 'VenueController@index');
    Route::get('/venue/{id}', 'VenueController@show');
    Route::group(['middleware' => 'can:create-venues'], function () {
        Route::post('/venue', 'VenueController@store');
    });
    Route::group(['middleware' => 'can:confirm-venues'], function () {
        Route::get('/venue/{id}/confirm', 'VenueController@confirm');
        Route::get('/venue/{id}/unconfirm', 'VenueController@unconfirm');
    });
    // Route::group(['middleware' => 'can:edit-venues'], function () {
    //     Route::put('/venue/{id}', 'VenueController@update');
    //     // checks made in controller:
    //     Route::get('/venue/{id}/giveedit/{userid}', 'VenueController@giveedit');
    //     Route::get('/venue/{id}/revokeedit/{userid}', 'VenueController@revokeedit');
    //     Route::get('/venue/{id}/giveadmin/{userid}', 'VenueController@giveadmin');
    //     Route::get('/venue/{id}/revokeadmin/{userid}', 'VenueController@revokeadmin');
    //     Route::get('/venue/{id}/editors', 'VenueController@geteditors');
    //     Route::get('/venue/{id}/admins', 'VenueController@getadmins');
    // });

// //Events
    //     Route::get('/event', 'EventController@index');
    //     Route::get('/event/{id}', 'EventController@show');

});
