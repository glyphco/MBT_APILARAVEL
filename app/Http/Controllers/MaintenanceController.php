<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Models\EventParticipant;
use App\Models\Page;
use App\Models\User;
use App\Models\Venue;
use Bouncer;
use DB;
use Illuminate\Http\Request;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class MaintenanceController extends BaseController
{
    use HasRolesAndAbilities;

    protected $addressbook;

    public function __construct(Venue $venue, Page $page, EventParticipant $participant, User $user)
    {

        $this->venue       = $venue;
        $this->page        = $page;
        $this->participant = $participant;
        $this->user        = $user;
    }

    //finds all venues on public and confirmed events
    //that are not linked to venues in the Database
    public function unlinkedVenues(Request $request)
    {
        $data = \App\Models\Event::
            select(DB::raw('count(*) as times_used, venue_name'))
            ->wherenull('venue_id')
            ->having('times_used', '>', 1)
            ->groupBy('venue_name', 'events.UTC_start')
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

    public function getNumbers(Request $request)
    {

        $data = [];

        if ((Bouncer::allows('edit-events')) or (Bouncer::allows('admin-events'))) {
            $data['events']['all']     = \App\Models\Event::count();
            $data['events']['current'] = \App\Models\Event::current()->count();
        }

        if (Bouncer::allows('confirm-events')) {
            $data['events']['unconfirmedcurrent'] = \App\Models\Event::unconfirmed()->current()->count();
        }

        if ((Bouncer::allows('edit-pages')) or (Bouncer::allows('admin-pages'))) {
            $data['pages']['all'] = \App\Models\Page::count();
            $data['shows']['all'] = \App\Models\show::count();
        }

        if (Bouncer::allows('confirm-pages')) {
            $data['pages']['unconfirmed'] = \App\Models\Page::unconfirmed()->count();
            $data['shows']['unconfirmed'] = \App\Models\Show::unconfirmed()->count();
        }

        if ((Bouncer::allows('edit-venues')) or (Bouncer::allows('admin-venues'))) {
            $data['venues']['all'] = \App\Models\Venue::count();
        }

        if (Bouncer::allows('confirm-venues')) {
            $data['venues']['unconfirmed'] = \App\Models\Venue::unconfirmed()->count();
        }

        if ((Bouncer::allows('edit-users')) or (Bouncer::allows('admin-users')) or (Bouncer::allows('ban-users'))) {
            $data['users']['all'] = \App\Models\User::count();
        }

        return $this->listResponse($data);

    }
}
