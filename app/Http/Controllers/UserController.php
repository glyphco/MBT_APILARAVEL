<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Traits\UserBanableTrait;
use App\Traits\UserConfirmableTrait;
use Bouncer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class UserController extends BaseController
{

    use UserConfirmableTrait, UserBanableTrait, HasRolesAndAbilities;

    const MODEL                = 'App\Models\User';
    protected $validationRules = ['email' => 'required', 'name' => 'required', 'password' => 'required'];
    protected $viewitems       = 'view-users';
    protected $edititems       = 'edit-users';
    protected $confirmitems    = 'edit-users';
    protected $banitems        = 'ban-users';

//These are public calls

    public function details(Request $request, $id)
    {

        $privacy = $this->getPrivacySettings($id);

        if ($privacy['userseesyou'] == 0) {
            return $this->clientErrorResponse('blocked');
        }

        $m = self::MODEL;

        $data = $m::withCount('followers');

        if ($privacy['events'] == 1) {
            $data = $data->with('eventsImAttending');
        }
        if ($privacy['likes'] == 1) {
            $data = $data
                ->with([
                    'likedVenues',
                    'likedPages',
                    'likedShows',
                ])
                ->withCount([
                    'likedVenues',
                    'likedPages',
                    'likedShows',
                ]);
        }
        if ($privacy['pyf'] == 1) {
            $data = $data->with('pyf')->withCount('pyf');
            $data = $data->with('followers');
        }

        if ($data = $data->find($id)) {
            $data['userseesyou'] = $privacy['userseesyou'];
            $data['youseeuser']  = $privacy['youseeuser'];

            $data->makeHidden([
                'facebook_id',
                'google_id',
                'email',
                'is_online',
                'is_banned',
                'banned_until',
                'updated_at',
                'deleted_at',
                'autoconfirmfollows',
            ]);

            return $this->showResponse($data);
        }
        return $this->notFoundResponse();

    }

//These are the rest

    public function index(Request $request)
    {
        //$request = Request::all();
        $m = self::MODEL;
        return $this->listResponse($m::all());

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $m = self::MODEL;

        if (!$data = $m::withoutGlobalScope(\App\Scopes\UserConfirmedScope::class)
            ->withoutGlobalScope(\App\Scopes\UserNotBannedScope::class)
            ->find($id)) {
            return $this->notFoundResponse();
        }

        if (!Bouncer::allows($this->edititems)) {
            return $this->unauthorizedResponse();
        }

        try
        {
            $input = $request->all();
            if ((array_key_exists('is_banned', $input)) && ($input['is_banned'] == 1)) {
                if (
                    (array_key_exists('banned_until', $input)) && (is_null($input['banned_until']))) {
                    $input['banned_until'] = Carbon::now()->addDays(10)->toDateString();
                }
            } else {
                $input['banned_until'] = null;
            }
            $data->fill($input);
            $data->save();
            return $this->showResponse($data);
        } catch (\Exception $ex) {
            $data = ['exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

    }

    public function show($id)
    {
        if (!((Bouncer::allows($this->viewitems)) or (Bouncer::allows($this->edititems)))) {
            return $this->unauthorizedResponse();
        }
        $m = self::MODEL;
        if (!$data = $m::find($id)) {
            return $this->notFoundResponse();

        }

        $attributes = $this->getAttributes($id);
        //dd($attributes);
        //$data['attributes'] = $this->attributearray;
        $fake = [

            "view-users"       => 1,
            "edit-users"       => 1,
            "ban-users"        => 1,
            "admin-pages"      => 1,
            "confirm-pages"    => 1,
            "create-pages"     => 1,
            //  "edit-pages"       => 1,
            "delete-pages"     => 1,
            "admin-venues"     => 1,
            "confirm-venues"   => 1,
            "create-venues"    => 1,
            "edit-venues"      => 1,
            "delete-venues"    => 1,
            "admin-events"     => 1,
            "confirm-events"   => 1,
            //"create-events"    => 1,
            "edit-events"      => 1,
            "delete-events"    => 1,
            "administer-event" => 1,
            "edit-event"       => 1,

        ];
        $data['access'] = $this->accessarray;
        return $this->showResponse($data);

    }

    public function edit($id)
    {
        //autorelates venue and participants in model
        $m = self::MODEL;
        if (!$data = $m::withoutGlobalScope(\App\Scopes\UserConfirmedScope::class)
            ->withoutGlobalScope(\App\Scopes\UserNotBannedScope::class)
            ->find($id)) {
            return $this->notFoundResponse();
        }

        if (!(
            (Bouncer::allows($this->edititems))
        )) {
            return $this->unauthorizedResponse();
        }

        //dd(Bouncer::role()->all()->toarray());

        $role = '';
        if ($roles = $data->roles->toarray()) {
            if (!empty($roles)) {
                $role = $roles[0]["name"];
            }
        }
        $data->offsetSet('role', $role);
        return $this->showResponse($data);
    }

    public function editable(Request $request)
    {
        if (!((Bouncer::allows($this->viewitems)) or (Bouncer::allows($this->edititems)))) {
            return $this->unauthorizedResponse();
        }
        $m    = self::MODEL;
        $data = $m::withoutGlobalScope(\App\Scopes\UserConfirmedScope::class)
            ->withoutGlobalScope(\App\Scopes\UserNotBannedScope::class);

        if ($request->exists('confirmed')) {
            $data = $data->where('confirmed', 1);
        }

        if ($request->exists('unconfirmed')) {
            $data = $data->where('confirmed', 0);
        }

        if ($request->exists('banned')) {
            $data = $data->where('banned', 1);
        }

        if ($request->exists('q')) {
            $data = $data->where('name', 'like', '%' . $request['q'] . '%');
        }

        if ($request->has('rank')) {
            switch ($request->input('rank')) {
                case 'superadmin':
                    $data = $data
                        ->whereIs('superadmin');
                    break;

                case 'admin':
                    $data = $data
                        ->whereIs('admin');
                    break;

                case 'mastereditor':
                    $data = $data
                        ->whereIs('mastereditor');
                    break;

                case 'contributor':
                    $data = $data
                        ->whereIs('contributor');
                    break;

                default:

                    break;
            }
        }

        if ($request->has('sortby')) {
            switch ($request->input('sortby')) {
                case 'name':
                    $data = $data
                        ->orderBy('name', 'ASC');
                    break;

                case 'email':
                    $data = $data
                        ->orderBy('email', 'asc')
                        ->orderBy('name', 'ASC');
                    break;

                case 'created_at':
                    $data = $data
                        ->orderBy('created_at', 'asc');
                    break;

                case 'is_banned':
                    $data = $data
                        ->orderBy('is_banned', 'desc')
                        ->orderBy('name', 'ASC');
                    break;

                default:
                    $data = $data
                        ->orderBy('name', 'ASC');
                    break;
            }
        } else {
            $data = $data->orderBy('name', 'ASC');
        }

        $pp = $request->input('pp', 25);
        if ($pp > 100) {$pp = 100;}
        $data = $data->paginate($pp);

        return $this->listResponse($data);
    }

    public function makesuperadmin($id)
    {
        $m = self::MODEL;
        if (!$user = $m::find($id)) {
            return $this->notFoundResponse();
        }

        if (\Auth::user()->facebook_id == \Config::get('services.superadmins.glyph_facebook')) {
            Bouncer::retract('admin')->from($user);
            Bouncer::retract('mastereditor')->from($user);
            Bouncer::retract('contributor')->from($user);
            Bouncer::assign('superadmin')->to($user);
        } else {
            return $this->reasonedUnauthorizedResponse('nice try dickwad');
        }
        Bouncer::refreshFor($user);
        return $this->showResponse('user is now superadmin');

    }

    public function makeadmin($id)
    {
        $m = self::MODEL;
        if (!$user = $m::find($id)) {
            return $this->notFoundResponse();
        }
        if (Bouncer::is($user)->a('superadmin')) {
            if (\Auth::user()->facebook_id == \Config::get('services.superadmins.glyph_facebook')) {
                Bouncer::retract('superadmin')->from($user);
            } else {
                return $this->reasonedUnauthorizedResponse('you cant unthrone a king');
            }
        }
        Bouncer::retract('mastereditor')->from($user);
        Bouncer::retract('contributor')->from($user);
        Bouncer::assign('admin')->to($user);
        Bouncer::refreshFor($user);
        return $this->showResponse('user is now admin');
    }

    public function makemastereditor($id)
    {
        $m = self::MODEL;
        if (!$user = $m::find($id)) {
            return $this->notFoundResponse();
        }
        if (Bouncer::is($user)->a('superadmin')) {
            if (\Auth::user()->facebook_id == \Config::get('services.superadmins.glyph_facebook')) {
                Bouncer::retract('superadmin')->from($user);
            } else {
                return $this->reasonedUnauthorizedResponse('you cant unthrone a king');
            }
        }

        Bouncer::retract('admin')->from($user);
        Bouncer::retract('contributor')->from($user);
        Bouncer::assign('mastereditor')->to($user);
        Bouncer::refreshFor($user);
        return $this->showResponse('user is now mastereditor');
    }

    public function makecontributor($id)
    {
        $m = self::MODEL;
        if (!$user = $m::find($id)) {
            return $this->notFoundResponse();
        }
        if (Bouncer::is($user)->a('superadmin')) {
            if (\Auth::user()->facebook_id == \Config::get('services.superadmins.glyph_facebook')) {
                Bouncer::retract('superadmin')->from($user);
            } else {
                return $this->reasonedUnauthorizedResponse('you cant unthrone a king');
            }
        }

        Bouncer::retract('admin')->from($user);
        Bouncer::retract('mastereditor')->from($user);
        Bouncer::assign('contributor')->to($user);
        Bouncer::refreshFor($user);
        return $this->showResponse('user is now contributor');
    }

    public function makenothing($id)
    {
        $m = self::MODEL;
        if (!$user = $m::find($id)) {
            return $this->notFoundResponse();
        }
        if (Bouncer::is($user)->a('superadmin')) {
            if (\Auth::user()->facebook_id == \Config::get('services.superadmins.glyph_facebook')) {
                Bouncer::retract('superadmin')->from($user);
            } else {
                return $this->reasonedUnauthorizedResponse('you cant unthrone a king');
            }
        }
        Bouncer::retract('admin')->from($user);
        Bouncer::retract('mastereditor')->from($user);
        Bouncer::retract('contributor')->from($user);
        Bouncer::refreshFor($user);
        return $this->showResponse('user is now nothing');
    }

    public function getSuperadmins(Request $request)
    {
        $m     = self::MODEL;
        $users = $m::whereIs('superadmin')->get();
        return $this->listResponse($users);
    }

    public function getAdmins(Request $request)
    {
        $m     = self::MODEL;
        $users = $m::whereIs('admin')->get();
        return $this->listResponse($users);
    }

    public function getMastereditors(Request $request)
    {
        $m     = self::MODEL;
        $users = $m::whereIs('mastereditor')->get();
        return $this->listResponse($users);
    }

    public function getContributors(Request $request)
    {
        $m     = self::MODEL;
        $users = $m::whereIs('contributor')->get();
        return $this->listResponse($users);
    }

    public function getFollowing($id)
    {

        $m = self::MODEL;

        if ($data = $m::with(['following'])->find($id)->following) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();
    }

    private function getAttributes($id)
    {
        $m = self::MODEL;

        //return \Auth::user()->getAbilities()->toArray();
        $attributes = $m::find($id)->getAbilities()->toArray();
        if (empty($attributes)) {
            return [[], []];
        }

        $models = [
            'App\Models\Venue' => 'venue',
            'App\Models\Page'  => 'page',
            'App\Models\Show'  => 'show',
            'App\Models\Event' => 'event',
            'App\Models\Mve'   => 'mve',
        ];
        $modelattributes   = ['edit', 'administer'];
        $returnattributes  = [];
        $this->accessarray = [];
        foreach ($attributes as $key => $attribute) {
            if (in_array($attribute['name'], $modelattributes)) {
                $this->attributearray[$models[$attribute['entity_type']]][$attribute['id']][$attribute['name']] = 1;
                //$returnattributes[$attribute['name'] . '-' . $models[$attribute['entity_type']]] = 1;

                $this->accessarray[$attribute['name'] . '-' . $models[$attribute['entity_type']]] = 1;
            } else {
                $this->attributearray[$attribute['name']] = 1;
                $this->accessarray[$attribute['name']]    = 1;

            }
        }
        return [$returnattributes];
        //return array_pluck(\Auth::user()->getAbilities()->toArray(), ['name', 'entity_type', 'entity_id'], 'id');
    }

    private function getPrivacySettings($id)
    {
        if ($id == \Auth::user()->id) {
            return [
                'events'      => 1,
                'likes'       => 1,
                'pyf'         => 1,
                'userseesyou' => 9,
                'youseeuser'  => 9,
            ];
        }

        $userseesyou = 1;
        $youseeuser  = 1;
//Check relationship to user:
        if ($checkuserseesyou = \App\Models\Following::where('user_id', \Auth::user()->id)->where('following_id', $id)->first()) {
            $userseesyou = $checkuserseesyou->status;

        }
        if ($checkyouseeuser = \App\Models\Following::where('user_id', $id)->where('following_id', \Auth::user()->id)->first()) {
            $youseeuser = $checkyouseeuser->status;
        }

//Logic:
        $showevents = 0;
        $showlikes  = 0;
        $showpyf    = 0;

        $showevents = $this->checkPrivacy(\Auth::user()->privacyevents, $userseesyou);
        $showlikes  = $this->checkPrivacy(\Auth::user()->privacylikes, $userseesyou);
        $showpyf    = $this->checkPrivacy(\Auth::user()->privacypyf, $userseesyou);

        $privacy = [
            'eventslogic' => \Auth::user()->privacyevents . '+' . $userseesyou . '=' . $showevents,
            'events'      => $showevents,
            'likeslogic'  => \Auth::user()->privacylikes . '+' . $userseesyou . '=' . $showlikes,
            'likes'       => $showlikes,
            'pyflogic'    => \Auth::user()->privacypyf . '+' . $userseesyou . '=' . $showpyf,
            'pyf'         => $showpyf,
            'userseesyou' => $userseesyou,
            'youseeuser'  => $youseeuser,

        ];
        //dump($privacy);

        return $privacy;
    }

    private function checkPrivacy($privacy, $status)
    {
        if ($privacy == 1) {
            //followers only
            if ($status == 3) {return 1;}
        }
        if ($privacy == 2) {
            //public
            if (!($status == 0)) {return 1;}
        }
        return 0;
    }
}
