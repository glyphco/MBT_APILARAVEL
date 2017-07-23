<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Models\User;
use App\Traits\ItemConfirmableTrait;
use App\Traits\ItemHasAdminsTrait;
use App\Traits\ItemHasEditorsTrait;
use App\Traits\ItemPrivateableTrait;
use Bouncer;
use Illuminate\Http\Request;

class PageController extends BaseController
{
    use ItemConfirmableTrait, ItemPrivateableTrait, ItemHasAdminsTrait, ItemHasEditorsTrait;

    const MODEL                = 'App\Models\Page';
    protected $validationRules = [
        'name'       => 'required',
        'category'   => 'required',
        'city'       => 'required',
        'state'      => 'required',
        'postalcode' => 'required',
    ];

    protected $createitems  = 'create-pages';
    protected $adminitems   = 'admin-pages';
    protected $edititems    = 'edit-pages';
    protected $confirmitems = 'confirm-pages';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $m    = self::MODEL;
        $data = $m::withCount([
            'eventsAsParticipant',
            'eventsAsProducer',
            'likes',
        ]);

        $data = $data->with(['categories']);

        if ($request->exists('q')) {
            $data = $data->where('name', 'like', '%' . $request['q'] . '%');
        }
        if ($request->exists('participant')) {
            $data = $data->where('participant', 1);
        }
        if ($request->exists('production')) {
            $data = $data->where('production', 1);
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
        $data = $m::PublicAndPrivate()
            ->ConfirmedAndUnconfirmed();

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

        if (!((Bouncer::allows('edit-pages')) or (Bouncer::allows('edit', $data)))) {
            return $this->unauthorizedResponse();
        }

        return $this->showResponse($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
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
        $m = self::MODEL;
        if ($data = $m::withCount([
            'eventsAsParticipant',
            'eventsAsProducer',
            'likes',
        ])
            ->with('groups')->with('members')->with('categories')
            ->find($id)) {
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

        if (!((Bouncer::allows('edit-pages')) or (Bouncer::allows('edit', $data)))) {
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $m = self::MODEL;

        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }
        if (!((Bouncer::allows('edit-pages')) or (Bouncer::allows('edit', $data)))) {
            return $this->unauthorizedResponse();
        }
        $data->delete();
        return $this->deletedResponse();
    }

    public function getlikes($id)
    {

        $m = self::MODEL;
        if ($data = $m::with('likes')->find($id)->likes) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();

    }

    public function getevents($id)
    {

        $m = self::MODEL;
        if ($data = $m::with('events')->find($id)->events) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();

    }

}
