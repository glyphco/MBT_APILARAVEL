<?php
namespace App\Http\Controllers\bots;

use App\Http\Controllers\Controller as BaseController;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class Bot3Controller extends BaseController
{

//This bot grabs the most popular "shows" this week

    public function index(Request $request)
    {
        $date    = null;
        $enddate = null;

//Dates unneeded, just grabbing current

// RAW:
        $raw = "select s.*, rank, count(user_id) as attending from attending a
left join event_venues ev on (ev.id = a.eventvenue_id)
left join events e on (e.id = ev.event_id)
left join event_shows es on (es.event_id = e.id)
left join showpages s on (es.showpage_id = s.id)

where (rank =?)
and s.id is not null
and ev.start >= ?
and ev.end >= ?

group by rank, s.id
order by attending desc



";
        $timestart = Carbon::now()->subHours(5)->toDateString();
        $timeend   = Carbon::now()->subHours(5)->toDateString();

        $shows = DB::select($raw, [3, $timestart, $timeend]);

        $pp = $request->input('pp', 25);
        if ($pp > 100) {$pp = 100;}
        // $data = $data->paginate($pp);
        $offset = 0;
        $page   = 1;

        return new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($shows, $offset, $pp, true), // Only grab the items we need
            count($shows), // Total items
            $pp, // Items per page
            $page, // Current page
            ['path' => $request->url(), 'query' => $request->query()]// We need this so we can keep all old query parameters from the url
        );

        return $this->listResponse($data);

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
