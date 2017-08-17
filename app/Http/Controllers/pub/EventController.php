<?php
namespace App\Http\Controllers\pub;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

class EventController extends BaseController
{

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

        $data = \App\Models\pub\Event::today($tz);
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getEvent($id)
    {
        //autorelates venue and participants in model
        if ($data = \App\Models\pub\Event::with('eventparticipants.page')
            ->with('eventproducers.page')
            ->with('eventshows.show')
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
