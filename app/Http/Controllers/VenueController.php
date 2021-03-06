<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Traits\ItemConfirmableTrait;
use App\Traits\ItemHasAdminsTrait;
use App\Traits\ItemHasEditorsTrait;
use App\Traits\ItemLikeableTrait;
use App\Traits\ItemPrivateableTrait;
use Bouncer;
use Illuminate\Http\Request;

class VenueController extends BaseController
{

    use ItemConfirmableTrait, ItemPrivateableTrait, ItemHasAdminsTrait, ItemHasEditorsTrait, ItemLikeableTrait;

    const MODEL                = 'App\Models\Venue';
    protected $validationRules = [
        'name'           => 'required',
        'street_address' => 'required',
        'city'           => 'required',
        'state'          => 'required',
        'postalcode'     => 'required',
        'lat'            => 'required',
        'lng'            => 'required',
        'local_tz'       => 'required',
    ];

    protected $createitems  = 'create-venues';
    protected $adminitems   = 'admin-venues';
    protected $edititems    = 'edit-venues';
    protected $confirmitems = 'confirm-venues';

    public function index(Request $request)
    {
        //$request = Request::all();
        $m = self::MODEL;
        //$data = $m;

        $data = $m::withCount([
            'currentevents',
            'events',
            'likes',
        ])
            ->with([
                'ilike',
            ]);

        if ($request->exists('q')) {
            $data = $data->where('name', 'like', '%' . $request['q'] . '%');
        }

        if ($request->exists('Unconfirmed')) {
            $data = $data->Unconfirmed();
        }
        if ($request->exists('ConfirmedAndUnconfirmed')) {
            $data = $data->ConfirmedAndUnconfirmed();
        }
        if ($request->exists('Private')) {
            $data = $data->Private();
        }
        if ($request->exists('PublicAndPrivate')) {
            $data = $data->PublicAndPrivate();
        }

        $pp = $request->input('pp', 25);
        if ($pp > 100) {$pp = 100;}
        $data = $data->paginate($pp);

        return $this->listResponse($data);

    }

