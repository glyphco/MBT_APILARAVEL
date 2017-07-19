<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Models\User;
use Bouncer;
use Illuminate\Http\Request;

class EventController extends BaseController
{
    //use HasRolesAndAbilities;

    const MODEL                = 'App\Models\Event';
    protected $validationRules = [
        'name'        => 'required',
        'UTC_start'   => 'required',
        'local_start' => 'required',
        'local_tz'    => 'required',
    ];
    protected $friends;

    public function index(Request $request)
    {
        $date    = null;
        $enddate = null;

        if ($request->has('date')) {
            //Date was given, check if ok and add it to the enddate as well
            //see if date is a unix timestamp
            $date = $request->input('date');
            if (!((string) (int) $date === $date) && ($date <= PHP_INT_MAX) && ($date >= ~PHP_INT_MAX)) {
                if (!$date = strtotime($request->input('date'))) {
                    return $this->clientErrorResponse('Invalid Date: date cannot be converted properly');
                }
            }
            //Set Enddate while youre at it
            $startdate = date('Y-m-d' . ' 00:00:00', $date);
            $enddate   = date('Y-m-d' . ' 23:59:59', $date);
            $date      = $startdate;
        }

        if ($request->has('enddate')) {
            if (!$enddate = strtotime($request->input('enddate'))) {
                return $this->clientErrorResponse('Invalid Date: end date cannot be converted properly');
            }
            $enddate = date('Y-m-d' . ' 23:59:59', $enddate);
        }

        $ranks = [2, 3];

        $m = self::MODEL;

        $this->friends = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        $data = $m::with(['mve', 'categories'])
            ->withCount([
                'attendingyes',
                'attendingmaybe',
                'attendingwish',
                'friendsattendingyes',
                'friendsattendingmaybe',
                'friendsattendingwish',
            ])
            ->with([
                'attendingyes',
                'attendingmaybe',
                'attendingwish',
                'friendsattendingyes',
                'friendsattendingmaybe',
                'friendsattendingwish',
            ])
        ;

        if ($date) {
            $data = $data->InDateRange($date, $enddate);
        }

        if ($request->exists('current')) {
            $data = $data->Current();
        }
        if ($request->has('v')) {
            $data = $data->AtVenue($request->input('v'));
        }
        if ($request->has('vn')) {
            $data = $data->AtVenuename($request->input('vn'));
        }
        if ($request->has('p')) {
            $data = $data->ByEventParticipant($request->input('p'));
        }
        if ($request->has('pn')) {
            $data = $data->ByEventParticipantname($request->input('pn'));
        }
        if ($request->has('sc')) {
            $data = $data->ByEventSubcategory($request->input('sc'));
        }
        if ($request->has('c')) {
            $data = $data->ByEventCategory($request->input('c'));
        }

        if ($request->has('lat') && $request->has('dist') && $request->has('lng') && $this->isValidLatitude($request->input('lat')) && $this->isValidLongitude($request->input('lng'))) {
            // /^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/
            // /^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/

            $data = $data->distance($request->input('dist'), $request->input('lat') . ',' . $request->input('lng'));
        }

        $pp = $request->input('pp', 25);
        if ($pp > 100) {$pp = 100;}
        $data = $data->paginate($pp);

        //dd($data->toArray());

        //dd($data->appends(['category' => 'category_name']));
        // $data['debug'] = [
        //     'date'    => $date,
        //     'enddate' => $enddate,
        //     'request' => $request->except('page'),
        // ];

        return $this->listResponse($data);

    }

