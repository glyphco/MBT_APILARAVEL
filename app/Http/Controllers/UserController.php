<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Bouncer;
use Illuminate\Http\Request;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class UserController extends BaseController
{

    use HasRolesAndAbilities;

    const MODEL                = 'App\Models\User';
    protected $validationRules = ['email' => 'required', 'name' => 'required', 'password' => 'required'];

//These are public calls

    public function details(Request $request, $id)
    {

//Check relationship to user:
        $userseesyou = \App\Models\Following::where('user_id', $id)->where('following_id', \Auth::user()->id)->first();

        $youseesuser = \App\Models\Following::where('user_id', $id)->where('following_id', \Auth::user()->id)->first();

        dump($userseesyou);
        dump($youseesuser);

        //autorelates venue and participants in model
        $m = self::MODEL;
        if ($data = $m::with([
            'mve',
            'venue',
            'categories',
            'eventshows',
            'eventparticipants',
            'eventproducer',

        ])
            ->withCount([
                'attendingyes',
                'attendingmaybe',
                'attendingwish',
                'pyfsattendingyes',
                'pyfsattendingmaybe',
                'pyfsattendingwish',
            ])
            ->distance($lat, $lng)
            ->with([
                'eventparticipants',
                'attendingyes',
                'attendingmaybe',
                'attendingwish',
                'pyfsattendingyes',
                'pyfsattendingmaybe',
                'pyfsattendingwish',
            ])
            ->where('confirmed', '=', 1)
            ->where('public', '=', 1)
            ->distance($lat, $lng, 'METERS')
            ->find($id)) {
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

    public function show($id)
    {
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

    // public function update(Request $request, $id) {
    //     $m = self::MODEL;

    //     if (!$data = $m::find($id)) {
    //         return $this->notFoundResponse();
    //     }

    //     try
    //     {
    //         $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->validationRules);

    //         if ($v->fails()) {
    //             throw new \Exception("ValidationException");
    //         }
    //         $data->fill($request->all());
    //         $data->save();
    //         return $this->showResponse($data);
    //     } catch (\Exception $ex) {
    //         $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
    //         return $this->clientErrorResponse($data);
    //     }
    // }

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

}
