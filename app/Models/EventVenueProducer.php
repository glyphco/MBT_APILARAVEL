<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class EventVenueProducer extends Model
{
    use Userstamps;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_venue_id',
        'name',
        'details',
        'page_id',
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

    public function eventvenue()
    {
        return $this->belongsTo('App\Models\EventVenue');
    }

    public function event()
    {
        return $this->hasManyThrough('App\Models\Event', 'App\Models\EventVenue');
    }

    public function venue()
    {
        return $this->eventvenue()->belongsTo('App\Models\Venue');
    }

    public function page()
    {
        return $this->belongsTo('App\Models\Page');
    }

}
