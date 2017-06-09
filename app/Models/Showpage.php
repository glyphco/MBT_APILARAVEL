<?php
namespace App\Models;

use App\Scopes\ShowpageConfirmedScope;
use App\Scopes\ShowpagePublicScope;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class Showpage extends Model
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
        'category',
        'tagline',
        'slug',
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
        static::addGlobalScope(new \App\Scopes\ShowpagePublicScope);
        static::addGlobalScope(new \App\Scopes\ShowpageConfirmedScope);
    }

    public function events()
    {
        return $this->belongsToMany('App\Models\Event', 'event_shows');
    }

    // public function showpagesubcategories()
    // {
    //     return $this->belongsToMany('App\Models\Showpagesubcategories', 'showpage_showpagesubcategories', 'showpage_id', 'showpagesubcategory_id');
    // }

    public function scopePrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ShowpagePublicScope::class)->where('public', '=', 0);
    }

    public function scopePublicAndPrivate($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ShowpagePublicScope::class);
    }

    public function scopeUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ShowpageConfirmedScope::class)->where('confirmed', '=', 0);
    }

    public function scopeConfirmedAndUnconfirmed($query)
    {
        return $query->withoutGlobalScope(\App\Scopes\ShowpageConfirmedScope::class);
    }

    public function likes()
    {
        return $this->morphToMany('App\Models\User', 'likeable')->wherenull('likeables.deleted_at')->select('user_id', 'name', 'avatar');
    }

    public function getIsLikedAttribute()
    {
        $like = $this->likes()->whereUserId(Auth::id())->first();
        return (!is_null($like)) ? true : false;
    }
}
