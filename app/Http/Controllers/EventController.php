<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Models\User;
use App\Traits\ItemConfirmableTrait;
use App\Traits\ItemHasAdminsTrait;
use App\Traits\ItemHasEditorsTrait;
use App\Traits\ItemPrivateableTrait;
use Bouncer;
use DB;
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
    protected $pyfs;

    protected $createitems  = 'create-events';
    protected $adminitems   = 'admin-events';
    protected $edititems    = 'edit-events';
    protected $confirmitems = 'confirm-events';

    public function index(Request $request)
    {
        $m = self::MODEL;
        return $this->listResponse($m::selectRaw(' *, ST_AsText(location) as locationx')->get());
    }

    public function oldindex(Request $request)
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

        $this->pyfs = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        $data = $m::with(['mve', 'categories'])
        // ->withCount([
        //     'attendingyes',
        //     'attendingmaybe',
        //     'attendingwish',
        //     'pyfsattendingyes',
        //     'pyfsattendingmaybe',
        //     'pyfsattendingwish',
        // ])
        // ->with([
        //     'iattending',
        //     'attendingyes',
        //     'attendingmaybe',
        //     'attendingwish',
        //     'pyfsattendingyes',
        //     'pyfsattendingmaybe',
        //     'pyfsattendingwish',
        // ])
        ;

        $data = $data->where('confirmed', '=', 1);
        $data = $data->where('public', '=', 1);

        if ($date) {
            $data = $data->InDateRange($date, $enddate);
        }

