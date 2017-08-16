<?php
namespace App\Models\pub;

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

    ];

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

}
