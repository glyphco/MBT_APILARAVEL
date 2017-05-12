<?php
namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\participant;
use App\Models\Profile;
use App\Models\User;
use App\Models\Venue;
use App\Traits\APIResponderTrait;
use DB;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class MaintenanceController extends BaseController {
	use APIResponderTrait;
	use HasRolesAndAbilities;

	protected $addressbook;

	public function __construct(Event $event, Venue $venue, Profile $profile, Participant $participant, User $user) {
		$this->event       = $event;
		$this->venue       = $venue;
		$this->profile     = $profile;
		$this->participant = $participant;
		$this->user        = $user;
	}

	//finds all venues on public and confirmed events
	//that are not linked to venues in the Database
	public function unlinkedVenues(Request $request) {
		$data = $this->event
			->select(DB::raw('count(*) as times_used, venue_name'))
			->wherenull('venue_id')
			->having('times_used', '>', 1)
			->groupBy('venue_name')
			->get();
		return $this->listResponse($data);
	}

	//finds all Participants on public and confirmed events
	//that are not linked to venues in the Database
	public function unlinkedParticipants(Request $request) {
		$data = $this->participant
			->select(DB::raw('count(*) as times_used, name'))
			->wherenull('profile_id')
			->having('times_used', '>', 1)
			->groupBy('name')
			->get();
		return $this->listResponse($data);
	}

}
