<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eventrole extends Model
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

    public function pages()
    {
        return $this->hasMany('App\Models\Page', 'page_eventroles');
    }
}
