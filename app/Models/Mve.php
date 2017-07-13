<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class Mve extends Model
{
    use Userstamps;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
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
        // static::addGlobalScope(new \App\Scopes\EventPublicScope);
        // static::addGlobalScope(new \App\Scopes\EventConfirmedScope);
        //        static::addGlobalScope(new \App\Scopes\EventOrderStartScope);
        //        static::addGlobalScope(new \App\Scopes\WithEventProducersScope);
        //        static::addGlobalScope(new \App\Scopes\WithEventShowsScope);
        //        static::addGlobalScope(new \App\Scopes\WithEventVenuesScope);
    }

    /**
     * Get all the Venues for an Event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventvenues()
    {
        return $this->hasMany('App\Models\EventVenue');
    }

    // public function participants()
    // {
    //     return $this->hasMany('App\Models\Participant');
    // }

    public function mveproducers()
    {
        return $this->hasMany('App\Models\MveProducer');
    }

    // public function eventshows()
    // {
    //     return $this->hasMany('App\Models\Eventshow');
    // }

    // public function scopePrivate($query)
    // {
    //     return $query->withoutGlobalScope(\App\Scopes\EventPublicScope::class)->where('public', '=', 0);
    // }
    // public function scopePublicAndPrivate($query)
    // {
    //     return $query->withoutGlobalScope(\App\Scopes\EventPublicScope::class);
    // }

    // public function scopeUnconfirmed($query)
    // {
    //     return $query->withoutGlobalScope(\App\Scopes\EventConfirmedScope::class)->where('confirmed', '=', 0);
    // }
    // public function scopeConfirmedAndUnconfirmed($query)
    // {
    //     return $query->withoutGlobalScope(\App\Scopes\EventConfirmedScope::class);
    // }

    // // public function scopeNoVenue($query)
    // // {
    // //     return $query->withoutGlobalScope(WithVenueScope::class);
    // // }

    // public function scopeNoParticipants($query)
    // {
    //     return $query->withoutGlobalScope(\App\Scopes\WithEventVenueParticipantsScope::class);
    // }

    // public function scopeAtEventVenue($filter, $venue_id)
    // {
    //     return $filter->where(function ($query) use ($venue_id) {
    //         $query
    //         //Start in date range
    //         ->whereHas('venue', function ($query) use ($venue_id) {
    //             $query->where('id', '=', $venue_id);
    //         });
    //     });
    // }

    // public function scopeAtEventVenuename($filter, $venue_name)
    // {
    //     return $filter->where(function ($query) use ($venue_name) {
    //         $query
    //         //Start in date range
    //         ->whereHas('venue', function ($query) use ($venue_name) {
    //             $query->where('id', 'like', $venue_name);
    //         });
    //     });
    // }

    // public function scopeByEventVenueParticipant($filter, $page_id)
    // {
    //     return $filter->where(function ($query) use ($page_id) {
    //         $query
    //         //Start in date range
    //         ->whereHas('eventvenues', function ($query) use ($page_id) {
    //             $query->whereHas('eventvenueparticipants', function ($query) use ($page_id) {
    //                 $query->where('page_id', '=', $page_id);
    //             });
    //         });
    //     });
    // }

    // public function scopeByEventVenueParticipantname($filter, $participant_name)
    // {
    //     return $filter->where(function ($query) use ($participant_name) {
    //         $query
    //         //Start in date range
    //         ->whereHas('eventvenues', function ($query) use ($participant_name) {
    //             $query->whereHas('eventvenueparticipants', function ($query) use ($participant_name) {
    //                 $query->where('name', 'like', $participant_name);
    //             });
    //         });
    //     });
    // }

    // public function scopeCurrent($filter)
    // {

    //     return $filter->where(function ($query) {
    //         $query
    //         //Start in date range
    //         ->whereDate('start', '>=', Carbon::now()->subHours(5)->toDateString())
    //         //End in date range
    //             ->orWhereDate('end', '>=', Carbon::now()->subHours(5)->toDateString());
    //     });
    // }

    // public function scopeInDateRange($filter, $date, $enddate)
    // {
    //     return $filter->where(function ($query) use ($date, $enddate) {
    //         $query
    //         //Start in date range
    //         ->whereBetween('start', [$date, $enddate])
    //         //End in date range
    //             ->orWhereBetween('end', [$date, $enddate])
    //         //OR...
    //             ->orWhere(function ($query) use ($date, $enddate) {
    //                 $query
    //                 //Started before this date
    //                 ->where('start', '<', $date)
    //                 //AND... it hasnt finished
    //                     ->where('end', '>', $enddate);

    //             });

    //     });
    // }

}
