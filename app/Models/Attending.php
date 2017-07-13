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
        'event_id',
        'rank',
    ];

    public function event()
    {
        return $this->belongsTo('App\Models\Event')->where('rank', '>', 0);
    }

}
