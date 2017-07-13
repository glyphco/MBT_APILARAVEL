<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class TestController extends BaseController
{
    use HasRolesAndAbilities;

    const MODEL                = 'App\Models\Event';
    protected $validationRules = [
        'name'  => 'required',
        'venue' => 'required',
        'start' => 'required',
    ];

}
