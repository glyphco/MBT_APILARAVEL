<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class friendships extends Model
{
    use SoftDeletes;

    protected $table = 'friendships';

    protected $fillable = [
        'user_id',
        'friend_id',
    ];

}
