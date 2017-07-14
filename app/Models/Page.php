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
        'email',
        'slug',
        'category',

        'city',
        'state',
        'postalcode',

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
        static::addGlobalScope(new \App\Scopes\PagePublicScope);
        static::addGlobalScope(new \App\Scopes\PageConfirmedScope);
    }

    public function events()
    {

        return $this->hasManyThrough(
            'App\Models\Event', 'App\Models\eventparticipant',
            'page_id', 'id'
        );
    }

    public function pagesubcategories()
    {
        return $this->belongsToMany('App\Models\Pagesubcategories', 'page_pagesubcategories', 'page_id', 'pagesubcategory_id');
    }

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

    public function getIsLikedAttribute()
    {
        $like = $this->likes()->whereUserId(Auth::id())->first();
        return (!is_null($like)) ? true : false;
    }

    public function categories()
    {
        return $this->hasMany('App\Models\PageCategory')->with(['category', 'subcategory']);
    }

}
