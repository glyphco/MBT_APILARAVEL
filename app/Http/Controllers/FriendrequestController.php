<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

class FriendshipController extends BaseController
{
    protected $valid_ranks = [
        0 => 'not attending',
        1 => 'maybe attending',
        2 => 'wish i could',
        3 => 'attending',
    ];

    public function addfriend(Request $request, $id)
    {

// Search for existing requests from other person
        if ($friendrequest = \App\Models\Friends::where('user_id', $id)->where('friend_id', \Auth::id())->first()) {
            //take note of others response
            if ($friendrequest['user_accepted'] == 4) {
                return $this->showResponse('Blocked from user');
            };
            $status = ($friendrequest['friend_accepted'] == 1) ? 'Friends' : 'Requested';

            //If exists, accept request
            $friendrequest->friend_accepted = 1;
            $friendrequest->save();
            return $this->showResponse('Accepted');
        }

// Search for existing requests from this person
        if ($friendrequest = \App\Models\Friends::where('user_id', \Auth::id())->where('friend_id', $id)->first()) {
            //take note of others response
            if ($friendrequest['friend_accepted'] == 4) {
                return $this->showResponse('Blocked from user');
            };
            $status = ($friendrequest['friend_accepted'] == 1) ? 'Friends' : 'Requested';
            //If exists, change the user accept to yes (incase previously unfriended)
            $friendrequest->user_accepted = 1;
            $friendrequest->save();
            return $this->showResponse($status);
        }

//Add request to DB

        \App\Models\Friends::Create(
            [
                'user_id'       => \Auth::id(),
                'friend_id'     => $id,
                'user_accepted' => 1,
            ]
        );

        return $this->showResponse('Requested');

    }

    public function unfriend(Request $request, $id)
    {

// Search for existing requests from other person
        if ($friendrequest = \App\Models\Friends::where('user_id', $id)->where('friend_id', \Auth::id())->first()) {
            //take note of others response
            if ($friendrequest['user_accepted'] == 4) {
                return $this->showResponse('Blocked from user');
            };
            //If exists, unfriend
            $friendrequest->friend_accepted = 0;
            $friendrequest->save();
            return $this->showResponse('Unfriended');
        }

// Search for existing requests from this person
        if ($friendrequest = \App\Models\Friends::where('user_id', \Auth::id())->where('friend_id', $id)->first()) {
            //take note of others response
            if ($friendrequest['friend_accepted'] == 4) {
                return $this->showResponse('Blocked from user');
            };
            $status = ($friendrequest['friend_accepted'] == 1) ? 'Unfriended' : 'Request Removed';
            //If exists, unfriend

            $friendrequest->user_accepted = 0;
            $friendrequest->save();
            return $this->showResponse($status);
        }

        return $this->notFoundResponse();
    }

    public function blockfriend(Request $request, $id)
    {

// Search for existing requests from other person
        if ($friendrequest = \App\Models\Friends::where('user_id', $id)->where('friend_id', \Auth::id())->first()) {
            //If exists, unfriend
            $friendrequest->friend_accepted = 4;
            $friendrequest->save();
            return $this->showResponse('Unfriended');
        }

// Search for existing requests from this person
        if ($friendrequest = \App\Models\Friends::where('user_id', \Auth::id())->where('friend_id', $id)->first()) {
            //If exists, unfriend
            $friendrequest->user_accepted = 4;
            $friendrequest->save();
            return $this->showResponse('User blocked');
        }

        return $this->notFoundResponse();
    }

}
