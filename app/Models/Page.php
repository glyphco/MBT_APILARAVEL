<?php
namespace App\Models;

use App\Scopes\PageConfirmedScope;
use App\Scopes\PagePublicScope;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class Page extends Model
{
    use Userstamps;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        //'email',
        'slug',
        //'category',
        'description',
        'tagline',
        //'summary',

        'city',
        'state',
        'postalcode',

        //'phone',
        'location',

        'categoriesjson',

        'participant',
        'production',
        'canhavemembers',
        'canbeamember',

        'public',
        'confirmed',
        'imageurl',
        'backgroundurl',
    ];

    protected $attributes = array(
        'categoriesjson' => '[]',
    );

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['summary', 'phone', 'email',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new \App\Scopes\PagePublicScope);
        static::addGlobalScope(new \App\Scopes\PageConfirmedScope);
    }

    public function eventsAsParticipant()
    {
        return $this->belongsToMany('App\Models\Event', 'event_participants', 'page_id', 'event_id')
            ->where('events.confirmed', '=', 1)
            ->where('events.public', '=', 1)
            ->orderby('UTC_start');
    }

    public function eventsAsParticipantCurrent()
    {
        return $this->belongsToMany('App\Models\Event', 'event_participants', 'page_id', 'event_id')
            ->current()
            ->where('events.confirmed', '=', 1)
            ->where('events.public', '=', 1)
            ->orderby('UTC_start');
    }

    public function eventsAsProducer()
    {
        return $this->belongsToMany('App\Models\Event', 'event_producers', 'page_id', 'event_id')
            ->where('events.confirmed', '=', 1)
            ->where('events.public', '=', 1)
            ->orderby('UTC_start');
    }

    public function eventsAsProducerCurrent()
    {
        return $this->belongsToMany('App\Models\Event', 'event_producers', 'page_id', 'event_id')
            ->current()
            ->where('events.confirmed', '=', 1)
            ->where('events.public', '=', 1)
            ->orderby('UTC_start');
    }

    public function categories()
    {
        return $this->hasMany('App\Models\PageCategory')->with(['category', 'subcategory']);
    }

    // public function pagesubcategories()
    // {
    //     return $this->belongsToMany('App\Models\Pagesubcategories', 'page_pagesubcategories', 'page_id', 'pagesubcategory_id');
    // }

    public function scopePrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\PagePublicScope::class)->where('public', '=', 0);
    }

    public function scopePublicAndPrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\PagePublicScope::class);
    }

    public function scopeUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\PageConfirmedScope::class)->where('confirmed', '=', 0);
    }

    public function scopeConfirmedAndUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\PageConfirmedScope::class);
    }

    public function groups()
    {
        return $this->belongsToMany('App\Models\Page', 'groupmembers', 'member_id', 'group_id');
    }

    public function members()
    {
        return $this->belongsToMany('App\Models\Page', 'groupmembers', 'group_id', 'member_id');
    }

    public function likes()
    {
        return $this->morphToMany('App\Models\User', 'likeable')->wherenull('likeables.deleted_at')->select('user_id', 'name', 'avatar');
    }

    public function ilike()
    {
        return $this->morphToMany('App\Models\User', 'likeable')->wherePivot('user_id', \Auth::id())->wherePivot('deleted_at', null)->select('user_id', 'name', 'avatar');
    }

    public function pyfslike()
    {
        return $this->morphToMany('App\Models\User', 'likeable')
            ->wherenull('likeables.deleted_at')
            ->wherePivotIn('user_id', function ($query) {
                $query->select('following_id')
                    ->from('following')
                    ->where('user_id', \Auth::id());
            })
            ->select('user_id', 'name', 'avatar');
    }

    public function getIsLikedAttribute()
    {
        $like = $this->likes()->whereUserId(Auth::id())->first();
        return (!is_null($like)) ? true : false;
    }

}
