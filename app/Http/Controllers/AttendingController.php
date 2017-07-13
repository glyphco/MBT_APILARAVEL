<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Attending;
use Illuminate\Http\Request;

class AttendingController extends BaseController
{
    protected $valid_ranks = [
        0 => 'not attending',
        1 => 'maybe attending',
        2 => 'wish i could',
        3 => 'attending',
    ];

    public function attendEvent(Request $request, $id)
    {

        if (!$request->has('rank') || !array_key_exists($request['rank'], $this->valid_ranks)) {
            return $this->clientErrorResponse('[rank] not found');
        }
        if (!\App\Models\Event::find($id)) {
            return $this->notFoundResponse();
        }

        return $this->showResponse($this->handleAttend($id, $request['rank']));

    }

    public function handleAttend($id, $rank)
    {
        $attending = \App\Models\Attending::updateOrCreate(
            ['event_id' => $id, 'user_id' => \Auth::id()],
            ['rank' => $rank]
        );
        return $this->valid_ranks[$rank];
    }

}
