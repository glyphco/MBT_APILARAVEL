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
        return $this->belongsTo(Event::class)->where('rank', '>', 0);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User')->where('rank', '>', 0);
    }

}
