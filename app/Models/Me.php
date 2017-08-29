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

class Me extends Authenticatable
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
        //'name',
        //'avatar',
        //'slug',
        'nighborhood',
        'city',
        'state',
        'postalcode',
        'bio',
        'imageurl',
        'backgroundurl',
        'autoacceptfollows',
        'privacyevents',
        'privacylikes',
        'privacypyf',
    ];

    protected $table = 'users';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'facebook_id',
        'google_id',
        'email',
        'is_online',
        'is_banned',
        'banned_until',
        'updated_at',
        'deleted_at',
        'confirmed',
    ];

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
        return $this->morphedByMany('App\Models\Venues', 'likeable')->whereDeletedAt(null);
    }

    // // pyfs
    // public function following()
    // {
    //     return $this->belongsToMany('App\Models\User', 'following', 'user_id', 'following_id')
    //         ->select('following_id as user_id', 'name', 'avatar');
    // }

    // pyfs
    public function pyfs()
    {
        return $this->belongsToMany('App\Models\User', 'following', 'following_id', 'user_id')
            ->wherePivot('status', 3)
            ->select('user_id as user_id', 'name', 'avatar');

        return $this->belongsToMany('App\Models\User', 'following', 'user_id', 'following_id')
            ->wherePivot('status', 3)
            ->select('following_id as user_id', 'name', 'avatar');
    }

    public function followers()
    {
        return $this->belongsToMany('App\Models\User', 'following', 'following_id', 'user_id')
            ->wherePivot('status', 3)
            ->select('user_id as user_id', 'name', 'avatar');
    }

    public function blocked()
    {
        return $this->belongsToMany('App\Models\User', 'following', 'following_id', 'user_id')
            ->wherePivot('status', 0)
            ->select('user_id as user_id', 'name', 'avatar');
    }

    public function requested()
    {
        return $this->belongsToMany('App\Models\User', 'following', 'following_id', 'user_id')
            ->wherePivot('status', 0)
            ->select('user_id as user_id', 'name', 'avatar');
    }

    //General structure to get a list of users that follow a person
    // must use with with:
    //
    // \App\Models\Me::whereHas('pfm', function ($query) {
    //        $query->where('following_id', \Auth::id());
    //    })
    //
    //to limit the results to a user

    public function usersfollowingusers()
    {
        return $this->belongsToMany('App\Models\User', 'following', 'user_id', 'following_id');
    }

    //General structure to get a list of users that a person follows
    // must use with with:
    //
    // $data = \App\Models\Me::whereHas('usersfollowingusersreverse', function ($query) {
    //     $query->where('user_id', \Auth::id())
    //         ->where('status', 3);
    // });
    //
    //to limit the results to a user

    public function usersfollowingusersreverse()
    {
        return $this->belongsToMany('App\Models\User', 'following', 'following_id', 'user_id');
    }
}
