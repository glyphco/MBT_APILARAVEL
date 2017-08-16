<?php
namespace App\Models;

use App\Traits\SpacialdataTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class Event extends Model
{
    use Userstamps;
    use SpacialdataTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',

        'event_id',
        'mve_id',
        'venue_id',
        'venue_name',
        'street_address',
        'city',
        'state',
        'postalcode',
        'lat',
        'lng',
        'local_tz',
        'venue_tagline',

        'price',
        'pricemin',
        'pricemax',
        'pricedescription',
        'pricelink',

        'ages',

        'UTC_start',
        'UTC_end',
        'local_start',
        'local_end',

        'info',
        'private_info',
        'order',

        'public',
        'confirmed',

        'imageurl',
        'backgroundurl',

        'categoriesjson',
        'showsjson',

    ];

    protected $attributes = array(
        'categoriesjson'   => '[]',
        'showsjson'        => '[]',
        'participantsjson' => '[]',
    );

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'location',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        //static::addGlobalScope(new \App\Scopes\WithEventparticipantsPageScope);
        //static::addGlobalScope(new \App\Scopes\WithEventparticipantsScope);
    }

    protected $pyfs;

    /**
     * Get all the Venues for an Event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function mve()
    {
        return $this->belongsTo('App\Models\Mve');
    }

    public function venue()
    {
        return $this->belongsTo('App\Models\Venue');
    }

    public function eventshows()
    {
        return $this->hasMany('App\Models\EventShow');
    }

    public function eventparticipants()
    {
        return $this->hasMany('App\Models\EventParticipant');
    }

    public function eventproducer()
    {
        return $this->hasMany('App\Models\EventProducer');
    }

    public function categories()
    {
        return $this->hasMany('App\Models\EventCategory', 'event_id')->with(['category', 'subcategory']);
    }

    public function scopeCurrent($filter)
    {

        return $filter->where(function ($query) {
            $query
            //Start in date range
            ->whereDate('UTC_start', '>=', Carbon::now()->subHours(3)->toDateTimeString())
            //End in date range
                ->orWhereDate('UTC_end', '>=', Carbon::now()->toDateTimeString());
        });
    }

    public function scopeToday($filter, $tz = 'America/Chicago')
    {

        $datetime = Carbon::now()->subHours(3)->toDateTimeString();
        //get the datetime for your timezone's end of day, add 4 hours, then convert that to utc
        $enddatetime = Carbon::now($tz)->endOfDay()->addHours(4)->setTimezone('UTC')->toDateTimeString();

        //dd($datetime, $enddatetime);
        return $filter->where(function ($query) use ($datetime, $enddatetime) {
            $query
            //Start in date range
            ->whereBetween('UTC_start', [$datetime, $enddatetime])
            //End in date range
                ->orWhereBetween('UTC_end', [$datetime, $enddatetime]);

        });
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

//SEARCHES
    public function scopeByEventParticipant($filter, $page_id)
    {
        return $filter->where(function ($query) use ($page_id) {
            $query->whereHas('eventparticipants', function ($query) use ($page_id) {
                $query->where('page_id', '=', $page_id);
            });

        });
    }
    public function scopeByEventCategory($filter, $category_id)
    {
        return $filter->where(function ($query) use ($category_id) {
            $query->whereHas('categories', function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            });

        });
    }

    public function scopeByEventMyAttending($filter)
    {
        return $filter->where(function ($query) {
            $query->whereHas('attending', function ($query) {
                $query->where('user_id', \Auth::user()->id)
                    ->where('rank', 3);
            });

        });
    }

    public function scopeByEventSubcategory($filter, $subcategory_id)
    {
        return $filter->where(function ($query) use ($subcategory_id) {
            $query->whereHas('categories', function ($query) use ($subcategory_id) {
                $query->where('subcategory_id', $subcategory_id);
            });

        });
    }

//This is WRONG (2nd part) and needs to be fixed
    public function scopeInDateRange($filter, $datetime, $enddatetime)
    {
        return $filter->where(function ($query) use ($datetime, $enddatetime) {
            $query
            //Start in date range
            ->whereBetween('UTC_start', [$datetime, $enddatetime])
            //End in date range
                ->orWhereBetween('UTC_end', [$datetime, $enddatetime])
            //OR...
                ->orWhere(function ($query) use ($datetime, $enddatetime) {
                    $query
                    //Started before this date
                    ->where('UTC_start', '<', $datetime)
                    //AND...
                        ->where(function ($query) use ($datetime, $enddatetime) {
                            $query
                            //Never Finished
                            ->whereNull('UTC_end')
                            //OR Finished after the date range
                                ->orWhere('UTC_end', '>', $enddatetime);
                        });

                });

        });
    }

    // public function attending()
    // {

    //     return $this->belongsToMany('App\Models\User', 'attending', 'event_id')->wherenull('likeables.deleted_at')->select('user_id', 'name', 'avatar');
    // }

    public function iattending()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')->wherePivot('user_id', \Auth::id())->wherePivot('deleted_at', null)->select('user_id', 'name', 'avatar', 'rank');
    }

    public function attendingyes_list()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->where('privacyevents', 2)
            ->wherePivot('rank', 3)
            ->select('user_id', 'name', 'avatar');
    }

    public function attendingwish_list()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->where('privacyevents', 2)
            ->wherePivot('rank', 2)
            ->select('user_id', 'name', 'avatar');
    }

    public function attendingmaybe_list()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->where('privacyevents', 2)
            ->wherePivot('rank', 1)
            ->select('user_id', 'name', 'avatar');
    }

    public function attendingyes()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->wherePivot('rank', 3)
            ->select('user_id');
    }

    public function attendingwish()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->wherePivot('rank', 2)
            ->select('user_id');
    }

    public function attendingmaybe()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->wherePivot('rank', 1)
            ->select('user_id');
    }

    public function pyfsattendingyes_list()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
        // wherePivot('user_id', \Auth::id())->wherePivot('deleted_at', null)->select('user_id', 'name', 'avatar', 'rank');
            ->where('privacyevents', '>', 0)
            ->wherePivot('rank', 3)
            ->wherein('user_id', function ($query) {
                $query->select('following_id')
                    ->from(with(new following)->getTable())
                    ->where('user_id', \Auth::user()->id)
                    ->where('status', 2);
            })
        //->where('privacyevents', '>', 0)
            ->select('user_id', 'name', 'avatar');
    }

    public function pyfsattendingwish_list()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->where('privacyevents', '>', 0)
            ->wherePivot('rank', 2)
            ->wherein('user_id', function ($query) {
                $query->select('following_id')
                    ->from(with(new following)->getTable())
                    ->where('user_id', \Auth::user()->id)
                    ->where('status', 2);
            })->
            select('user_id', 'name', 'avatar');
    }

    public function pyfsattendingmaybe_list()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->where('privacyevents', '>', 0)
            ->wherePivot('rank', 1)
            ->wherein('user_id', function ($query) {
                $query->select('following_id')
                    ->from(with(new following)->getTable())
                    ->where('user_id', \Auth::user()->id)
                    ->where('status', 2);
            })->
            select('user_id', 'name', 'avatar');
    }

    public function pyfsattendingyes()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
        // wherePivot('user_id', \Auth::id())->wherePivot('deleted_at', null)->select('user_id', 'name', 'avatar', 'rank');
            ->wherePivot('rank', 3)
            ->wherein('user_id', function ($query) {
                $query->select('following_id')
                    ->from(with(new following)->getTable())
                    ->where('user_id', \Auth::user()->id)
                    ->where('status', 2);
            })
        //->where('privacyevents', '>', 0)
            ->select('user_id');
    }

    public function pyfsattendingwish()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->wherePivot('rank', 2)
            ->wherein('user_id', function ($query) {
                $query->select('following_id')
                    ->from(with(new following)->getTable())
                    ->where('user_id', \Auth::user()->id)
                    ->where('status', 2);
            })->
            select('user_id');
    }

    public function pyfsattendingmaybe()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->wherePivot('rank', 1)
            ->wherein('user_id', function ($query) {
                $query->select('following_id')
                    ->from(with(new following)->getTable())
                    ->where('user_id', \Auth::user()->id)
                    ->where('status', 2);
            })->
            select('user_id');
    }

}
