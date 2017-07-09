<?php
namespace App\Http\Controllers\bots;

use App\Http\Controllers\Controller as BaseController;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class Bot2Controller extends BaseController
{

//This bot grabs the eventvenue the most of your friends are going to (popular with friends)

    public function index(Request $request)
    {
        $date    = null;
        $enddate = null;

//Dates unneeded, just grabbing current

// RAW:
        $raw = "
select ev.*,eventvenue_id, rank, count(user_id) as attending from attending a
left join event_venues ev on (ev.id = a.eventvenue_id)
where (rank =?)
and ev.start > ?
and user_id in (
select friend_id from friendships
where (user_id =?))
group by eventvenue_id, rank
order by attending desc

";
        $raw = "
select ev.*, ST_AsText(location) as location,eventvenue_id, rank, count(user_id) as attending from attending a
left join event_venues ev on (ev.id = a.eventvenue_id)
where (rank =?)
and ev.start >= ?
and ev.end >= ?
and user_id in (
select friend_id from friendships
where (user_id =?))
group by eventvenue_id, rank
order by attending desc
limit ?
";

        $timestart = Carbon::now()->subHours(5)->toDateString();
        $timeend   = Carbon::now()->subHours(5)->toDateString();

        $events = DB::select($raw, [3, $timestart, $timeend, \Auth::id(), 25]);
        // dd($event_ids);

        // $data = collect($events);

        $pp = $request->input('pp', 25);
        if ($pp > 100) {$pp = 100;}
        // $data = $data->paginate($pp);
        $offset = 0;
        $page   = 1;

        return new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($events, $offset, $pp, true), // Only grab the items we need
            count($events), // Total items
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
