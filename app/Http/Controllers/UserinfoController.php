<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

class UserinfoController extends BaseController
{

    //use HasRolesAndAbilities;

    const MODEL = 'App\Models\User';

    public function userinfo(Request $request)
    {
        $data               = $this->getUser();
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

}
