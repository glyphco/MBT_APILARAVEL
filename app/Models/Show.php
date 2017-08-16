<?php
namespace App\Models;

use App\Scopes\ShowConfirmedScope;
use App\Scopes\ShowPublicScope;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class Show extends Model
{
    use Userstamps;
    //use SpacialdataTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'summary',
        'tagline',
        'slug',
        'imageurl',
        'backgroundurl',
        'categoriesjson',
        'public',
        'confirmed',
    ];

    protected $attributes = array(
        'categoriesjson' => '[]',
    );

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
        //static::addGlobalScope(new \App\Scopes\ShowPublicScope);
        //static::addGlobalScope(new \App\Scopes\ShowConfirmedScope);
    }

    public function events()
    {
        return $this->belongsToMany('App\Models\Event', 'event_shows');
    }

    public function eventslistcurrent()
    {
        return $this->belongsToMany('App\Models\Event', 'event_shows')->current()->where('confirmed', '=', 1)->where('public', '=', 1)->orderby('UTC_start');
    }

    public function scopePrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ShowPublicScope::class)->where('public', '=', 0);
    }

    public function scopePublicAndPrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ShowPublicScope::class);
    }

    public function scopeUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ShowConfirmedScope::class)->where('confirmed', '=', 0);
    }

    public function scopeConfirmedAndUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ShowConfirmedScope::class);
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
