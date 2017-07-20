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

//Route::options('/venue', 'VenueController@index'); // Preflight
Route::get('/test', 'TestController@index');
//ONLY Tokened Visitors beyond this point!

//Route::group(['middleware' => ['jwt.auth', 'cors']], function () {

Route::group(['middleware' => ['cors']], function () {

    Route::options('/{any}',
        [
            'as'   => 'anything',
            'uses' => 'VenueController@index',
        ])
        ->where(['any' => '.*']);

// Guest
    Route::get('/public/events', 'pub\EventController@getEvents');
    Route::get('/public/event/{id}', 'pub\EventController@getEvent');
});

Route::group(['middleware' => ['cors', 'jwt.auth']], function () {

//debug and general
    Route::get('/userinfo', 'UserinfoController@userinfo');

    Route::get('/test2', function () {
        var_dump(\Auth::user());
        $attributes = array_pluck(\Auth::user()->getAbilities()->toArray(), 'name');
        //$attributes = array_pluck(\Auth::user()->get, 'name');
        $attributes = \Auth::user()->getAbilities()->toarray();
        var_dump($attributes);
    });

// ME
    Route::get('/me', 'MeController@userinfo');
    Route::get('/me/friends', 'MeController@getFriendships');
    Route::put('/me/setlocation', 'MeController@setLocation');
    Route::get('/me/friendships', 'MeController@getFriendships');
    Route::get('/me/friend/{id}', 'FriendshipController@addFriend');
    Route::get('/me/unfriend/{id}', 'FriendshipController@unFriend');
    Route::get('/me/blockfriend/{id}', 'FriendshipController@blockFriend');
    Route::get('/me/attend/{id}', 'AttendingController@attendEvent');
    Route::get('/me/events', 'me\EventController@getEvents');
//Venues

    Route::get('/venue', 'VenueController@index');
    Route::get('/venue/{id}', 'VenueController@show');
    Route::get('/venue/like/{id}', ['as' => 'venue.like', 'uses' => 'LikeController@likeVenue']);
    Route::group(['middleware' => 'can:create-venues'], function () {
        Route::post('/venue', 'VenueController@store');
    });
    Route::group(['middleware' => 'can:confirm-venues'], function () {
        Route::get('/venue/{id}/confirm', 'VenueController@confirm');
        Route::get('/venue/{id}/unconfirm', 'VenueController@unconfirm');
    });
    Route::group(['middleware' => 'can:edit-venues'], function () {
        Route::put('/venue/{id}', 'VenueController@update');
        Route::get('/venue/{id}/makepublic', 'VenueController@makepublic');
        Route::get('/venue/{id}/makeprivate', 'VenueController@makeprivate');
        // checks made in controller:
        Route::get('/venue/{id}/giveedit/{userid}', 'VenueController@giveedit');
        Route::get('/venue/{id}/revokeedit/{userid}', 'VenueController@revokeedit');
        Route::get('/venue/{id}/giveadmin/{userid}', 'VenueController@giveadmin');
        Route::get('/venue/{id}/revokeadmin/{userid}', 'VenueController@revokeadmin');
        Route::get('/venue/{id}/editors', 'VenueController@geteditors');
        Route::get('/venue/{id}/admins', 'VenueController@getadmins');
    });

//Pages

    Route::get('/page', 'PageController@index');
    Route::get('/page/{id}', 'PageController@show');
    Route::get('/page/{id}/likes', 'PageController@getLikes');
    Route::get('/page/{id}/events', 'PageController@getEvents');
    Route::get('/page/like/{id}', ['as' => 'page.like', 'uses' => 'LikeController@likePage']);

    Route::group(['middleware' => 'can:create-pages'], function () {
        Route::post('/page', 'PageController@store');
    });
    Route::group(['middleware' => 'can:confirm-pages'], function () {
        Route::get('/page/{id}/confirm', 'PageController@confirm');
        Route::get('/page/{id}/unconfirm', 'PageController@unconfirm');
    });

    Route::group(['middleware' => 'can:edit-pages'], function () {
        Route::put('/page/{id}', 'PageController@update');
        Route::get('/page/{id}/makepublic', 'PageController@makepublic');
        Route::get('/page/{id}/makeprivate', 'PageController@makeprivate');
        // checks made in controller:
        Route::get('/page/{id}/giveedit/{userid}', 'PageController@giveedit');
        Route::get('/page/{id}/revokeedit/{userid}', 'PageController@revokeedit');
        Route::get('/page/{id}/giveadmin/{userid}', 'PageController@giveadmin');
        Route::get('/page/{id}/revokeadmin/{userid}', 'PageController@revokeadmin');
        Route::get('/page/{id}/editors', 'PageController@geteditors');
        Route::get('/page/{id}/admins', 'PageController@getadmins');
    });

// Show Pages
    Route::group(array('prefix' => 'show'), function () {
        Route::get('/', 'ShowController@index');
        Route::get('/{id}', 'ShowController@show');
        Route::get('/{id}/likes', 'ShowController@getLikes');
        Route::get('/like/{id}', ['as' => 'show.like', 'uses' => 'LikeController@likeShow']);

        Route::group(['middleware' => 'can:create-pages'], function () {
            Route::post('/', 'ShowController@store');
        });
        Route::group(['middleware' => 'can:confirm-pages'], function () {
            Route::get('/{id}/confirm', 'ShowController@confirm');
            Route::get('/{id}/unconfirm', 'ShowController@unconfirm');
        });

        Route::group(['middleware' => 'can:edit-pages'], function () {
            Route::put('/{id}', 'ShowController@update');
            Route::get('/{id}/makepublic', 'ShowController@makepublic');
            Route::get('/{id}/makeprivate', 'ShowController@makeprivate');
            // checks made in controller:
            Route::get('/{id}/giveedit/{userid}', 'ShowController@giveedit');
            Route::get('/{id}/revokeedit/{userid}', 'ShowController@revokeedit');
            Route::get('/{id}/giveadmin/{userid}', 'ShowController@giveadmin');
            Route::get('/{id}/revokeadmin/{userid}', 'ShowController@revokeadmin');
            Route::get('/{id}/editors', 'ShowController@geteditors');
            Route::get('/{id}/admins', 'ShowController@getadmins');
        });
    });

//Page Participants
    // Route::group(['prefix' => 'page/{page_id}', 'middleware' => 'can:edit-pages'], function () {
    //     Route::get('/participants', 'EventParticipantController@index');
    //     Route::get('/participants/{id}', 'EventParticipantController@show');
    // });

//Page Categories
    Route::group(['prefix' => 'page/{page_id}'], function () {
        Route::get('/category', 'PageCategoryController@index');
    });
    Route::group(['prefix' => 'page/{page_id}', 'middleware' => 'can:edit-pages'], function () {
        Route::post('/category', 'PageCategoryController@store');
    });
    Route::group(['middleware' => 'can:edit-pages'], function () {
        Route::delete('/pagecategory/{pagecategory_id}', 'PageCategoryController@destroy');
    });

//mves

    Route::get('/mve', 'MveController@index');
    Route::get('/mve/{id}', 'MveController@show');
    Route::group(['middleware' => 'can:create-events'], function () {
        Route::post('/mve', 'MveController@store');
    });
    Route::group(['middleware' => 'can:confirm-events'], function () {
        Route::get('/mve/{id}/confirm', 'MveController@confirm');
        Route::get('/mve/{id}/unconfirm', 'MveController@unconfirm');
    });

    Route::group(['middleware' => 'can:edit-events'], function () {
        Route::put('/mve/{id}', 'MveController@update');
        Route::get('/mve/{id}/makepublic', 'MveController@makepublic');
        Route::get('/mve/{id}/makeprivate', 'MveController@makeprivate');
        // checks made in controller:
        Route::get('/mve/{id}/giveedit/{userid}', 'MveController@giveedit');
        Route::get('/mve/{id}/revokeedit/{userid}', 'MveController@revokeedit');
        Route::get('/mve/{id}/giveadmin/{userid}', 'MveController@giveadmin');
        Route::get('/mve/{id}/revokeadmin/{userid}', 'MveController@revokeadmin');
        Route::get('/mve/{id}/editors', 'MveController@geteditors');
        Route::get('/mve/{id}/admins', 'MveController@getadmins');
    });

//events
    Route::get('/event', 'EventController@index');
    Route::post('/event', 'EventController@store');

//
    Route::get('/event/editable', 'EventController@editable');

//Specific Event
    Route::group(['prefix' => 'event/{event_id}'], function () {
        Route::get('/', 'EventController@show');

//Access to Edits will be checked when the event is retrieved in controller
        Route::get('/edit', 'EventController@edit');
        Route::put('/', 'EventController@update');

        Route::get('/confirm', 'EventController@confirm');
        Route::get('/unconfirm', 'EventController@unconfirm');
        Route::get('/makepublic', 'EventController@makepublic');
        Route::get('/makeprivate', 'EventController@makeprivate');
        Route::get('/giveedit/{userid}', 'EventController@giveedit');
        Route::get('/revokeedit/{userid}', 'EventController@revokeedit');
        Route::get('/giveadmin/{userid}', 'EventController@giveadmin');
        Route::get('/revokeadmin/{userid}', 'EventController@revokeadmin');
        Route::get('/editors', 'EventController@geteditors');
        Route::get('/admins', 'EventController@getadmins');

//Attending
        Route::get('/attend', 'AttendingController@attendEvent');

//Event Shows
        Route::group(['prefix' => 'shows'], function () {
            Route::get('/', 'EventShowController@index');
            Route::post('/', 'EventShowController@store');
            Route::delete('/{eventshow_id}', 'EventShowController@destroy');
        });

//Event Categories
        Route::get('/categories', 'EventCategoryController@index');
        Route::post('/categories', 'EventCategoryController@store');
        Route::delete('/categories/{eventcategory_id}', 'EventCategoryController@destroy');

//Event Participants
        Route::get('/participants', 'EventParticipantController@index');
        Route::get('/participants/{id}', 'EventParticipantController@show');
        Route::post('/participants', 'EventParticipantController@store');
        Route::put('/participants/{id}', 'EventParticipantController@update');
        Route::delete('/participants/{id}', 'EventParticipantController@destroy');
    });

// Users (Mostly Admin stuff)
    Route::group(['middleware' => 'can:view-users'], function () {
        Route::get('/user', 'UserController@index');
        Route::get('/user/getsuperadmins', 'UserController@getSuperadmins');
        Route::get('/user/getadmins', 'UserController@getAdmins');
        Route::get('/user/getmastereditors', 'UserController@getMastereditors');
        Route::get('/user/getcontributors', 'UserController@getContributors');

        // Route::get('/user/friends/', 'UserController@getFriendships');

        Route::get('/user/{id}', 'UserController@show');

    });

    Route::group(['middleware' => 'can:edit-users'], function () {
        // Route::put('/user/{id}', 'UserController@update');
        // Route::get('/user/{id}/ban', 'UserController@ban');
        Route::get('/user/{id}/makesuperadmin', 'UserController@makesuperadmin');
        Route::get('/user/{id}/makeadmin', 'UserController@makeadmin');
        Route::get('/user/{id}/makemastereditor', 'UserController@makemastereditor');
        Route::get('/user/{id}/makecontributor', 'UserController@makecontributor');
        Route::get('/user/{id}/makenothing', 'UserController@makenothing');
        Route::get('/user/{id}/friends', 'UserController@getFriendships');
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

//PageCategories
    Route::get('/category', 'CategoryController@index');

    // Route::get('/giveglypheradmin', function () {
    //     if (\Auth::user()->facebook_id == env('glyph_facebook', 0)) {
    //         Bouncer::assign('superadmin')->to(\Auth::user());
    //         return \Auth::user()->getAbilities()->toArray();
    //     } else {
    //         return 'nice try dickwad.';
    //     }
    // });

//Uploads
    Route::get('/signupload/{item}/{id}/{for}', 'SignUploadController@sign');
    //Route::post('/uploadheadimage/{item}/{id}/', 'UploadController@uploadheadimage');

//BOTS!!
    Route::get('/bots/bot1', 'bots\Bot1Controller@index');
    Route::get('/bots/bot2', 'bots\Bot2Controller@index');
    Route::get('/bots/bot3', 'bots\Bot3Controller@index');
});
