<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Bouncer;
use Illuminate\Http\Request;

class MeController extends BaseController
{

    //use HasRolesAndAbilities;

    const MODEL               = 'App\Models\Me';
    protected $accessarray    = [];
    protected $attributearray = [];

    public function userinfo(Request $request)
    {
        $data       = $this->getUser()->toArray();
        $attributes = $this->getAttributes();
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
        //$data['access'] = [];
        //$data['access'] = $fake;
        //$data['token']      = app('request')->header('Authorization');
        return $this->showResponse($data);
    }

    public function edit()
    {
        $id = \Auth::user()->id;
        //autorelates venue and participants in model
        $m = self::MODEL;
        if (!$data = $m::find($id)) {
            return $this->notFoundResponse();
        }

        return $this->showResponse($data);
    }

    public function update(Request $request)
    {
        $id = \Auth::user()->id;
        $m  = self::MODEL;

        if (!$data = $m::find($id)) {
            return $this->notFoundResponse();
        }

        if (!Bouncer::allows($this->edititems)) {
            return $this->unauthorizedResponse();
        }

        try
        {
            $input = $request->all();
            $data->fill($input);
            $data->save();
            return $this->showResponse($data);
        } catch (\Exception $ex) {
            $data = ['exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

    }

    private function getUser()
    {
        $m    = self::MODEL;
        $data = $m::find(\Auth::user()->id);
        //with(['following'])
        return $data;
    }

    private function getAttributes()
    {

        //return \Auth::user()->getAbilities()->toArray();
        $attributes = \Auth::user()->getAbilities()->toArray();
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

//FOLLOWING

//followers
    public function getPyfs()
    {
        if ($data = \App\Models\Me::with('pyfs')
            ->find(\Auth::user()->id)
            ->pyfs) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();
    }

    public function getFollowers()
    {
        if ($data = \App\Models\Me::with('requested')
            ->find(\Auth::user()->id)
            ->requested) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();
    }

    public function getFollowersRequests()
    {
        if ($data = \App\Models\Me::with('requested')
            ->find(\Auth::user()->id)
            ->requested) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();
    }

    public function getFollowersBlocked()
    {
        if ($data = \App\Models\Me::with('blocked')
            ->find(\Auth::user()->id)
            ->blocked) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();
    }

    public function setLocation(Request $request)
    {
        $m               = self::MODEL;
        $validationRules = [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ];

        if (!$data = $m::find(\Auth::id())) {
            return $this->notFoundResponse();
        }

        try
        {
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), $validationRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }

            $request->request->add(['location' => implode(', ', $request->only('lng', 'lat'))]);

            $data->fill($request->only('lat', 'lng', 'location', 'locationname', 'sublocationname'));
            $data->save();

            return $this->createdResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }

    public function makeMe($position)
    {

        $validpositions = [
            'superadmin',
            'admin',
            'mastereditor',
            'contributor',
            'nothing',
        ];

        if (!(in_array($position, $validpositions))) {
            return $this->notFoundResponse();
        }
        $user = \Auth::User();
        Bouncer::retract('superadmin')->from($user);
        Bouncer::retract('admin')->from($user);
        Bouncer::retract('mastereditor')->from($user);
        Bouncer::retract('contributor')->from($user);

        if ($position == 'nothing') {
            Bouncer::refreshFor($user);
            return $this->showResponse('user is now ' . $position);
        }

        if ($position == 'superadmin') {
            if (\Auth::user()->facebook_id == \Config::get('services.superadmins.glyph_facebook')) {
                Bouncer::assign('superadmin')->to($user);
                Bouncer::refreshFor($user);
                return $this->showResponse('user is now ' . $position);
            } else {
                return $this->reasonedUnauthorizedResponse('nice try dickwad');
            }
        }

        Bouncer::assign($position)->to($user);
        Bouncer::refreshFor($user);
        return $this->showResponse('user is now ' . $position);
    }

    public function modelAccess($attributes)
    {
        foreach ($variable as $key => $value) {

            switch ($attribute['name']) {
                case 'view-users':
                    # code...
                    break;
                case 'edit-users':
                    # code...
                    break;

                case 'view-users':
                    # code...
                    break;

                case 'edit-users':
                    # code...
                    break;

                case 'ban-users':
                    # code...
                    break;

                case 'admin-pages':
                    # code...
                    break;

                case 'confirm-pages':
                    # code...
                    break;

                case 'create-pages':
                    # code...
                    break;

                case 'edit-pages':
                    # code...
                    break;

                case 'delete-pages':
                    # code...
                    break;

                case 'admin-venues':
                    # code...
                    break;

                case 'confirm-venues':
                    # code...
                    break;

                case 'create-venues':
                    # code...
                    break;

                case 'edit-venues':
                    # code...
                    break;

                case 'delete-venues':
                    # code...
                    break;

                case 'admin-events':
                    # code...
                    break;

                case 'confirm-events':
                    # code...
                    break;

                case 'create-events':
                    # code...
                    break;

                case 'edit-events':
                    # code...
                    break;

                case 'delete-events':
                    # code...
                    break;

                default:
                    # code...
                    break;
            }
        }
    }
}
