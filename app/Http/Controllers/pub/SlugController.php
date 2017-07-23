<?php
namespace App\Http\Controllers\pub;

use App\Http\Controllers\Controller as BaseController;

class SlugController extends BaseController
{
    protected $valid_slugs = [
        'user'  => 'App\Models\User',
        'venue' => 'App\Models\Venue',
        'page'  => 'App\Models\Page',
        'show'  => 'App\Models\Show',
    ];

    public function index($slug)
    {
        foreach ($this->valid_slugs as $key => $value) {
            if ($data = $value::where('slug', 'like', $slug)->first()) {
                return $this->showResponse([$key, $data->id]);
            }
        }
        return $this->reasonedNotFoundResponse($slug . ' not found');
    }

}
