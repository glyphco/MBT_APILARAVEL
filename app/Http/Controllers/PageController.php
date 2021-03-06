<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Models\User;
use App\Traits\ItemConfirmableTrait;
use App\Traits\ItemHasAdminsTrait;
use App\Traits\ItemHasEditorsTrait;
use App\Traits\ItemLikeableTrait;
use App\Traits\ItemPrivateableTrait;
use Bouncer;
use Illuminate\Http\Request;

class PageController extends BaseController
{
    use ItemConfirmableTrait, ItemPrivateableTrait, ItemHasAdminsTrait, ItemHasEditorsTrait, ItemLikeableTrait;

    const MODEL                = 'App\Models\Page';
    protected $validationRules = [
        'name'  => 'required',
        'city'  => 'required',
        'state' => 'required',
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
        $data = $m::withCount([
            'eventsAsParticipant',
            'likes',
        ]);
        $data = $data->withoutGlobalScope(\App\Scopes\PagePublicScope::class)->withoutGlobalScope(\App\Scopes\PageConfirmedScope::class);

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
                        ->orderBy('events_as_participant_count', 'desc')
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

        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

        if ($request->exists('categories')) {
            $categoriesjson         = $this->saveCategories($request['categories'], $data->id);
            $data['categoriesjson'] = $categoriesjson;
            $data->save();
        }

        Bouncer::allow(\Auth::user())->to('administer', $data);
        Bouncer::allow(\Auth::user())->to('edit', $data);
        Bouncer::refreshFor(\Auth::user());

        return $this->createdResponse($data);

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
            $data->fill($request->all());
            $v = \Illuminate\Support\Facades\Validator::make($data->toarray(), $this->validationRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }

            $data->save();

        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

        if ($request->exists('categories')) {
            $categoriesjson         = $this->saveCategories($request['categories'], $data->id);
            $data['categoriesjson'] = $categoriesjson;
            $data->save();

        }

        $data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id);
        return $this->showResponse($data);

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

    public function getevents($id)
    {

        $m = self::MODEL;
        if ($data = $m::with('events')->find($id)->events) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();

    }

    private function saveCategories($categoriesJson, $page_id)
    {

        $deletedRows   = \App\Models\PageCategory::where('page_id', $page_id)->delete();
        $categoryarray = [];

        if (!$categories = json_decode($categoriesJson, true)) {
            return json_encode($categoryarray);
        }

        foreach ($categories as $key => $value) {

            $category_id    = array_key_exists('category_id', $value) ? $value['category_id'] : '';
            $subcategory_id = array_key_exists('subcategory_id', $value) ? $value['subcategory_id'] : '';

            if (!\App\Models\Category::find($category_id)) {
                continue;
            }

            if ($subcategory_id) {
                if (!$subcategory = \App\Models\Subcategory::where('category_id', $category_id)->find($subcategory_id)) {
                    continue;
                }
            }

            $savecategory = [
                'category_id'      => $subcategory['category_id'],
                'subcategory_id'   => $subcategory['id'],
                'subcategory_name' => $subcategory['name'],
            ];

            $extra = [
                'page_id' => $page_id,
            ];

            $data = \App\Models\PageCategory::create(array_merge($savecategory, $extra));

            $categoryarray[] = $savecategory;
        }

        return json_encode($categoryarray);

    }

    public function details($id)
    {
        $m = self::MODEL;
        if ($data = $m::withCount('eventsAsParticipant')
            ->withCount('eventsAsProducer')
            ->with('eventsAsParticipantCurrent')
            ->with('eventsAsProducerCurrent')
            ->withCount('ilike')
            ->withCount('likes')
            ->withCount('pyfslike')
            ->with('pyfslike')
            ->find($id)) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();
    }

}
