<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Bouncer;
use Illuminate\Http\Request;

class MeController extends BaseController
{

    //use HasRolesAndAbilities;

    const MODEL = 'App\Models\Me';

    public function userinfo(Request $request)
    {
        $data               = $this->getUser()->toArray();
        $data['attributes'] = $this->getAttributes();
        $data['token']      = app('request')->header('Authorization');
        return $this->showResponse($data);
    }

    private function getUser()
    {
        $m    = self::MODEL;
        $data = $m::with(['friendships'])->find(\Auth::user()->id);

        return $data;
    }

    private function getAttributes()
    {

        //return \Auth::user()->getAbilities()->toArray();
        $attributes = \Auth::user()->getAbilities()->toArray();
        if (empty($attributes)) {
            return [];
        }

        $models = [
            'App\Models\Venue' => 'venue',
            'App\Models\Page'  => 'page',
        ];
        $modelattributes  = ['edit', 'administer'];
        $returnattributes = [];
        foreach ($attributes as $key => $attribute) {
            if (in_array($attribute['name'], $modelattributes)) {

                $returnattributes[$models[$attribute['entity_type']]][$attribute['id']][$attribute['name']] = 1;
            } else {
                $returnattributes[$attribute['name']] = 1;
            }
        }
        return $returnattributes;
        //return array_pluck(\Auth::user()->getAbilities()->toArray(), ['name', 'entity_type', 'entity_id'], 'id');
    }

    public function getFriendships()
    {

        $m = self::MODEL;

        if ($data = $m::with(['friendships'])->find(\Auth::id())->friendships) {
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

            $request->request->add(['location' => implode(', ', $request->only('lat', 'lng'))]);

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

}