    public function editable(Request $request)
    {

        $m    = self::MODEL;
        $data = $m::PublicAndPrivate()
            ->ConfirmedAndUnconfirmed()
            ->with(['mve', 'categories']);

        $pp = $request->input('pp', 25);
        if ($pp > 100) {$pp = 100;}

//If you can edit all, get all!
        if (Bouncer::allows('edit-events')) {
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
        if (!(Bouncer::allows('create-events'))) {
            return $this->unauthorizedResponse();
        }

        $m = self::MODEL;

        try
        {
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->validationRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }
            $request->request->add(['location' => implode(', ', $request->only('lat', 'lng'))]);
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
    public function show($id)
    {
        //autorelates venue and participants in model
        $m = self::MODEL;
        if ($data = $m::find($id)) {
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

        if (!((Bouncer::allows('edit-events')) or (Bouncer::allows('edit', $data)))) {
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

        if (!((Bouncer::allows('edit-events')) or (Bouncer::allows('edit', $data)))) {
            return $this->unauthorizedResponse();
        }

        try
        {
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->validationRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }
            $data->fill($request->all());
            $data->save();
            return $this->showResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }

    public function destroy($id)
    {
        $m = self::MODEL;
        if (!$data = $m::find($id)) {
            return $this->notFoundResponse();
        }
        if (!((Bouncer::allows('edit-events')) or (Bouncer::allows('edit', $data)))) {
            return $this->unauthorizedResponse();
        }

        $data->delete();
        return $this->deletedResponse();
    }

    public function confirm($id)
    {
        $m = self::MODEL;

        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if (!(Bouncer::allows('confirm-events'))) {
            return $this->unauthorizedResponse();
        }

        try
        {
            $data->confirmed = 1;
            $data->save();
            return $this->showResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

    }
    public function unconfirm($id)
    {
        $m = self::MODEL;

        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if (!(Bouncer::allows('confirm-events'))) {
            return $this->unauthorizedResponse();
        }

        try
        {
            $data->confirmed = 0;
            $data->save();
            return $this->showResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }

    public function giveedit(Request $request, $id, $userid)
    {
        $m = self::MODEL;
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }
        if (!$otheruser = User::find($userid)) {
            return $this->notFoundResponse();
        }
        if ((Bouncer::allows('admin-events')) or (Bouncer::allows('administer', $data))) {
            Bouncer::allow($otheruser)->to('edit', $data);
            return $this->showResponse('');
        }
        return $this->unauthorizedResponse();
    }

    public function revokeedit(Request $request, $id, $userid)
    {
        $m = self::MODEL;
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }
        if (!$otheruser = User::find($userid)) {
            return $this->notFoundResponse();
        }
        if ((Bouncer::allows('edit-events')) or (Bouncer::allows('edit', $data))) {
            Bouncer::disallow($otheruser)->to('edit', $data);
            return $this->showResponse('');
        }
        return $this->unauthorizedResponse();
    }

    public function getEditors(Request $request, $id)
    {
        $m = self::MODEL;
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if ((Bouncer::allows('admin-events')) or (Bouncer::allows('administer', $data))) {
            $users = User::WhereCan('edit', $data)->select('id', 'name', 'avatar')->get();
            return $this->showResponse($users);
        }
        return $this->unauthorizedResponse();
    }

    public function makepublic($id)
    {
        $m = self::MODEL;

        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if ((Bouncer::allows('edit', $data)) or (Bouncer::allows('administer', $data)) or (Bouncer::allows('edit-events'))) {
            try
            {
                $data->public = 1;
                $data->save();
                return $this->showResponse($data);
            } catch (\Exception $ex) {
                $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
                return $this->clientErrorResponse($data);
            }

        } else {
            return $this->unauthorizedResponse();
        }

    }
    public function makeprivate($id)
    {
        $m = self::MODEL;

        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if ((Bouncer::allows('edit', $data)) or (Bouncer::allows('administer', $data)) or (Bouncer::allows('edit-events'))) {
            try
            {
                $data->public = 1;
                $data->save();
                return $this->showResponse($data);
            } catch (\Exception $ex) {
                $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
                return $this->clientErrorResponse($data);
            }

        } else {
            return $this->unauthorizedResponse();
        }
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
