<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class following extends Model
{
    use SoftDeletes;

    protected $table = 'following';

    protected $fillable = [
        'user_id',
        'following_id',
        'status',
    ];

    public function users()
    {
        return $this->hasMany('App\Models\User', 'id', 'user_id')->select('id', 'name', 'avatar');
    }

    public function followers()
    {
        return $this->hasMany('App\Models\User', 'id', 'following_id')->select('id', 'name', 'avatar');
    }
}
