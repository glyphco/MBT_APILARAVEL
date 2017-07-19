<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Bouncer;
use Illuminate\Http\Request;

class EventShowController extends BaseController
{

    const MODEL                = 'App\Models\EventShow';
    protected $validationRules = [
        'showpage_id' => 'required',
    ];

    public function index(Request $request, $event_id)
    {

        $m    = self::MODEL;
        $data = $m::where('event_id', $event_id)->with(['showpage']);
        $data = $data->get();

        return $this->listResponse($data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $event_id)
    {

        if (!($event = \App\Models\Event::find($event_id))) {
            return $this->notFoundResponse();
        }
        if (!((Bouncer::allows('edit-events')) or (Bouncer::allows('edit', $event)))) {
            return $this->unauthorizedResponse();
        }

        $m = self::MODEL;

        $showpage_id         = $request->input('showpage_id', null);
        $request['event_id'] = $event_id;
        if (!\App\Models\Event::find($event_id)) {
            return $this->clientErrorResponse('Could not save: [event_id] not found');
        }
        if (!$showpage_id) {
            return $this->clientErrorResponse('Could not save: [showpage_id] not found');
        }

        if (!\App\Models\Showpage::find($showpage_id)) {
            return $this->clientErrorResponse('Could not save: [showpage_id] not found');
        }

        try
        {
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->validationRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }

            $data = $m::create($request->all());
            return $this->createdResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }

    public function destroy($event_id, $id)
    {
        if (!($event = \App\Models\Event::find($event_id))) {
            return $this->notFoundResponse();
        }

        if (!((Bouncer::allows('edit-events')) or (Bouncer::allows('edit', $event)))) {
            return $this->unauthorizedResponse();
        }

        $m = self::MODEL;

        if (!$data = $m::where('event_id', $event_id)->find($id)) {
            return $this->notFoundResponse();
        }
        $data->delete();
        return $this->deletedResponse();
    }

}
