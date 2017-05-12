<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\APIResponderTrait;
use App\Traits\RestControllerTrait;
use Bouncer;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class VenueController extends BaseController
{
    use RestControllerTrait;
    use APIResponderTrait;
    use HasRolesAndAbilities;

    const MODEL                = 'App\Models\Venue';
    protected $validationRules = [
        'name'           => 'required',
        'category'       => 'required',
        'street_address' => 'required',
        'city'           => 'required',
        'state'          => 'required',
        'postalcode'     => 'required',
        'lat'            => 'required',
        'lng'            => 'required',
    ];

    public function index(Request $request)
    {
        $m = self::MODEL;
        //$data = $m;

        $data = $m::withCount(['currentevents', 'events']);
        //$data = $data->with('currentevents');

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
        $data = $data->get();

        return $this->listResponse($data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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

        $data = $m::withCount(['currentevents', 'events'])->find($id);

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

        if ((Bouncer::allows('edit', $data)) or (Bouncer::allows('administer', $data)) or (Bouncer::allows('edit-venue'))) {
            try
            {
                $input = $request->all();
                // at this point any of these users can make public
                // BUT if a user cant set confirmed, strip it:
                if (!(Bouncer::allows('confirm-venue'))) {
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
                $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
                return $this->clientErrorResponse($data);
            }

        } else {
            return $this->unauthorizedResponse();
        }
    }

    public function destroy($id)
    {
        $m = self::MODEL;
        if (!$data = $m::find($id)) {
            return $this->notFoundResponse();
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

        if (!(Bouncer::allows('confirm-venues'))) {
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

        if (!(Bouncer::allows('confirm-venues'))) {
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
        if ((Bouncer::allows('admin-venues')) or (Bouncer::allows('administer', $data))) {
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
        if ((Bouncer::allows('admin-venues')) or (Bouncer::allows('administer', $data))) {
            Bouncer::disallow($otheruser)->to('edit', $data);
            return $this->showResponse('');
        }
        return $this->unauthorizedResponse();
    }

    public function giveadmin(Request $request, $id, $userid)
    {
        $m = self::MODEL;
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }
        if (!$otheruser = User::find($userid)) {
            return $this->notFoundResponse();
        }
        if ((Bouncer::allows('admin-venues')) or (Bouncer::allows('administer', $data))) {
            Bouncer::allow($otheruser)->to('administer', $data);
            return $this->showResponse('');
        }
        return $this->unauthorizedResponse();
    }

    public function revokeadmin(Request $request, $id, $userid)
    {
        $m = self::MODEL;
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }
        if (!$otheruser = User::find($userid)) {
            return $this->notFoundResponse();
        }
        if ((Bouncer::allows('admin-venues')) or (Bouncer::allows('administer', $data))) {
            Bouncer::disallow($otheruser)->to('administer', $data);
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

        if ((Bouncer::allows('admin-venues')) or (Bouncer::allows('administer', $data))) {
            $users = User::WhereCan('edit', $data)->select('id', 'name', 'avatar')->get();
            return $this->showResponse($users);
        }
        return $this->unauthorizedResponse();
    }

    public function getAdmins(Request $request, $id)
    {
        $m = self::MODEL;
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if ((Bouncer::allows('admin-venues')) or (Bouncer::allows('administer', $data))) {
            $users = User::WhereCan('administer', $data)->select('id', 'name', 'avatar')->get();
            return $this->showResponse($users);
        }
        return $this->unauthorizedResponse();
    }

    public function map(Request $request)
    {
        $location          = $request->input('l', '41.291824,-87.763978');
        $distanceinmeters  = $request->input('d', 100000); //(in meters)
        $distanceindegrees = $distanceinmeters / 111195; //(degrees (approx))

        $m = self::MODEL;
        //$data = $m::pluck('name', 'location');
        $data = $m::distance($distanceindegrees, $location)->get();
        //$data = $m::distance($distanceindegrees, $location)->pluck('name', 'location');

        return $this->listResponse($data);

    }

}
