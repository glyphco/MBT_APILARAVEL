<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attending extends Model
{
    use SoftDeletes;

    protected $table = 'attending';

    protected $fillable = [
        'user_id',
        'eventvenue_id',
        'rank',
    ];

    public function eventvenue()
    {
        return $this->belongsTo('App\Models\EventVenue')->where('rank', '>', 0);
    }

}
