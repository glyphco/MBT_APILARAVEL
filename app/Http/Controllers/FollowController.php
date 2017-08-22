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

        $userseesyou = 1;
        if ($existingUserSeesYou = \App\Models\Following::where('following_id', \Auth::user()->id)
            ->where('user_id', $user_id)
            ->first()) {
            $userseesyou = $existingUserSeesYou->status;
        }

        $status         = $request['status'];
        $youseeuserword = '';
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

        if (!$existingYouSeeUser = \App\Models\Following::where('user_id', \Auth::user()->id)
            ->where('following_id', $user_id)
            ->first()) {
            if ($status = 2) {
                $youseeuserword = 'requested';

            }
            \App\Models\Following::create(
                ['user_id' => \Auth::user()->id, 'following_id' => $user_id, 'status' => $status]
            );
            return $this->clientErrorResponse(['youseeuser' => $status, 'youseeuserword' => $youseeuserword, 'userseesyou' => $userseesyou]);
        }

        $youseeuser = $existingYouSeeUser->status;

        $youseeuserword = '';

//Currently nothing
        if ($youseeuser == 1) {
            if ($status == 2) {
                $youseeuserword = 'requested';
                if ($user->autoacceptfollows == 1) {
                    $status         = 3;
                    $youseeuserword = 'accepted';
                }

                $existingYouSeeUser->status = $status;
                $existingYouSeeUser->save();
                return $this->showResponse(['youseeuser' => $status, 'youseeuserword' => $youseeuserword, 'userseesyou' => $userseesyou]);
            }
            return $this->clientErrorResponse('Cant do that');
        }

//Currently Requested
        if ($youseeuser == 2) {
            if ($status == 1) {
                $youseeuserword             = 'request canclled';
                $existingYouSeeUser->status = $status;
                $existingYouSeeUser->save();
                return $this->showResponse(['youseeuser' => $status, 'youseeuserword' => $youseeuserword, 'userseesyou' => $userseesyou]);
            }
            if ($status == 2) {
                $youseeuserword = 'requested'; //(still requested)
                if ($user->autoacceptfollows == 1) {
                    $status         = 3;
                    $youseeuserword = 'accepted';
                }

                $existingYouSeeUser->status = $status;
                $existingYouSeeUser->save();
                return $this->showResponse(['youseeuser' => $status, 'youseeuserword' => $youseeuserword, 'userseesyou' => $userseesyou]);
            }
            return $this->clientErrorResponse('Cant do that');
        }

//Currently Following
        if ($youseeuser == 3) {
            if ($status == 1) {
                $youseeuserword             = 'unrequested';
                $existingYouSeeUser->status = $status;
                $existingYouSeeUser->save();
                return $this->showResponse(['youseeuser' => $status, 'youseeuserword' => $youseeuserword, 'userseesyou' => $userseesyou]);
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

        $status          = $request['status'];
        $userseesyouword = '';

        $youseeuser = 1;
        if ($existingYouSeeUser = \App\Models\Following::where('user_id', \Auth::user()->id)
            ->where('following_id', $user_id)
            ->first()) {
            $youseeuser = $existingYouSeeUser->status;
        }

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

        if (!$existingUserSeesYou = \App\Models\Following::where('following_id', \Auth::user()->id)
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

        $userseesyou = $existingUserSeesYou->status;

//Currently blocked
        if ($userseesyou == 0) {
            if ($status == 1) {
                $userseesyouword             = 'unblocked';
                $existingUserSeesYou->status = $status;
                $existingUserSeesYou->save();
                return $this->showResponse(['userseesyou' => $status, 'userseesyouword' => $userseesyouword, 'youseeuser' => $youseeuser]);
            }
            return $this->clientErrorResponse('Cant do that');
        }

//Currently nothing
        if ($userseesyou == 1) {
            if ($status == 0) {
                $userseesyouword             = 'user blocked';
                $existingUserSeesYou->status = $status;
                $existingUserSeesYou->save();
                return $this->showResponse(['userseesyou' => $status, 'userseesyouword' => $userseesyouword, 'youseeuser' => $youseeuser]);
            }
            return $this->clientErrorResponse('Cant do that');
        }

//Currently requesting
        if ($userseesyou == 2) {
            if ($status == 0) {
                $userseesyouword             = 'request blocked';
                $existingUserSeesYou->status = $status;
                $existingUserSeesYou->save();
                return $this->showResponse(['userseesyou' => $status, 'userseesyouword' => $userseesyouword, 'youseeuser' => $youseeuser]);
            }
            if ($status == 1) {
                $userseesyouword             = 'request ignored';
                $existingUserSeesYou->status = $status;
                $existingUserSeesYou->save();
                return $this->showResponse(['userseesyou' => $status, 'userseesyouword' => $userseesyouword, 'youseeuser' => $youseeuser]);
            }
            if ($status == 3) {
                $userseesyouword             = 'request accepted';
                $existingUserSeesYou->status = $status;
                $existingUserSeesYou->save();
                return $this->showResponse(['userseesyou' => $status, 'userseesyouword' => $userseesyouword, 'youseeuser' => $youseeuser]);
            }
            return $this->clientErrorResponse('Cant do that');
        }

//Currently following
        if ($userseesyou == 3) {
            if ($status == 0) {
                $userseesyouword             = 'follower blocked';
                $existingUserSeesYou->status = $status;
                $existingUserSeesYou->save();
                return $this->showResponse(['userseesyou' => $status, 'userseesyouword' => $userseesyouword, 'youseeuser' => $youseeuser]);
            }
            if ($status == 1) {
                $userseesyouword             = 'follower dismissed';
                $existingUserSeesYou->status = $status;
                $existingUserSeesYou->save();
                return $this->showResponse(['userseesyou' => $status, 'userseesyouword' => $userseesyouword, 'youseeuser' => $youseeuser]);
            }
            return $this->clientErrorResponse('Cant do that');
        }
        return $this->clientErrorResponse('Cant do that');
    }

}