//$data = $data->WithRadiusFrom($query, $max, $latlng);

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

            // $data = $data->distance($request->input('dist'), $request->input('lat') . ',' . $request->input('lng'));

            $data = $data->near(
                $request->input('lat'),
                $request->input('lng'),
                $request->input('dist', 50),
                $request->input('units', 'MILES')
            )

                ->orderBy('distance', 'asc');

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
        $data = $m::with('mve')->backstageCurrent();

        $pp = $request->input('pp', 25);
        if ($pp > 100) {$pp = 100;}

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

        //If you can edit or admin all, get all!
        //Otherwise only the ones you can get to:
        if (!((Bouncer::allows($this->edititems)) or (Bouncer::allows($this->adminitems)))) {
            $data = $data->wherein('id', \Auth::User()->abilities->where('entity_type', self::MODEL)->pluck('entity_id'));
        }

        if ($request->has('sortby')) {
            switch ($request->input('sortby')) {
                case 'date':
                    $data = $data
                        ->orderBy('UTC_start', 'ASC')
                        ->orderBy('venue_name', 'ASC')
                        ->orderBy('name', 'DESC');
                    break;
                case 'venue':
                    $data = $data
                        ->orderBy('venue_name', 'ASC')
                        ->orderBy('UTC_start', 'ASC')
                        ->orderBy('name', 'DESC');
                    break;
                case 'event':
                    $data = $data
                        ->orderBy('name', 'DESC')
                        ->orderByRaw('date(UTC_start) ASC');
                    break;
                default:
                    $data = $data
                        ->orderBy('UTC_start', 'ASC')
                        ->orderBy('venue_name', 'ASC')
                        ->orderBy('name', 'DESC');
                    break;
            }
        } else {
            $data = $data->orderBy('UTC_start', 'ASC')
                ->orderBy('venue_name', 'ASC')->orderBy('name', 'DESC');
        }

        $data = $data->paginate($pp);

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

        if ($request->exists('shows')) {
            $showsjson         = $this->saveShows($request['shows'], $data->id);
            $data['showsjson'] = $showsjson;
            $data->save();
        }

        if ($request->exists('categories')) {
            $categoriesjson         = $this->saveCategories($request['categories'], $data->id);
            $data['categoriesjson'] = $categoriesjson;
            $data->save();
        }

        if ($request->exists('participants')) {
            $participantsjson         = $this->saveParticipants($request['participants'], $data->id);
            $data['participantsjson'] = $participantsjson;
            $data->save();
        }

        if ($request->exists('producers')) {
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
        if ($data = $m::with([
            'mve',
            'venue',
            'categories',
            'eventshows',
            'eventparticipants',
            'eventproducers',

        ])
            ->withCount([
                'attendingyes',
                'attendingmaybe',
                'attendingwish',
                'pyfsattendingyes',
                'pyfsattendingmaybe',
                'pyfsattendingwish',
            ])
            ->with([
                'attendingyes',
                'attendingmaybe',
                'attendingwish',
                'pyfsattendingyes',
                'pyfsattendingmaybe',
                'pyfsattendingwish',
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
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()
            ->with([
                'mve',
                'venue',
                'categories',
                'eventshows',
                'eventparticipants',
                'eventproducers',

            ])
            ->find($id)) {
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
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

        if ($request->exists('shows')) {
            $showsjson         = $this->saveShows($request['shows'], $data->id);
            $data['showsjson'] = $showsjson;
            $data->save();
        }

        if ($request->exists('categories')) {
            $categoriesjson         = $this->saveCategories($request['categories'], $data->id);
            $data['categoriesjson'] = $categoriesjson;
            $data->save();
        }

        if ($request->exists('producers')) {
            $this->saveProducers($request['producers'], $data->id);
        }

        if ($request->exists('participants')) {
            $participantsjson         = $this->saveParticipants($request['participants'], $data->id);
            $data['participantsjson'] = $participantsjson;
            $data->save();
        }

        $data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id);
        return $this->showResponse($data);

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

    private function saveShows($showsJson, $event_id)
    {

        $deletedRows = \App\Models\EventShow::where('event_id', $event_id)->delete();
        $showarray   = [];

        if (!$shows = json_decode($showsJson, true)) {
            return json_encode($showarray);
        }

        foreach ($shows as $key => $value) {

            $show_id = array_key_exists('id', $value) ? $value['id'] : '';

            if (!$show = \App\Models\Show::find($show_id)) {
                continue;
            }

            $saveshow = [
                'show_id'  => $show['id'],
                'name'     => $show['name'],
                'info'     => $show['tagline'],
                'imageurl' => $show['imageurl'],
            ];

            $extra = [
                'event_id' => $event_id,
            ];

            $data = \App\Models\EventShow::create(array_merge($saveshow, $extra));

            $showarray[] = $saveshow;
        }

        return json_encode($showarray);
    }

    private function saveCategories($categoriesJson, $event_id)
    {
        $deletedRows = \App\Models\EventCategory::where('event_id', $event_id)->delete();

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
                'event_id' => $event_id,
            ];

            $data = \App\Models\EventCategory::create(array_merge($savecategory, $extra));

            $categoryarray[] = $savecategory;
        }

        return json_encode($categoryarray);
    }

    private function saveProducers($producersJson, $event_id)
    {
        $deletedRows = \App\Models\EventProducer::where('event_id', $event_id)->delete();

        $producerarray = [];
        if (!$producers = json_decode($producersJson, true)) {
            return json_encode($producerarray);
        }

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

            $data = \App\Models\EventProducer::create(array_merge($saveproducer, $extra));

            $producerarray[] = $saveproducer;

        }
        return json_encode($producerarray);
    }

    private function saveParticipants($participantsJson, $event_id)
    {
        $deletedRows = \App\Models\EventParticipant::where('event_id', $event_id)->delete();

        $participantarray = [];
        if (!$participants = json_decode($participantsJson, true)) {
            return json_encode($participantarray);
        }

        foreach ($participants as $key => $value) {

            if (!array_key_exists('name', $value)) {
                continue;
            }

            $participant_id = '';
            if ((array_key_exists('page_id', $value)) && (is_int($value['page_id']))) {

                if ($participant = \App\Models\Page::where('participant', 1)->find($value['page_id'])) {
                    $participant_id = $value['page_id'];
                }
            }

            $saveparticipant = [
                'name'     => $value['name'],
                'imageurl' => array_key_exists('imageurl', $value) ? $value['imageurl'] : '',
                'start'    => array_key_exists('start', $value) ? $value['start'] : '',
            ];

            if ($participant_id) {
                $saveparticipant['page_id'] = $participant_id;
            }

            $extra = [
                'event_id'     => $event_id,
                'info'         => array_key_exists('info', $value) ? $value['info'] : '',
                'private_info' => array_key_exists('private_info', $value) ? $value['private_info'] : '',
                'end'          => array_key_exists('end', $value) ? $value['imageurl'] : '',
                'order'        => array_key_exists('order', $value) ? $value['order'] : '0',
                'public'       => array_key_exists('public', $value) ? $value['public'] : '0',
                'confirmed'    => array_key_exists('confirmed', $value) ? $value['confirmed'] : '0',
            ];

            $data = \App\Models\EventParticipant::create(array_merge($saveparticipant, $extra));

//only add them to the participants array if they're confirmed and public
            if (($extra['public'] == 1) and ($extra['confirmed'] == 1)) {
                $participantarray[] = $saveparticipant;
            }
        }

        return json_encode($participantarray);
    }

    public function today(Request $request)
    {

        $tz   = 'America/Chicago';
        $lat  = '41.875792256780315';
        $lng  = '-87.62811183929443';
        $dist = '5000';

        if ($request->has('tz') && $this->isValidTimezoneId($request->input('tz'))) {
            $tz = $request->input('tz');
        }

        if ($request->has('lat') && $this->isValidLatitude($request->input('lat'))) {
            $lat = $request->input('lat');
        }

        if ($request->has('lng') && $this->isValidLongitude($request->input('lng'))) {
            $lng = $request->input('lng');
        }

        if ($request->has('dist')) {
            $dist = $request->input('dist');
        }

        $m    = self::MODEL;
        $data = $m::withCount([
            'attendingyes',
            'attendingmaybe',
            //'attendingwish',
            'pyfsattendingyes',
            'pyfsattendingmaybe',
            //'pyfsattendingwish',
        ])
            ->with([
                'iattending',
                'pyfsattendingyes_list',
                //'pyfsattendingmaybe_list',
                //'pyfsattendingwish_list',
            ])
        ;

        $data = $data->today($tz);
        $data = $data->where('confirmed', '=', 1);
        $data = $data->where('public', '=', 1);

        $data = $data->near($lat, $lng, $dist, 'METERS');

        if ($request->has('sortby')) {
            switch ($request->input('sortby')) {
                case 'date':
                    $data = $data->orderBy('UTC_start', 'asc');
                    break;
                case 'distance':
                    $data = $data->orderBy('distance', 'asc');
                    break;
                default:
                    $data = $data->orderBy('distance', 'asc');
                    break;
            }
        } else {
            $data = $data->orderBy('distance', 'asc');
        }

        $pp = $request->input('pp', 25);
        if ($pp > 100) {$pp = 100;}
        $data = $data->paginate($pp);

        return $this->listResponse($data);

    }

    public function todayMap(Request $request)
    {
        $tz   = 'America/Chicago';
        $lat  = '41.875792256780315';
        $lng  = '-87.62811183929443';
        $dist = '5000';

        if ($request->has('tz') && $this->isValidTimezoneId($request->input('tz'))) {
            $tz = $request->input('tz');
        }

        if ($request->has('lat') && $this->isValidLatitude($request->input('lat'))) {
            $lat = $request->input('lat');
        }

        if ($request->has('lng') && $this->isValidLongitude($request->input('lng'))) {
            $lng = $request->input('lng');
        }

        if ($request->has('dist')) {
            $dist = $request->input('dist');
        }

        $m    = self::MODEL;
        $data = $m::today($tz);
        $data = $data->where('confirmed', '=', 1);
        $data = $data->where('public', '=', 1);

        $spherelocation = $lng . ',' . $lat;

        $data = $data->where(DB::raw('( ST_Distance_Sphere(location,POINT(' . $spherelocation . ')))'), '<', $dist);

        $data = $data->
            selectRaw('id, events.name, events.venue_name, events.local_start, events.lat, events.lng, ( ST_Distance_Sphere(location,POINT(' . $spherelocation . '))) as distance')

            ->get();

        return $this->listResponse($data);

    }

    public function current(Request $request)
    {

        $tz   = 'America/Chicago';
        $lat  = '41.875792256780315';
        $lng  = '-87.62811183929443';
        $dist = '5000';

        if ($request->has('tz') && $this->isValidTimezoneId($request->input('tz'))) {
            $tz = $request->input('tz');
        }

        if ($request->has('lat') && $this->isValidLatitude($request->input('lat'))) {
            $lat = $request->input('lat');
        }

        if ($request->has('lng') && $this->isValidLongitude($request->input('lng'))) {
            $lng = $request->input('lng');
        }

        if ($request->has('dist')) {
            $dist = $request->input('dist');
        }

        $m    = self::MODEL;
        $data = $m::current(); //tz unneeded for current
        $data = $data->where('confirmed', '=', 1);
        $data = $data->where('public', '=', 1);
        $data = $data
            ->withCount([
                'attendingyes',
                'attendingmaybe',
                //'attendingwish',
                'pyfsattendingyes',
                'pyfsattendingmaybe',
                //'pyfsattendingwish',
            ])
            ->with([
                'iattending',
                'pyfsattendingyes_list',
                //'pyfsattendingmaybe_list',
                //'pyfsattendingwish_list',
            ]);

        $data = $data->near($lat, $lng, $dist, 'METERS');

        if ($request->has('sortby')) {
            switch ($request->input('sortby')) {
                case 'date':
                    $data = $data->orderBy('UTC_start', 'asc');
                    break;
                case 'distance':
                    $data = $data->orderBy('distance', 'asc');
                    break;
                default:
                    $data = $data->orderBy('UTC_start', 'asc');
                    break;
            }
        } else {
            $data = $data->orderBy('UTC_start', 'asc');
        }

        $pp = $request->input('pp', 25);
        if ($pp > 100) {$pp = 100;}
        $data = $data->paginate($pp);

        return $this->listResponse($data);

    }

    public function details(Request $request, $id)
    {

        $lat = '41.875792256780315';
        $lng = '-87.62811183929443';

        if ($request->has('lat') && $this->isValidLatitude($request->input('lat'))) {
            $lat = $request->input('lat');
        }

        if ($request->has('lng') && $this->isValidLongitude($request->input('lng'))) {
            $lng = $request->input('lng');
        }

        //autorelates venue and participants in model
        $m = self::MODEL;
        if ($data = $m::with([
            'mve',
            'venue',
            'categories',
            'eventshows',
            'eventparticipants',
            'eventproducers',

        ])
            ->withCount([
                'attendingyes',
                'attendingmaybe',
                'attendingwish',
                'pyfsattendingyes',
                'pyfsattendingmaybe',
                'pyfsattendingwish',
            ])
            ->distance($lat, $lng)
            ->with([
                'iattending',
                'eventparticipants',
                'attendingyes_list',
                'attendingmaybe_list',
                'attendingwish_list',
                'pyfsattendingyes_list',
                'pyfsattendingmaybe_list',
                'pyfsattendingwish_list',
            ])
            ->where('confirmed', '=', 1)
            ->where('public', '=', 1)
            ->distance($lat, $lng, 'METERS')
            ->find($id)) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();

    }

    private function isValidLongitude($longitude)
    {
        if (preg_match("/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/", $longitude)) {
            return true;
        } else {
            return false;
        }
    }
    private function isValidLatitude($latitude)
    {

        if (preg_match("/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/", $latitude)) {
            return true;
        } else {
            return false;
        }
    }

    private function isValidTimezoneId($tzid)
    {
        $valid = array();
        $tza   = timezone_abbreviations_list();
        foreach ($tza as $zone) {
            foreach ($zone as $item) {
                $valid[$item['timezone_id']] = true;
            }
        }

        unset($valid['']);
        $res = isset($valid[$tzid]);
        return $res;
    }

}
