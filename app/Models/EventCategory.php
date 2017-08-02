<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'category_id',
        'subcategory_id',
        'subcategory_name',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * Get all the Venues for an Event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function subcategory()
    {
        return $this->belongsTo('App\Models\Subcategory');
    }

    public function event()
    {
        return $this->belongsTo('App\Models\Event');
    }

}
