<?php

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

// NOTE! Since we're optimizing routes, we cant have any CLOSURES in the routes files.
// Returns are nonos

// Route::get('/', function () {
//     return app()->version();
// });

// Route::get('/giveglypheradmin', function () {
//     if (\Auth::user()->facebook_id == env('glyph_facebook', 0)) {
//         Bouncer::assign('superadmin')->to(\Auth::user());
//         return \Auth::user()->getAbilities()->toArray();
//     } else {
//         return 'nice try dickwad.';
//     }
// });

// Route::get('/test2', function () {
//     var_dump(\Auth::user());
//     $attributes = array_pluck(\Auth::user()->getAbilities()->toArray(), 'name');
//     //$attributes = array_pluck(\Auth::user()->get, 'name');
//     $attributes = \Auth::user()->getAbilities()->toarray();
//     var_dump($attributes);
// });

Route::get('/', 'pub\GeneralController@getVer');

Route::get('/tests/ver', 'pub\GeneralController@getVer');
Route::get('/tests/php', 'pub\GeneralController@getVer');

Route::get('/test', 'TestController@index');

Route::group(['middleware' => ['cors']], function () {

    Route::options('/{any}',
        [
            'as'   => 'anything',
            'uses' => 'VenueController@index',
        ])
        ->where(['any' => '.*']);

// Guest
    Route::get('/public/events/today', 'pub\EventController@today');
    //Route::get('/public/event/{id}', 'pub\EventController@getEvent');
    //Route::get('/slug/{slugsearch}', 'pub\SlugController@index');
});

//ONLY Tokened Visitors beyond this point!

