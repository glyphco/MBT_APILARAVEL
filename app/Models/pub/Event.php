<?php
namespace App\Models\pub;

use App\Traits\SpacialdataTrait;
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
        static::addGlobalScope(new \App\Scopes\WithVenueScope);
        static::addGlobalScope(new \App\Scopes\WithEventparticipantsPageScope);
        static::addGlobalScope(new \App\Scopes\WithEventproducersPageScope);
        static::addGlobalScope(new \App\Scopes\WithEventshowsShowScope);
        static::addGlobalScope(new \App\Scopes\WithEventcategoriesCategoriesScope);

        static::addGlobalScope(new \App\Scopes\EventPublicScope);
        static::addGlobalScope(new \App\Scopes\EventConfirmedScope);
        static::addGlobalScope(new \App\Scopes\EventCurrentScope);
    }

    protected $friends;

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

    public function eventproducers()
    {
        return $this->hasMany('App\Models\EventProducer');
    }

    public function categories()
    {
        return $this->hasMany('App\Models\EventCategory', 'event_id')->with(['category', 'subcategory']);
    }

    // public function scopeCurrent($filter)
    // {

    //     return $filter->where(function ($query) {
    //         $query
    //         //Start in date range
    //         ->whereDate('UTC_start', '>=', Carbon::now()->subHours(5)->toDateTimeString())
    //         //End in date range
    //             ->orWhereDate('UTC_end', '>=', Carbon::now()->subHours(5)->toDateTimeString());
    //     });
    // }

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

    public function attending()
    {

        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')->select('user_id', 'name', 'avatar');
    }

    public function attendingyes()
    {

        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->where('rank', 3)
            ->select('user_id', 'name', 'avatar');
    }

    public function attendingwish()
    {

        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')->where('rank', 2)
            ->select('user_id', 'name', 'avatar');
    }

    public function attendingmaybe()
    {

        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')->where('rank', 1)
            ->select('user_id', 'name', 'avatar');
    }

    public function friendsattending()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->wherePivotIn('user_id', $this->friends)
            ->select('user_id', 'name', 'avatar');
    }

    public function friendsattendingyes()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->where('rank', 3)
            ->wherein('user_id', function ($query) {
                $query->select('friend_id')
                    ->from(with(new friendships)->getTable())
                    ->where('user_id', \Auth::user()->id);
            })->
            select('user_id', 'name', 'avatar');
    }

    public function friendsattendingwish()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->where('rank', 2)
            ->wherein('user_id', function ($query) {
                $query->select('friend_id')
                    ->from(with(new friendships)->getTable())
                    ->where('user_id', \Auth::user()->id);
            })->
            select('user_id', 'name', 'avatar');
    }

    public function friendsattendingmaybe()
    {
        return $this->belongsToMany('App\Models\User', 'attending', 'event_id')
            ->where('rank', 1)
            ->wherein('user_id', function ($query) {
                $query->select('friend_id')
                    ->from(with(new friendships)->getTable())
                    ->where('user_id', \Auth::user()->id);
            })->
            select('user_id', 'name', 'avatar');
    }

}
