<?php
namespace App\Http\Controllers\pub;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

class EventvenueController extends BaseController
{

    public function getEventvenues(Request $request)
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

        $m = '\App\Models\EventVenue';

        $data = $m::with(['event.eventshows', 'event.eventproducers', 'categories', 'venue'])
            ->withCount([
                'attendingyes',
                'attendingmaybe',
                'attendingwish',
            ])
            ->current()
        ;

        if ($date) {
            $data = $data->InDateRange($date, $enddate);
        }

        if ($request->exists('current')) {
            $data = $data->Current();
        }
        if ($request->has('v')) {
            $data = $data->AtEventVenue($request->input('v'));
        }
        if ($request->has('vn')) {
            $data = $data->AtEventVenuename($request->input('vn'));
        }
        if ($request->has('p')) {
            $data = $data->ByEventVenueParticipant($request->input('p'));
        }
        if ($request->has('pn')) {
            $data = $data->ByEventVenueParticipantname($request->input('pn'));
        }
        if ($request->has('sc')) {
            $data = $data->ByEventVenueSubcategory($request->input('sc'));
        }
        if ($request->has('c')) {
            $data = $data->ByEventVenueCategory($request->input('c'));
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getEventvenue($id)
    {
        //autorelates venue and participants in model
        $m = self::MODEL;
        if ($data = $m::find($id)) {
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
