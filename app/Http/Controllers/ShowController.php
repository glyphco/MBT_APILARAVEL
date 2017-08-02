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

class ShowController extends BaseController
{
    use ItemConfirmableTrait, ItemPrivateableTrait, ItemHasAdminsTrait, ItemHasEditorsTrait, ItemLikeableTrait;

    const MODEL                = 'App\Models\Show';
    protected $validationRules = [
        'name' => 'required',
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
        $data = $m::withCount('events')->withCount('likes');
        if ($request->exists('q')) {
            $data = $data->where('name', 'like', $request['q'] . '%');
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!(Bouncer::allows('create-pages'))) {
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
        if ($request['categories']) {
            if ($categoriesjson = $this->saveCategories($request['categories'], $data->id)) {
                $data['categoriesjson'] = $categoriesjson;
                $data->save;
            }
        }

        Bouncer::allow(\Auth::user())->to('administer', $data);
        Bouncer::allow(\Auth::user())->to('edit', $data);
        Bouncer::refreshFor(\Auth::user());

        return $this->createdResponse($data);

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
    public function show($id)
    {
        $m = self::MODEL;
        if ($data = $m::withCount('events')->withCount('likes')->find($id)) {
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

        if (!((Bouncer::allows('edit-pages')) or (Bouncer::allows('edit', $data)))) {
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

        if (!$data = $m::find($id)) {
            return $this->notFoundResponse();
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

    private function saveCategories($categoriesJson, $show_id)
    {

        if (!$categories = json_decode($categoriesJson, true)) {
            return false;
        }
        $categoriesarray = [];

        $deletedRows = \App\Models\ShowCategory::where('show_id', $show_id)->delete();

        foreach ($categories as $key => $value) {

            $category_id    = array_key_exists('category_id', $value) ? $value['category_id'] : '';
            $subcategory_id = array_key_exists('subcategory_id', $value) ? $value['subcategory_id'] : '';

            if (!\App\Models\Category::find($category_id)) {
                return false;
            }

            if ($subcategory_id) {
                if (!$subcategory = \App\Models\Subcategory::where('category_id', $category_id)->find($subcategory_id)) {
                    return false;
                }
            }

            $savecategory = [
                'category_id'      => $subcategory['category_id'],
                'subcategory_id'   => $subcategory['id'],
                'subcategory_name' => $subcategory['name'],
            ];

            $extra = [
                'show_id' => $show_id,
            ];

            $data = \App\Models\ShowCategory::create(array_merge($savecategory, $extra));

            return json_encode($savecategory);
        }
    }

}
