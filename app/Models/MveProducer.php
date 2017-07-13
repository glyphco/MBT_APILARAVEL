<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class MveProducer extends Model
{
    use Userstamps;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'name',
        'details',
        'page_id',
        'order',
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

    public function mve()
    {
        return $this->belongsTo('App\Models\Mve');
    }

    public function page()
    {
        return $this->belongsTo('App\Models\Page');
    }

}
