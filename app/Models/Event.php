<?php
namespace App\Models;

use App\Scopes\EventConfirmedScope;
use App\Scopes\EventOrderStartScope;
use App\Scopes\EventPublicScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class Event extends Model
{
    use Userstamps;
    use \App\Traits\SpacialdataTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'venue_name',
        'street_address',
        'city',
        'state',
        'postalcode',
        'lat',
        'lon',
        'location',
        'venue_info',
        'venue_id',
        'description',
        'start',
        'end',
        'public',
        'confirmed',
        'imageurl',
        'backgroundurl',
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
        static::addGlobalScope(new \App\Scopes\EventPublicScope);
        static::addGlobalScope(new \App\Scopes\EventConfirmedScope);
        static::addGlobalScope(new \App\Scopes\EventOrderStartScope);
        static::addGlobalScope(new \App\Scopes\WithParticipantsScope);

    }

    /**
     * Get all the Venues for an Event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function venue()
    {
        return $this->belongsTo('App\Models\Venue');
    }

    public function participants()
    {
        return $this->hasMany('App\Models\Participant');
    }

    public function scopePrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\EventPublicScope::class)->where('public', '=', 0);
    }
    public function scopePublicAndPrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\EventPublicScope::class);
    }

    public function scopeUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\EventConfirmedScope::class)->where('confirmed', '=', 0);
    }
    public function scopeConfirmedAndUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\EventConfirmedScope::class);
    }

    // public function scopeNoVenue($query)
    // {
    //     return $query->withoutGlobalScope(WithVenueScope::class);
    // }

    public function scopeNoParticipants($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\WithParticipantsScope::class);
    }

    public function scopeAtVenue($filter, $venue_id)
    {
        return $filter->where(function ($query) use ($venue_id) {
            $query
            //Start in date range
            ->whereHas('venue', function ($query) use ($venue_id) {
                $query->where('id', '=', $venue_id);
            });
        });
    }

    public function scopeAtVenuename($filter, $venue_name)
    {
        return $filter->where(function ($query) use ($venue_name) {
            $query
            //Start in date range
            ->whereHas('venue', function ($query) use ($venue_name) {
                $query->where('id', 'like', $venue_name);
            });
        });
    }

    public function scopeByParticipant($filter, $page_id)
    {
        return $filter->where(function ($query) use ($page_id) {
            $query
            //Start in date range
            ->whereHas('participant', function ($query) use ($page_id) {
                $query->where('page_id', '=', $page_id);
            });
        });
    }

    public function scopeByParticipantname($filter, $participant_name)
    {
        return $filter->where(function ($query) use ($participant_name) {
            $query
            //Start in date range
            ->whereHas('participant', function ($query) use ($participant_name) {
                $query->where('name', 'like', $participant_name);
            });
        });
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

    public function scopeInDateRange($filter, $date, $enddate)
    {
        return $filter->where(function ($query) use ($date, $enddate) {
            $query
            //Start in date range
            ->whereBetween('start', [$date, $enddate])
            //End in date range
                ->orWhereBetween('end', [$date, $enddate])
            //OR...
                ->orWhere(function ($query) use ($date, $enddate) {
                    $query
                    //Started before this date
                    ->where('start', '<', $date)
                    //AND... it hasnt finished
                        ->where('end', '>', $enddate);

                });

        });
    }

}
