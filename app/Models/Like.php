<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Like extends Model
{
    use SoftDeletes;

    protected $table = 'likeables';

    protected $fillable = [
        'user_id',
        'likeable_id',
        'likeable_type',
    ];

    /**
     * Get all of the products that are assigned this like.
     */
    public function pages()
    {
        return $this->morphedByMany('App\Models\Page', 'likeable');
    }

    /**
     * Get all of the posts that are assigned this like.
     */
    public function venues()
    {
        return $this->morphedByMany('App\Models\Venue', 'likeable');
    }
}
