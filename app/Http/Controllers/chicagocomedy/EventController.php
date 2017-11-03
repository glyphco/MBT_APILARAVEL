<?php
namespace App\Http\Controllers\chicagocomedy;

use App\Http\Controllers\Controller as BaseController;
use App\Traits\ItemConfirmableTrait;
use App\Traits\ItemHasAdminsTrait;
use App\Traits\ItemHasEditorsTrait;
use App\Traits\ItemPrivateableTrait;
use Carbon\Carbon;
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

        // $pp = $request->input('pp', 25);
        // if ($pp > 100) {$pp = 100;}
        // $data = $data->paginate($pp);
        // $data = $this->addEventExtras($data);

        // return $this->listResponse($data);

        $data = $data->get();

        $data = $this->addEventExtras($data);

        return $this->pureResponse($data);

    }

    private function addEventExtras($data)
    {

        foreach ($data as $item) {
            $item->offsetSet('imageIcon', '');
            $item->offsetSet('imageSm', '');
            $item->offsetSet('imageLg', '');
            $item->offsetSet('venueimageIcon', '');
            $item->offsetSet('venueimageSm', '');
            $item->offsetSet('venueimageLg', '');
            $item->offsetSet('agesWord', '');
            $item->offsetSet('agesIcon', '');
            $item->offsetSet('localstarttime', '');
            $item->offsetSet('localendtime', '');
            $item->offsetSet('localtimes', '');
            $item->offsetSet('localstartdate', '');
            $item->offsetSet('localenddate', '');
            $item->offsetSet('priceWord', '');
            $item->offsetSet('priceMinMax', '');

            if (isset($item->imageurl)) {
                $item->offsetSet('imageIcon', preg_replace('/\.[^.]+$/', '', $item->imageurl) . "_icon.jpg");
                $item->offsetSet('imageSm', preg_replace('/\.[^.]+$/', '', $item->imageurl) . "_sm.jpg");
                $item->offsetSet('imageLg', preg_replace('/\.[^.]+$/', '', $item->imageurl) . "_lg.jpg");
            }

            if (isset($item->venueimageurl)) {
                $item->offsetSet('venueimageIcon', preg_replace('/\.[^.]+$/', '', $item->venueimageurl) . "_icon.jpg");
                $item->offsetSet('venueimageSm', preg_replace('/\.[^.]+$/', '', $item->venueimageurl) . "_sm.jpg");
                $item->offsetSet('venueimageLg', preg_replace('/\.[^.]+$/', '', $item->venueimageurl) . "_lg.jpg");
            }

            if (isset($item->ages)) {
                switch ($item->ages) {
                    case 0:
                        $item->offsetSet('agesWord', '');
                        $item->offsetSet('agesIcon', '');
                        break;
                    case 1:
                        $item->offsetSet('agesWord', 'family');
                        //This didnt work :(
                        $item->offsetSet('agesIcon', '<span class=\"fa-stack\"><i class=\"fa fa-male fa-stack-1x\" style="left: -4px;\"></i><i class=\"fa fa-male fa-stack-1x\" style="font-size: .75em; left: 2px;top: 2px;\"></i><i class=\"fa fa-female fa-stack-1x\" style="left: 8px;\"></i></span>');
                        $item->offsetSet('agesIcon', '');
                        break;
                    case 2:
                        $item->offsetSet('agesWord', 'all ages');
                        $item->offsetSet('agesIcon', '');
                        break;
                    case 3:
                        $item->offsetSet('agesWord', '18+');
                        $item->offsetSet('agesIcon', '');
                        break;
                    case 4:
                        $item->offsetSet('agesWord', '21+');
                        $item->offsetSet('agesIcon', '');
                        break;
                    default:
                        $item->offsetSet('agesWord', '');
                        $item->offsetSet('agesIcon', '');
                }
            }

            if (isset($item->price)) {
                switch ($item->price) {
                    case 0:
                        $item->offsetSet('priceWord', '');
                        break;
                    case 1:
                        $item->offsetSet('priceWord', 'free');
                        break;
                    case 2:
                        $item->offsetSet('priceWord', 'donation');
                        break;
                    case 3:
                        $item->offsetSet('priceWord', 'sliding');
                        break;
                    default:
                        $item->offsetSet('priceWord', '');
                }
            }

            if ((isset($item->pricemin)) && (isset($item->pricemax)) && ($item->pricemin > $item->pricemax)) {
                //swap 'em
                $tmp = $item->pricemin;
                $item->offsetSet('pricemin', $item->pricemax);
                $item->offsetSet('pricemax', $tmp);
            }

            if (isset($item->pricemin)) {
                $item->offsetSet('priceMinMax', '$' . $item->pricemin);
                if ((isset($item->pricemax)) && ($item->pricemax != $item->pricemin) && ($item->pricemax != 0)) {
                    $item->offsetSet('priceMinMax', $item->priceMinMax . ' - $' . $item->pricemax);
                }
            }
            if ((isset($item->pricemin)) && (isset($item->pricemax)) && ($item->pricemin == 0) && ($item->pricemax == 0)) {
                $item->offsetSet('priceMinMax', '');
            }

            if (isset($item->local_start)) {
                $carbontime = new Carbon($item->local_start, $item->local_tz);
                //dd($carbontime->minute);
                if ($carbontime->minute == 0) {
                    $item->offsetSet('localstarttime', $carbontime->format('ga'));
                } else {
                    $item->offsetSet('localstarttime', $carbontime->format('g:ia'));
                }
                $item->offsetSet('localtimes', $item->localstarttime);
                $item->offsetSet('localstartdate', $carbontime->format('D M j'));
            }

            if (isset($item->local_end)) {
                $carbontime = new Carbon($item->local_end, $item->local_tz);
                //dd($carbontime->minute);
                if ($carbontime->minute == 0) {
                    $item->offsetSet('localendtime', $carbontime->format('ga'));
                } else {
                    $item->offsetSet('localendtime', $carbontime->format('g:ia'));
                }
                $item->offsetSet('localtimes', $item->localtimes . '-' . $item->localendtime);
                $item->offsetSet('localenddate', $carbontime->format('D M j'));
            }

        }
        return $data;
    }

}
