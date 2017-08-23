<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

class ErrorController extends BaseController
{

    public function fourohfour(Request $request, $page)
    {
        return $this->reasonedNotFoundResponse($page);
    }

}
