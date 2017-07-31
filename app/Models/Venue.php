<?php
namespace App\Models;

use App\Scopes\VenueConfirmedScope;
use App\Scopes\VenuePublicScope;
use App\Traits\SpacialdataTrait;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class Venue extends Model
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
        'slug',
        'category',
        'street_address',
        'city',
        'state',
        'postalcode',
        'lat',
        'lng',
        'local_tz',

        'neighborhood',
        'website',
        'tagline',
        'description',
        'phone',
        'email',
        'location',
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
        static::addGlobalScope(new \App\Scopes\VenuePublicScope);
        static::addGlobalScope(new \App\Scopes\VenueConfirmedScope);
    }

    public function events()
    {
        return $this->hasMany('App\Models\Event');
    }

    public function currentevents()
    {
        return $this->hasMany('App\Models\Event')->Current();
    }

    public function scopePrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\VenuePublicScope::class)->where('public', '=', 0);
    }
    public function scopePublicAndPrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\VenuePublicScope::class);
    }

    public function scopeUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\VenueConfirmedScope::class)->where('confirmed', '=', 0);
    }

    public function scopeConfirmedAndUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\VenueConfirmedScope::class);
    }

    public function likes()
    {
        return $this->morphToMany('App\Models\User', 'likeable')->wherenull('likeables.deleted_at')->select('user_id', 'name', 'avatar');
    }

    public function ilike()
    {
        return $this->morphToMany('App\Models\User', 'likeable')->wherePivot('user_id', \Auth::id())->wherePivot('deleted_at', null)->select('user_id', 'name', 'avatar');
    }

    public function getIsLikedAttribute()
    {
        $like = $this->likes()->whereUserId(\Auth::id())->first();
        return (!is_null($like)) ? true : false;
    }

    public function scopeWithMyLikes($query)
    {
        if (!$this->likes) {
            return $query;
        }
        return $query->with(['likes' => function ($query) {
            $query->where('user_id', \Auth::id());
        }]);

    }

}
