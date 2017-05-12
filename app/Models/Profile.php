<?php
namespace App\Models;

use App\Scopes\ProfileConfirmedScope;
use App\Scopes\ProfilePublicScope;
use App\Traits\SpacialdataTrait;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class Profile extends Model
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
        'email',
        'slug',
        'category',
        'street_address',
        'city',
        'state',
        'postalcode',
        'lat',
        'lng',
        'phone',
        'location',
        'participant',
        'production',
        'canhavemembers',
        'canbeamember',
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
        static::addGlobalScope(new \App\Scopes\ProfilePublicScope);
        static::addGlobalScope(new \App\Scopes\ProfileConfirmedScope);
    }

    public function events()
    {
        //return $this->hasMany('App\Models\Event');
        return $this->hasManyThrough(
            'App\Models\Event', 'App\Models\participant',
            'profile_id', 'id'
        );
    }

    public function scopePrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ProfilePublicScope::class)->where('public', '=', 0);
    }
    public function scopePublicAndPrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ProfilePublicScope::class);
    }

    public function scopeUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ProfileConfirmedScope::class)->where('confirmed', '=', 0);
    }

    public function scopeConfirmedAndUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ProfileConfirmedScope::class);
    }

    public function groups()
    {
        return $this->belongsToMany('App\Models\Profile', 'groupmembers', 'member_id', 'group_id');
    }

    public function members()
    {
        return $this->belongsToMany('App\Models\Profile', 'groupmembers', 'group_id', 'member_id');
    }

}