Route::group(['middleware' => ['cors', 'jwt.auth']], function () {

// ME
    Route::get('/me', 'MeController@userinfo');
    Route::put('/me/setlocation', 'MeController@setLocation');
    Route::get('/me/pyfs', 'MeController@getPyf');
    Route::get('/me/followers', 'MeController@getFollowers');

    Route::get('/me/follow/{id}', 'FriendshipController@follow');

    Route::get('/me/accept/{id}', 'FriendshipController@accept');
    Route::get('/me/block/{id}', 'FriendshipController@block');

    Route::post('/me/attend/{id}', 'AttendingController@attendEvent');

    Route::get('/me/events', 'me\EventController@getEvents');
//FORTESTING REMOVE FOR FINAL PRODUCTION:
    Route::get('/me/makeme/{position}', 'MeController@makeMe');

//Venues

    Route::get('/venue', 'VenueController@index');
    Route::post('/venue', 'VenueController@store');
    Route::get('/venue/editable', 'VenueController@editable');
    Route::get('/venue/map', 'VenueController@getMap');
    Route::get('/venue/{id}', 'VenueController@show');
    Route::put('/venue/{id}', 'VenueController@update');
    Route::get('/venue/{id}/details', 'VenueController@details');
    Route::get('/venue/{id}/edit', 'VenueController@edit');
    Route::delete('/venue/{id}', 'VenueController@destroy');
    Route::get('/venue/{id}/like', 'VenueController@like');
    Route::get('/venue/{id}/likes', 'VenueController@getLikes');
    Route::get('/venue/{id}/confirm', 'VenueController@confirm');
    Route::get('/venue/{id}/unconfirm', 'VenueController@unconfirm');
    Route::get('/venue/{id}/makepublic', 'VenueController@makepublic');
    Route::get('/venue/{id}/makeprivate', 'VenueController@makeprivate');
    Route::get('/venue/{id}/giveedit/{userid}', 'VenueController@giveedit');
    Route::get('/venue/{id}/revokeedit/{userid}', 'VenueController@revokeedit');
    Route::get('/venue/{id}/giveadmin/{userid}', 'VenueController@giveadmin');
    Route::get('/venue/{id}/revokeadmin/{userid}', 'VenueController@revokeadmin');
    Route::get('/venue/{id}/editors', 'VenueController@geteditors');
    Route::get('/venue/{id}/admins', 'VenueController@getadmins');

//Pages

    Route::get('/page', 'PageController@index');
    Route::post('/page', 'PageController@store');
    Route::get('/page/editable', 'PageController@editable');
    Route::get('/page/{id}', 'PageController@show');
    Route::put('/page/{id}', 'PageController@update');
    Route::get('/page/{id}/details', 'PageController@details');
    Route::get('/page/{id}/edit', 'PageController@edit');
    Route::get('/page/{id}/like', 'PageController@like');
    Route::get('/page/{id}/likes', 'PageController@getLikes');
    Route::get('/page/{id}/confirm', 'PageController@confirm');
    Route::get('/page/{id}/unconfirm', 'PageController@unconfirm');
    Route::get('/page/{id}/makepublic', 'PageController@makepublic');
    Route::get('/page/{id}/makeprivate', 'PageController@makeprivate');
    Route::get('/page/{id}/giveedit/{userid}', 'PageController@giveedit');
    Route::get('/page/{id}/revokeedit/{userid}', 'PageController@revokeedit');
    Route::get('/page/{id}/giveadmin/{userid}', 'PageController@giveadmin');
    Route::get('/page/{id}/revokeadmin/{userid}', 'PageController@revokeadmin');
    Route::get('/page/{id}/editors', 'PageController@geteditors');
    Route::get('/page/{id}/admins', 'PageController@getadmins');

    Route::get('/page/{id}/events', 'PageController@getEvents');

// Shows

    Route::get('/show', 'ShowController@index');
    Route::post('/show', 'ShowController@store');
    Route::get('/show/editable', 'ShowController@editable');
    Route::get('/show/{id}', 'ShowController@show');
    Route::put('/show/{id}', 'ShowController@update');
    Route::get('/show/{id}/details', 'ShowController@details');
    Route::get('/show/{id}/edit', 'ShowController@edit');
    Route::get('/show/{id}/like', 'ShowController@like');
    Route::get('/show/{id}/likes', 'ShowController@getLikes');
    Route::get('/show/{id}/confirm', 'ShowController@confirm');
    Route::get('/show/{id}/unconfirm', 'ShowController@unconfirm');
    Route::get('/show/{id}/makepublic', 'ShowController@makepublic');
    Route::get('/show/{id}/makeprivate', 'ShowController@makeprivate');
    Route::get('/show/{id}/giveedit/{userid}', 'ShowController@giveedit');
    Route::get('/show/{id}/revokeedit/{userid}', 'ShowController@revokeedit');
    Route::get('/show/{id}/giveadmin/{userid}', 'ShowController@giveadmin');
    Route::get('/show/{id}/revokeadmin/{userid}', 'ShowController@revokeadmin');
    Route::get('/show/{id}/editors', 'ShowController@geteditors');
    Route::get('/show/{id}/admins', 'ShowController@getadmins');
//Page Categories
    Route::get('/page/{id}/categories', 'PageCategoryController@index');
    Route::post('/page/{id}/categories', 'PageCategoryController@store');
    Route::delete('/page/{id}/categories/{pagecategory_id}', 'PageCategoryController@destroy');

//Page Participants
    // Route::group(['prefix' => 'page/{page_id}', 'middleware' => 'can:edit-pages'], function () {
    //     Route::get('/participants', 'EventParticipantController@index');
    //     Route::get('/participants/{id}', 'EventParticipantController@show');
    // });

//mves
    Route::get('/mve', 'MveController@index');
    Route::post('/mve', 'MveController@store');
    Route::get('/mve/editable', 'MveController@editable');
    Route::get('/mve/{id}', 'MveController@show');
    Route::put('/mve/{id}', 'MveController@update');
    Route::get('/mve/{id}/edit', 'MveController@edit');
    Route::get('/mve/{id}/confirm', 'MveController@confirm');
    Route::get('/mve/{id}/unconfirm', 'MveController@unconfirm');
    Route::get('/mve/{id}/makepublic', 'MveController@makepublic');
    Route::get('/mve/{id}/makeprivate', 'MveController@makeprivate');
    Route::get('/mve/{id}/giveedit/{userid}', 'MveController@giveedit');
    Route::get('/mve/{id}/revokeedit/{userid}', 'MveController@revokeedit');
    Route::get('/mve/{id}/giveadmin/{userid}', 'MveController@giveadmin');
    Route::get('/mve/{id}/revokeadmin/{userid}', 'MveController@revokeadmin');
    Route::get('/mve/{id}/editors', 'MveController@geteditors');
    Route::get('/mve/{id}/admins', 'MveController@getadmins');

//events
    Route::get('/event', 'EventController@index');
    Route::post('/event', 'EventController@store');
    Route::get('/event/editable', 'EventController@editable');
    Route::get('/event/today', 'EventController@today');
    Route::get('/event/today/map', 'EventController@todayMap');
    Route::get('/event/current', 'EventController@current');
    Route::get('/event/{id}/', 'EventController@show');
    Route::put('/event/{id}/', 'EventController@update');
    Route::get('/event/{id}/edit', 'EventController@edit');
    Route::get('/event/{id}/details', 'EventController@details');
    //Events are attended, not liked.
    Route::get('/event/{id}/attend', 'AttendingController@attendEvent');
    Route::get('/event/{id}/confirm', 'EventController@confirm');
    Route::get('/event/{id}/unconfirm', 'EventController@unconfirm');
    Route::get('/event/{id}/makepublic', 'EventController@makepublic');
    Route::get('/event/{id}/makeprivate', 'EventController@makeprivate');
    Route::get('/event/{id}/giveedit/{userid}', 'EventController@giveedit');
    Route::get('/event/{id}/revokeedit/{userid}', 'EventController@revokeedit');
    Route::get('/event/{id}/giveadmin/{userid}', 'EventController@giveadmin');
    Route::get('/event/{id}/revokeadmin/{userid}', 'EventController@revokeadmin');
    Route::get('/event/{id}/editors', 'EventController@geteditors');
    Route::get('/event/{id}/admins', 'EventController@getadmins');
//Attending

//Event Shows
    // Route::get('/event/{id}/shows/', 'EventShowController@index');
    // Route::post('/event/{id}/shows/', 'EventShowController@store');
    // Route::delete('/event/{id}/shows/{eventshow_id}', 'EventShowController@destroy');
    //Event Categories
    // Route::get('/event/{id}/categories', 'EventCategoryController@index');
    // Route::post('/event/{id}/categories', 'EventCategoryController@store');
    // Route::delete('/event/{id}/categories/{eventcategory_id}', 'EventCategoryController@destroy');

//Event Participants
    // Route::get('/event/{id}/participants', 'EventParticipantController@index');
    // Route::post('/event/{id}/participants', 'EventParticipantController@store');
    // Route::get('/event/{id}/participants/{eventparticipant_id}', 'EventParticipantController@show');
    // Route::put('/event/{id}/participants/{eventparticipant_id}', 'EventParticipantController@update');
    // Route::delete('/event/{id}/participants/{eventparticipant_id}', 'EventParticipantController@destroy');

// Users (Mostly Admin stuff)

    Route::get('/user/editable/', 'UserController@editable');
    Route::get('/user/{id}/', 'UserController@details');

    Route::group(['middleware' => 'can:view-users'], function () {
        Route::get('/user', 'UserController@index');
        Route::get('/user/getsuperadmins', 'UserController@getSuperadmins');
        Route::get('/user/getadmins', 'UserController@getAdmins');
        Route::get('/user/getmastereditors', 'UserController@getMastereditors');
        Route::get('/user/getcontributors', 'UserController@getContributors');

        // Route::get('/user/friends/', 'UserController@getFollowing');

        //Route::get('/user/{id}', 'UserController@show');

    });

    Route::group(['middleware' => 'can:edit-users'], function () {
        // Route::put('/user/{id}', 'UserController@update');
        // Route::get('/user/{id}/ban', 'UserController@ban');
        Route::get('/user/{id}/makesuperadmin', 'UserController@makesuperadmin');
        Route::get('/user/{id}/makeadmin', 'UserController@makeadmin');
        Route::get('/user/{id}/makemastereditor', 'UserController@makemastereditor');
        Route::get('/user/{id}/makecontributor', 'UserController@makecontributor');
        Route::get('/user/{id}/makenothing', 'UserController@makenothing');
        Route::get('/user/{id}/friends', 'UserController@getFollowing');
    });

    Route::group(['middleware' => 'can:ban-users'], function () {
        // Route::get('/user/{id}/ban', 'UserController@ban');
        // Route::get('/user/{id}/unban', 'UserController@unban');
    });

    //Maintenance
    Route::group(['middleware' => 'can:confirm-pages'], function () {
        Route::get('/maintenance/unlinkedvenues', 'MaintenanceController@unlinkedvenues');
        Route::get('/maintenance/unlinkedparticipants', 'MaintenanceController@unlinkedparticipants');
    });

    Route::get('/maintenance/numbers', 'MaintenanceController@getNumbers');

//Categories
    Route::get('/category', 'CategoryController@index');

//Uploads
    //    Route::get('/signupload/{item}/{id}/{for}', 'SignUploadController@sign');
    //Route::post('/uploadheadimage/{item}/{id}/', 'UploadController@uploadheadimage');

//BOTS!!
    Route::get('/bots/bot1', 'bots\Bot1Controller@index');
    Route::get('/bots/bot2', 'bots\Bot2Controller@index');
    Route::get('/bots/bot3', 'bots\Bot3Controller@index');
});
