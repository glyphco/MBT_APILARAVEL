<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class EventVenueParticipantController extends BaseController
{
    use HasRolesAndAbilities;
    const MODEL                = 'App\Models\EventVenueParticipant';
    protected $validationRules = [
        'name'           => 'required',
        'event_venue_id' => 'required',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $event_venue_id)
    {
        $m    = self::MODEL;
        $data = $m::where('event_venue_id', $event_venue_id);
        $data = $data->get();

        return $this->listResponse($data);
    }

    public function show(Request $request, $event_venue_id, $id)
    {
        $m = self::MODEL;
        if ($data = $m::where('event_venue_id', $event_venue_id)->find($id)) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $event_venue_id)
    {
        $request['event_venue_id'] = $event_venue_id;
        $m                         = self::MODEL;

// make sure event is there
        $event = \App\Models\Event::find($request['event_venue_id']);
        if (!($event)) {
            // Oops.
            return $this->clientErrorResponse('Could not save: [event_venue_id] not found');
        }

        $page_id = $request->input('page_id', null);

        if ($page_id) {
            $page = \App\Models\Page::find($page_id);
            if (!($page)) {
                // Oops.
                return $this->clientErrorResponse('Could not save: [page_id] not found');
            }

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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $event_venue_id, $id)
    {
        $request['event_venue_id'] = $event_venue_id;
        $m                         = self::MODEL;

        if (!$data = $m::where('event_venue_id', $event_venue_id)->find($id)) {
            return $this->notFoundResponse();
        }

        try
        {
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->validationRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }
            $data->fill($request->all());
            $data->save();
            return $this->showResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $event_venue_id, $id)
    {
        $m = self::MODEL;
        if (!$data = $m::where('event_venue_id', $event_venue_id)->find($id)) {
            return $this->notFoundResponse();
        }
        $data->delete();
        return $this->deletedResponse();
    }

}
