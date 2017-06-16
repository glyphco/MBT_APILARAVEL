<?php
namespace App\Models;

use App\Traits\SpacialdataTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class EventVenue extends Model
{
    use Userstamps;
    use SpacialdataTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'venue_id',
        'venue_name',
        'street_address',
        'city',
        'state',
        'postalcode',
        'lat',
        'lng',
        'venue_tagline',
        'start',
        'end',
        'info',
        'private_info',
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
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new \App\Scopes\WithEventVenueParticipantsScope);
    }

    /**
     * Get all the Venues for an Event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function event()
    {
        return $this->belongsTo('App\Models\Event');
    }

    public function venue()
    {
        return $this->belongsTo('App\Models\Venue');
    }

    public function eventvenueparticipants()
    {
        return $this->hasMany('App\Models\EventVenueParticipant');
    }

    public function categories()
    {
        return $this->hasMany('App\Models\EventVenueCategory')->with(['category', 'subcategory']);
    }

    public function scopeCurrent($filter)
    {

        return $filter->where(function ($query) {
            $query
            //Start in date range
            ->whereDate('start', '>=', Carbon::now()->subHours(5)->toDateString())
            //End in date range
                ->orWhereDate('end', '>=', Carbon::now()->subHours(5)->toDateString());
        });
    }

}
