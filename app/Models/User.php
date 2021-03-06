<?php
namespace App\Models;

use App\Traits\SpacialdataTrait;
// use Illuminate\Auth\Authenticatable;
// use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
// use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
// use Illuminate\Database\Eloquent\Model;
// use Laravel\Lumen\Auth\Authorizable;

// //use Illuminate\Foundation\Auth\User as Authenticatable;
// //use Illuminate\Notifications\Notifiable;

// use Tymon\JWTAuth\Contracts\JWTSubject as AuthenticatableUserContract;

// //use Wildside\Userstamps\Userstamps;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class User extends Authenticatable
//extends Model
// implements
// AuthenticatableContract,
// AuthenticatableUserContract,
// AuthorizableContract

{

    //use Authenticatable, Authorizable, HasRolesAndAbilities, SpacialdataTrait;
    use HasRolesAndAbilities, Notifiable, SpacialdataTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'avatar',
        'imageurl',
        'backgroundurl',
        'slug',
        'bio',
        'confirmed',
        'autoacceptfollows',
        'is_banned',
        'banned_until',
        'last_active_desc',
        'last_active',
        'is_online',
        'remember_token',
        'neighborhood',
        'city',
        'state',
        'postalcode',
        'email_token',
        'imageurl',
        'backgroundurl',
        'privacyevents',
        'privacylikes',
        'privacypyf',

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new \App\Scopes\UserConfirmedScope);
        static::addGlobalScope(new \App\Scopes\UserNotBannedScope);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $ability
     */
    public function scopeWhereCan($query, $ability)
    {
        $query->where(function ($query) use ($ability) {
            // direct
            $query->whereHas('abilities', function ($query) use ($ability) {
                $query->byName($ability);
            });
            // through roles
            $query->orWhereHas('roles', function ($query) use ($ability) {
                $query->whereHas('abilities', function ($query) use ($ability) {
                    $query->byName($ability);
                });
            });
        });
    }

    public function likedPages()
    {
        return $this->morphedByMany('App\Models\Page', 'likeable');
    }

    public function likedVenues()
    {
        return $this->morphedByMany('App\Models\Venue', 'likeable')->whereDeletedAt(null);
    }

    public function likedShows()
    {
        return $this->morphedByMany('App\Models\Show', 'likeable')->whereDeletedAt(null);
    }

    public function eventsImAttending()
    {
        return $this->belongsToMany('App\Models\Event', 'attending', 'user_id')->current()->wherePivot('user_id', \Auth::id());
    }

    public function scopeUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\UserConfirmedScope::class)->where('confirmed', '=', 0);
    }

    public function scopeConfirmedAndUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\UserConfirmedScope::class);
    }

    public function scopeBanned($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\UserNotBannedScope::class)->where('banned', '=', 1);
    }

    public function scopeBannedAndNotBanned($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\UserNotBannedScope::class);
    }

    // pyfs
    public function pyf()
    {
        return $this->belongsToMany('App\Models\User', 'following', 'user_id', 'following_id')
            ->wherePivot('status', 3)
            ->select('following_id as user_id', 'name', 'avatar');
    }

    // pyfs
    public function followers()
    {
        return $this->belongsToMany('App\Models\User', 'following', 'following_id', 'user_id')
            ->wherePivot('status', 3)
            ->select('user_id as user_id', 'name', 'avatar');
    }

    public function following()
    {
        return $this->hasMany('App\Models\Following');
    }

}
