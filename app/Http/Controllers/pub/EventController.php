<?php
namespace App\Http\Controllers\pub;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

class EventController extends BaseController
{

    public function getEvents(Request $request)
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

        $m = '\App\Models\pub\Event';

        $data = $m::with(['categories'])
            ->withCount([
                'attendingyes',
                'attendingmaybe',
                'attendingwish',
            ])
        ;

        if ($date) {
            $data = $data->InDateRange($date, $enddate);
        }

        if ($request->exists('current')) {
            $data = $data->Current();
        }
        if ($request->has('v')) {
            $data = $data->AtEvent($request->input('v'));
        }
        if ($request->has('vn')) {
            $data = $data->AtEventName($request->input('vn'));
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

        $data = $data->orderBy('UTC_start');

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
        //$data['now'] = Carbon::now()->subHours(5)->toDateTimeString();
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

}
