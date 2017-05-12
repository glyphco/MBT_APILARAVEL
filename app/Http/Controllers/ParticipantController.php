<?php
namespace App\Http\Controllers;

use App\Traits\APIResponderTrait;
use App\Traits\RestControllerTrait;
use Laravel\Lumen\Routing\Controller as BaseController;

class ParticipantController extends BaseController
{
    use RestControllerTrait;
    use APIResponderTrait;
    const MODEL                = 'App\Models\Event';
    protected $validationRules = [
        'name',
    ];

}
