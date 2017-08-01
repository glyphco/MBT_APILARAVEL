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

class EventController extends BaseController
{

    use ItemConfirmableTrait, ItemPrivateableTrait, ItemHasAdminsTrait, ItemHasEditorsTrait;

    const MODEL                = 'App\Models\Event';
    protected $validationRules = [
        'name'        => 'required',
        'UTC_start'   => 'required',
        'local_start' => 'required',
        'local_tz'    => 'required',
    ];
    protected $friends;

    protected $createitems  = 'create-events';
    protected $adminitems   = 'admin-events';
    protected $edititems    = 'edit-events';
    protected $confirmitems = 'confirm-events';

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
                'iattending',
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
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

        if ($request['shows']) {
            if ($showsjson = $this->saveShows($request['shows'], $data->id)) {
                $data['showsjson'] = $showsjson;
                $data->save;
            }

        }

        if ($request['categories']) {
            if ($categoriesjson = $this->saveCategories($request['categories'], $data->id)) {
                $data['categoriesjson'] = $categoriesjson;
                $data->save;
            }

        }

        if ($request['producers']) {
            $this->saveProducers($request['producers'], $data->id);
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
        //autorelates venue and participants in model
        $m = self::MODEL;
        if ($data = $m::with(['mve', 'categories'])
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
            ->find($id)) {
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
            (Bouncer::allows('edit-events'))
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
            (Bouncer::allows('edit-events'))
        )) {
            return $this->unauthorizedResponse();
        }

        try
        {

            $data->fill($request->all());

            $v = \Illuminate\Support\Facades\Validator::make($data->toArray(), $this->validationRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }

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
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if (!(
            (Bouncer::allows('administer', $data)) or
            (Bouncer::allows('edit', $data)) or
            (Bouncer::allows('edit-events'))
        )) {
            return $this->unauthorizedResponse();
        }

        $data->delete();
        return $this->deletedResponse();
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

    private function saveShows($showsJson, $event_id)
    {

        if (!$shows = json_decode($showsJson, true)) {
            return false;
        }
        $showarray = [];

        $deletedRows = \App\Models\EventShow::where('event_id', $event_id)->delete();

        foreach ($shows as $key => $value) {

            $show_id = array_key_exists('id', $value) ? $value['id'] : '';

            if (!$show = \App\Models\Show::find($show_id)) {
                continue;
            }

            $data = \App\Models\EventShow::create(['event_id' => $event_id, 'show_id' => $show_id]);

            $showarray[] = [
                'id'      => $show['id'],
                'name'    => $show['name'],
                'img_url' => $show['img_url'],
            ];
        }
        return json_encode($showarray);
    }

    private function saveCategories($categoriesJson, $event_id)
    {

        if (!$categories = json_decode($categoriesJson, true)) {
            return false;
        }
        $categoriesarray = [];

        $deletedRows = \App\Models\EventCategory::where('event_id', $event_id)->delete();

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
            $data = \App\Models\EventCategory::create(['event_id' => $event_id, 'category_id' => $category_id, 'subcategory_id' => $subcategory_id]);

            $categoryarray[] = [
                'category_id'      => $subcategory['category_id'],
                'subcategory_id'   => $subcategory['id'],
                'subcategory_name' => $subcategory['name'],
            ];
            return json_encode($categoryarray);
        }
    }

    private function saveProducers($producersJson, $event_id)
    {

        if (!$producers = json_decode($producersJson, true)) {
            return false;
        }
        $producerarray = [];

        $deletedRows = \App\Models\EventProducer::where('event_id', $event_id)->delete();

        foreach ($producers as $key => $value) {

            if (!array_key_exists('name', $value)) {
                continue;
            }

            $producer_id = '';
            if (array_key_exists('page_id', $value)) {

                if ($producer = \App\Models\Page::where('production', 1)->find($value['page_id'])) {
                    $producer_id = $value['page_id'];
                }
            }

            $saveproducer = [
                'page_id'      => $producer_id,
                'name'         => $value['name'],
                'info'         => array_key_exists('info', $value) ? $value['info'] : '',
                'private_info' => array_key_exists('private_info', $value) ? $value['private_info'] : '',
                'imageurl'     => array_key_exists('imageurl', $value) ? $value['imageurl'] : '',
            ];

            $extra = [
                'event_id'  => $event_id,
                'confirmed' => 1,
                'public'    => 1,
            ];

            $data = \App\Models\EventShow::create(array_merge($saveproducer, $extra));

            $showarray[] = $saveproducer;

        }
        return json_encode($showarray);
    }

}
