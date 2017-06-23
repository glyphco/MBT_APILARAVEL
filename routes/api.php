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

//ONLY Tokened Visitors beyond this point!

//Route::group(['middleware' => ['jwt.auth', 'cors']], function () {

Route::group(['middleware' => ['cors']], function () {

    Route::options('/{any}',
        [
            'as'   => 'anything',
            'uses' => 'VenueController@index',
        ])
        ->where(['any' => '.*']);

});

Route::group(['middleware' => ['cors', 'jwt.auth']], function () {

//debug and general
    Route::get('/userinfo', 'UserinfoController@userinfo');
    Route::get('/me', 'UserinfoController@userinfo');

    Route::get('/test', function () {
        var_dump(\Auth::user());
        $attributes = array_pluck(\Auth::user()->getAbilities()->toArray(), 'name');
        //$attributes = array_pluck(\Auth::user()->get, 'name');
        $attributes = \Auth::user()->getAbilities()->toarray();
        var_dump($attributes);
    });

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
    Route::group(array('prefix' => 'showpage'), function () {
        Route::get('/', 'ShowpageController@index');
        Route::get('/{id}', 'ShowpageController@show');
        Route::get('/{id}/likes', 'ShowpageController@getLikes');
        Route::get('/like/{id}', ['as' => 'showpage.like', 'uses' => 'LikeController@likeShowpage']);

        Route::group(['middleware' => 'can:create-pages'], function () {
            Route::post('/', 'ShowpageController@store');
        });
        Route::group(['middleware' => 'can:confirm-pages'], function () {
            Route::get('/{id}/confirm', 'ShowpageController@confirm');
            Route::get('/{id}/unconfirm', 'ShowpageController@unconfirm');
        });

        Route::group(['middleware' => 'can:edit-pages'], function () {
            Route::put('/{id}', 'ShowpageController@update');
            Route::get('/{id}/makepublic', 'ShowpageController@makepublic');
            Route::get('/{id}/makeprivate', 'ShowpageController@makeprivate');
            // checks made in controller:
            Route::get('/{id}/giveedit/{userid}', 'ShowpageController@giveedit');
            Route::get('/{id}/revokeedit/{userid}', 'ShowpageController@revokeedit');
            Route::get('/{id}/giveadmin/{userid}', 'ShowpageController@giveadmin');
            Route::get('/{id}/revokeadmin/{userid}', 'ShowpageController@revokeadmin');
            Route::get('/{id}/editors', 'ShowpageController@geteditors');
            Route::get('/{id}/admins', 'ShowpageController@getadmins');
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

//events

    Route::get('/event', 'eventController@index');
    Route::get('/event/{id}', 'eventController@show');
    Route::get('/event/like/{id}', ['as' => 'event.like', 'uses' => 'LikeController@likeEvent']);
    Route::group(['middleware' => 'can:create-events'], function () {
        Route::post('/event', 'eventController@store');
    });
    Route::group(['middleware' => 'can:confirm-events'], function () {
        Route::get('/event/{id}/confirm', 'eventController@confirm');
        Route::get('/event/{id}/unconfirm', 'eventController@unconfirm');
    });

    Route::group(['middleware' => 'can:edit-events'], function () {
        Route::put('/event/{id}', 'eventController@update');
        Route::get('/event/{id}/makepublic', 'eventController@makepublic');
        Route::get('/event/{id}/makeprivate', 'eventController@makeprivate');
        // checks made in controller:
        Route::get('/event/{id}/giveedit/{userid}', 'eventController@giveedit');
        Route::get('/event/{id}/revokeedit/{userid}', 'eventController@revokeedit');
        Route::get('/event/{id}/giveadmin/{userid}', 'eventController@giveadmin');
        Route::get('/event/{id}/revokeadmin/{userid}', 'eventController@revokeadmin');
        Route::get('/event/{id}/editors', 'eventController@geteditors');
        Route::get('/event/{id}/admins', 'eventController@getadmins');
    });

//eventvenuess

    Route::get('/eventvenue', 'EventvenueController@index');
    Route::get('/eventvenue/{id}', 'EventvenueController@show');
//Attending
    Route::get('/eventvenue/attend/{id}', ['as' => 'eventvenue.attending', 'uses' => 'AttendingController@attendEventVenue']);

    Route::group(['middleware' => 'can:edit-events'], function () {
        Route::post('/eventvenue', 'EventvenueController@store');
        Route::get('/eventvenue/{id}/confirm', 'EventvenueController@confirm');
        Route::get('/eventvenue/{id}/unconfirm', 'EventvenueController@unconfirm');
        Route::put('/eventvenue/{id}', 'EventvenueController@update');
        Route::get('/eventvenue/{id}/makepublic', 'EventvenueController@makepublic');
        Route::get('/eventvenue/{id}/makeprivate', 'EventvenueController@makeprivate');
    });

//EventVenue Categories
    Route::group(['prefix' => 'eventvenue/{page_id}'], function () {
        Route::get('/category', 'EventVenueCategoryController@index');
    });
    Route::group(['prefix' => 'eventvenue/{eventvenue_id}', 'middleware' => 'can:edit-pages'], function () {
        Route::post('/category', 'EventVenueCategoryController@store');
    });
    Route::group(['middleware' => 'can:edit-pages'], function () {
        Route::delete('/eventvenuecategory/{eventvenuecategory_id}', 'EventVenueCategoryController@destroy');
    });

//EventVenue Participants
    Route::group(['prefix' => 'eventvenue/{eventvenue_id}', 'middleware' => 'can:edit-events'], function () {
        Route::get('/participants', 'EventVenueParticipantController@index');
        Route::get('/participants/{id}', 'EventVenueParticipantController@show');
    });
    Route::group(['prefix' => 'eventvenue/{eventvenue_id}', 'middleware' => 'can:create-events'], function () {
        Route::post('/participants', 'EventVenueParticipantController@store');
    });
    Route::group(['prefix' => 'eventvenue/{eventvenue_id}', 'middleware' => 'can:edit-events'], function () {
        Route::put('/participants/{id}', 'EventVenueParticipantController@update');
    });
    Route::group(['prefix' => 'eventvenue/{eventvenue_id}', 'middleware' => 'can:edit-events'], function () {
        Route::delete('/participants/{id}', 'EventVenueParticipantController@destroy');
    });

//Participants
    Route::group(['middleware' => 'can:edit-events'], function () {
        Route::get('/participants', 'ParticipantController@index');
        Route::get('/participants/{id}', 'ParticipantController@show');
    });
    Route::group(['middleware' => 'can:create-events'], function () {
        Route::post('/participants', 'ParticipantController@store');
    });

// Users (Mostly Admin stuff)
    Route::group(['middleware' => 'can:view-users'], function () {
        Route::get('/user', 'UserController@index');
        Route::get('/user/getsuperadmins', 'UserController@getSuperadmins');
        Route::get('/user/getadmins', 'UserController@getAdmins');
        Route::get('/user/getmastereditors', 'UserController@getMastereditors');
        Route::get('/user/getcontributors', 'UserController@getContributors');

        Route::get('/user/friends/', 'UserController@getFriends');

        Route::get('/user/friend/{id}', 'FriendshipController@addFriend');
        Route::get('/user/unfriend/{id}', 'FriendshipController@unFriend');
        Route::get('/user/blockfriend/{id}', 'FriendshipController@blockFriend');

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
    Route::get('/pagecategory', 'PagecategoryController@index');

    // Route::get('/giveglypheradmin', function () {
    //     if (\Auth::user()->facebook_id == env('glyph_facebook', 0)) {
    //         Bouncer::assign('superadmin')->to(\Auth::user());
    //         return \Auth::user()->getAbilities()->toArray();
    //     } else {
    //         return 'nice try dickwad.';
    //     }
    // });

});
