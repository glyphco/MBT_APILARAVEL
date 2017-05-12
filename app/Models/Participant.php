<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class Participant extends Model {
	use Userstamps;
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'event_id',
		'name',
		'details',
		'profile_id',
		'start',
		'end',
		'order',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
	];

	/**
	 * Get all the Venues for an Event.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */

	public function event() {
		return $this->belongsTo('App\Models\Event');
	}

	public function profile() {
		return $this->belongsTo('App\Models\Profile');
	}

}
