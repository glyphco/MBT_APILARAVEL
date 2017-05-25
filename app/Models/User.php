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
        'name', 'avatar', 'slug', 'confirmed', 'is_banned', 'banned_until', 'last_active_desc', 'last_active', 'is_online', 'remember_token',
        'street_address',
        'city',
        'state',
        'postalcode',
        'lat',
        'lng',
        'location',
        'imageurl',
        'backgroundurl',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
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
}
