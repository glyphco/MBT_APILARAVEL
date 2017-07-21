<?php
namespace App\Http\Controllers\pub;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

class GeneralController extends BaseController
{

    public function getVer(Request $request)
    {
        return app()->version();
    }

}