    public function editable(Request $request)
    {

        $m    = self::MODEL;
        $data = $m::withCount([
            'currentevents',
            'events',
            'likes',
        ]);

        $data = $data->withoutGlobalScope(\App\Scopes\VenuePublicScope::class)->withoutGlobalScope(\App\Scopes\VenueConfirmedScope::class);

        if ($request->exists('confirmed')) {
            $data = $data->where('confirmed', '=', 1);
        }

        if ($request->exists('unconfirmed')) {
            $data = $data->where('confirmed', '=', 0);
        }

        if ($request->exists('public')) {
            $data = $data->where('public', '=', 1);
        }

        if ($request->exists('private')) {
            $data = $data->where('public', '=', 0);
        }

        if ($request->has('sortby')) {
            switch ($request->input('sortby')) {
                case 'name':
                    $data = $data
                        ->orderBy('name', 'ASC');
                    break;

                case 'likes':
                    $data = $data
                        ->orderBy('likes_count', 'desc')
                        ->orderBy('name', 'ASC');
                    break;

                case 'eventscount':
                    $data = $data
                        ->orderBy('events_count', 'desc')
                        ->orderBy('name', 'ASC');
                    break;

                case 'city':
                    $data = $data
                        ->orderBy('state', 'ASC')
                        ->orderBy('city', 'ASC')
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

//If you can edit or admin all, get all!
        if ((Bouncer::allows($this->edititems)) or (Bouncer::allows($this->adminitems))) {
            $data = $data->paginate($pp);
            return $this->listResponse($data);
        }
//Otherwise only the ones you can get to:
        $data = $data->wherein('id', \Auth::User()->abilities->where('entity_type', self::MODEL)->pluck('entity_id'))->paginate($pp);

        return $this->listResponse($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!(Bouncer::allows($this->createitems))) {
            return $this->unauthorizedResponse();
        }

        if (!(Bouncer::allows($this->confirmitems))) {
            $request['confirm'] = null;
        }

        $m = self::MODEL;

        try
        {
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->validationRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }
            $request->request->add(['location' => implode(', ', $request->only('lng', 'lat'))]);
            $data = $m::create($request->all());

            Bouncer::allow(\Auth::user())->to('administer', $data);
            Bouncer::allow(\Auth::user())->to('edit', $data);
            Bouncer::refreshFor(\Auth::user());

            return $this->createdResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $m = self::MODEL;

        $data = $m::withCount(['currentevents', 'events']);

        // if ($request->exists('User')) {
        //     $edit       = User::WhereCan('edit', $data)->get();
        //     $administer = User::WhereCan('administer', $data)->get();
        //     return $this->showResponse(['edit' => $edit, 'administer' => $administer]);
        // }

        if ($request->exists('Unconfirmed')) {
            $data = $data->Unconfirmed();
        }
        if ($request->exists('ConfirmedAndUnconfirmed')) {
            $data = $data->ConfirmedAndUnconfirmed();
        }
        if ($request->exists('Private')) {
            $data = $data->Private();
        }
        if ($request->exists('PublicAndPrivate')) {
            $data = $data->PublicAndPrivate();
        }

        if ($data = $data->find($id)) {
            return $this->showResponse($data);
        }

        return $this->notFoundResponse();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //autorelates venue and participants in model
        $m = self::MODEL;
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if (!(
            (Bouncer::allows('administer', $data)) or
            (Bouncer::allows('edit', $data)) or
            (Bouncer::allows($this->edititems))
        )) {
            return $this->unauthorizedResponse();
        }

        return $this->showResponse($data);
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

        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if (!(
            (Bouncer::allows('administer', $data)) or
            (Bouncer::allows('edit', $data)) or
            (Bouncer::allows($this->edititems))
        )) {
            return $this->unauthorizedResponse();
        }

        try
        {
            $input = $request->all();
            // at this point any of these users can make public
            // BUT if a user cant set confirmed, strip it:
            if (!(Bouncer::allows($this->confirmitems))) {
                $input = $request->except(['confirmed']);
            }

            // $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->validationRules);

            // if ($v->fails()) {
            //     throw new \Exception("ValidationException");
            // }
            $data->fill($input);
            $data->save();
            return $this->showResponse($data);
        } catch (\Exception $ex) {
            $data = ['exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

    }

    public function destroy($id)
    {
        $m = self::MODEL;
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if (!(
            (Bouncer::allows('administer', $data)) or
            (Bouncer::allows('edit', $data)) or
            (Bouncer::allows($this->edititems))
        )) {
            return $this->unauthorizedResponse();
        }

        $data->delete();
        return $this->deletedResponse();
    }

    // public function map(Request $request)
    // {
    //     $location          = $request->input('l', '41.291824,-87.763978');
    //     $distanceinmeters  = $request->input('d', 100000); //(in meters)
    //     $distanceindegrees = $distanceinmeters / 111195; //(degrees (approx))

    //     $m = self::MODEL;
    //     //$data = $m::pluck('name', 'location');
    //     $data = $m::distance($distanceindegrees, $location)->get();
    //     //$data = $m::distance($distanceindegrees, $location)->pluck('name', 'location');

    //     return $this->listResponse($data);

    // }

    public function getMap(Request $request)
    {
        $m = self::MODEL;

        $center = $request->input('center', '41.964072,-87.687302');
        $count  = $request->input('count', '50');

        $distanceinmeters  = $request->input('limit', 100000); //(in meters)
        $distanceindegrees = $distanceinmeters / 111195; //(degrees (approx))
        $limit             = $distanceinmeters;

        //$data = $m::WithDistanceFrom($limit, $center)->get();
        $data = $m::WithRadiusFrom($limit, $center)->take($count)->get();
        return $this->listResponse($data);
    }

    public function details($id)
    {
        $m = self::MODEL;
        if ($data = $m::with('eventslistcurrent')
            ->withCount('events')
            ->withCount('ilike')
            ->withCount('likes')
            ->withCount('pyfslike')
            ->with('pyfslike')
            ->find($id)) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();
    }

    public function isValidLongitude($longitude)
    {
        if (preg_match("/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/", $longitude)) {
            return true;
        } else {
            return false;
        }
    }
    public function isValidLatitude($latitude)
    {

        if (preg_match("/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/", $latitude)) {
            return true;
        } else {
            return false;
        }
    }

}
