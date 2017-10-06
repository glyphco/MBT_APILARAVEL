<?php
namespace App\Http\Controllers\chicagocomedy;

use App\Http\Controllers\Controller as BaseController;
use App\Traits\ItemConfirmableTrait;
use App\Traits\ItemHasAdminsTrait;
use App\Traits\ItemHasEditorsTrait;
use App\Traits\ItemPrivateableTrait;
use Illuminate\Http\Request;

class EventController extends BaseController
{

    use ItemConfirmableTrait, ItemPrivateableTrait, ItemHasAdminsTrait, ItemHasEditorsTrait;

    const MODEL                = 'App\Models\Event';
    protected $validationRules = [
        'name'        => 'required',
        'UTC_start'   => 'required',
        'local_start' => 'required',
        'local_tz'    => 'required',
    ];
    protected $pyfs;

    protected $createitems  = 'create-events';
    protected $adminitems   = 'admin-events';
    protected $edititems    = 'edit-events';
    protected $confirmitems = 'confirm-events';

    public function index(Request $request)
    {

        $lat  = "41.818408221760095";
        $lng  = "-87.81646728515625";
        $dist = "38624.159999999996";
        $tz   = "America/Chicago";

        if ($request->has('tz') && $this->isValidTimezoneId($request->input('tz'))) {
            $tz = $request->input('tz');
        }

        if ($request->has('lat') && $this->isValidLatitude($request->input('lat'))) {
            $lat = $request->input('lat');
        }

        if ($request->has('lng') && $this->isValidLongitude($request->input('lng'))) {
            $lng = $request->input('lng');
        }

        if ($request->has('dist')) {
            $dist = $request->input('dist');
        }

        $m    = self::MODEL;
        $data = $m::current(); //tz unneeded for current
        $data = $data->where('confirmed', '=', 1);
        $data = $data->where('public', '=', 1);
        $data = $data->near($lat, $lng, $dist, 'METERS');
        $data = $data->orderBy('UTC_start', 'asc');

        $pp = $request->input('pp', 25);
        if ($pp > 100) {$pp = 100;}
        $data = $data->paginate($pp);

        return $this->listResponse($data);

    }
}
