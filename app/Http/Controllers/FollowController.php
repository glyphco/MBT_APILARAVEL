<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

class FollowController extends BaseController
{

    protected $valid_statuses = [
        0 => 'blocked',
        1 => 'nothing',
        2 => 'requested',
        3 => 'accepted',
    ];

    protected $valid_follows = [
        1 => 'request cancelled',
        2 => 'requested',
    ];

    protected $valid_responds = [
        0 => 'blocked',
        1 => 'nothing',
        3 => 'accepted',
    ];

    public function follow(Request $request, $user_id)
    {
        if ($user_id == \Auth::user()->id) {
            return $this->clientErrorResponse('cant follow yourself... dummy');
        }

//FOR LATER Check if user has been banned or otherwise invalid
        if (!$user = \App\Models\User::find($user_id)) {
            return $this->notFoundResponse();
        }

        if (!$request->has('status') || !array_key_exists($request['status'], $this->valid_follows)) {
            return $this->clientErrorResponse('[status] not found');
        }

        if (!array_key_exists($request['status'], $this->valid_follows)) {
            $this->clientErrorResponse('Cant do that');
        }

        $status = $request['status'];

        //from 0 to 1: CANT DO THAT
        //from 0 to 2: CANT DO THAT
        //from 0 to 3: CANT DO THAT

        //from 1 to 0: CANT DO THAT
        //from 1 to 2: Request Follow
        //from 1 to 3: CANT DO THAT

        //from 2 to 0: CANT DO THAT
        //from 2 to 1: Cancel Request
        //from 2 to 2: rerequest (also rechecks autoaccept)
        //from 2 to 3: CANT DO THAT

        //from 3 to 0: CANT DO THAT
        //from 3 to 1: unfollow
        //from 3 to 2: CANT DO THAT

        if (!$existingfollow = \App\Models\Following::where('user_id', \Auth::user()->id)
            ->where('following_id', $user_id)
            ->first()) {
            //No Follow Exists: allow setting to 1 or 2
            \App\Models\Following::create(
                ['user_id' => \Auth::user()->id, 'following_id' => $user_id, 'status' => $status]
            );
            return $this->clientErrorResponse($this->valid_follows[$status]);
        }

        $currentstatus = $existingrequest->status;

//Currently nothing
        if ($currentstatus == 1) {
            if ($status == 2) {
                //request user
                if ($user->autoacceptfollows == 1) {
                    $status = 3; //Accepted
                }

                $existingrequest->status = $status;
                $existingrequest->save();
                return $this->showResponse('requested');
            }
            return $this->clientErrorResponse('Cant do that');
        }

//Currently Requested
        if ($status == 2) {
            if ($status == 1) {
                //unrequest
                $existingrequest->status = $status;
                $existingrequest->save();
                return $this->showResponse('request cancelled');
            }
            if ($status == 2) {
                //request user
                if ($user->autoacceptfollows == 1) {
                    $status = 3; //Accepted
                }

                $existingrequest->status = $status;
                $existingrequest->save();
                return $this->showResponse('requested');
            }
            return $this->clientErrorResponse('Cant do that');
        }

//Currently Following
        if ($status == 3) {
            if ($status == 1) {
                //unrequest
                $existingrequest->status = $status;
                $existingrequest->save();
                return $this->showResponse('unfollowed');
            }
            return $this->clientErrorResponse('Cant do that');
        }

        return $this->clientErrorResponse('Cant do that');
    }

    public function respond(Request $request, $user_id)
    {
        if ($user_id == \Auth::user()->id) {
            return $this->clientErrorResponse('cant follow yourself... dummy');
        }

        if (!$request->has('status') || !array_key_exists($request['status'], $this->valid_statuses)) {
            return $this->clientErrorResponse('[status] not found');
        }

        $status = $request['status'];

        //from 0 to 1: undo block
        //from 0 to 2: CANT DO THAT
        //from 0 to 3: CANT DO THAT

        //ALSO for non Existance
        //from 1 to 0: block user
        //from 1 to 2: CANT DO THAT
        //from 1 to 3: CANT DO THAT

        //from 2 to 0: block request
        //from 2 to 1: ignore request
        //from 2 to 3: accept request

        //from 3 to 0: block follower
        //from 3 to 1: dismiss follower
        //from 3 to 2: CANT DO THAT

//TRYING TO MAKE A REQUEST from other person
        if ($status == 2) {
            return $this->clientErrorResponse('Cant do that');
        }

        if (!$existingrequest = \App\Models\Following::where('following_id', \Auth::user()->id)
            ->where('user_id', $user_id)
            ->first()) {
            if ($status == 0) {
                //block user
                \App\Models\Following::create(
                    ['user_id' => $user_id, 'following_id' => \Auth::user()->id, 'status' => 0]
                );
                return $this->clientErrorResponse('user blocked');
            }
            //trying to create a follow for the other person...
            return $this->clientErrorResponse('Cant do that');
        }

        $currentstatus = $existingrequest->status;

//Currently blocked
        if ($currentstatus == 0) {
            if ($status == 1) {
                //undo the block
                $existingrequest->status = $status;
                $existingrequest->save();
                return $this->showResponse('unblocked');
            }
            return $this->clientErrorResponse('Cant do that');
        }

//Currently nothing
        if ($currentstatus == 1) {
            if ($status == 0) {
                //block user
                $existingrequest->status = $status;
                $existingrequest->save();
                return $this->showResponse('user blocked');
            }
            return $this->clientErrorResponse('Cant do that');
        }

//Currently requesting
        if ($currentstatus == 2) {
            if ($status == 0) {
                //block request
                $existingrequest->status = $status;
                $existingrequest->save();
                return $this->showResponse('request blocked');
            }
            if ($status == 1) {
                //ignore request
                $existingrequest->status = $status;
                $existingrequest->save();
                return $this->showResponse('request ignored');
            }
            if ($status == 3) {
                //accept request
                $existingrequest->status = $status;
                $existingrequest->save();
                return $this->showResponse('request accepted');
            }
            return $this->clientErrorResponse('Cant do that');
        }

//Currently following
        if ($currentstatus == 3) {
            if ($status == 0) {
                //block user
                $existingrequest->status = $status;
                $existingrequest->save();
                return $this->showResponse('follower blocked');
            }
            if ($status == 1) {
                //block user
                $existingrequest->status = $status;
                $existingrequest->save();
                return $this->showResponse('follower dismissed');
            }
            return $this->clientErrorResponse('Cant do that');
        }
        return $this->clientErrorResponse('Cant do that');
    }

}
