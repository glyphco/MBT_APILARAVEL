<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagecategory extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function event()
    {
        return $this->belongsTo('App\Models\Event');
    }
    public function currentevents()
    {
        return $this->hasMany('App\Models\Pagesubcategory');
    }
}
