<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class friends extends Model
{
    use SoftDeletes;

    protected $table = 'friends';

    protected $fillable = [
        'user_id',
        'following_id',
        'user_accepted',
        'friend_accepted',
    ];

}
