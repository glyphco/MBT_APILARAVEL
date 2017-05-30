<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Event;
use App\Models\Page;
use App\Models\participant;
use App\Models\User;
use App\Models\Venue;
use DB;
use Illuminate\Http\Request;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class MaintenanceController extends BaseController
{
    use HasRolesAndAbilities;

    protected $addressbook;

    public function __construct(Event $event, Venue $venue, Page $page, Participant $participant, User $user)
    {
        $this->event       = $event;
        $this->venue       = $venue;
        $this->page        = $page;
        $this->participant = $participant;
        $this->user        = $user;
    }

    //finds all venues on public and confirmed events
    //that are not linked to venues in the Database
    public function unlinkedVenues(Request $request)
    {
        $data = $this->event
            ->select(DB::raw('count(*) as times_used, venue_name'))
            ->wherenull('venue_id')
            ->having('times_used', '>', 1)
            ->groupBy('venue_name', 'events.start')
            ->orderBy('venue_name')
            ->withoutGlobalScopes()
            ->get();
        return $this->listResponse($data);
    }

    //finds all Participants on public and confirmed events
    //that are not linked to venues in the Database
    public function unlinkedParticipants(Request $request)
    {
        $data = $this->participant
            ->select(DB::raw('count(*) as times_used, name'))
            ->wherenull('page_id')
            ->having('times_used', '>', 1)
            ->groupBy('name')
            ->orderBy('name')
            ->get();
        return $this->listResponse($data);
    }

}
