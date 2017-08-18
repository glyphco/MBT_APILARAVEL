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

}
