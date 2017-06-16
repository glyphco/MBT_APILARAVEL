<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class SubcategoryController extends BaseController
{
    use HasRolesAndAbilities;
    const MODEL                = 'App\Models\Subcategory';
    protected $validationRules = [
        'name' => 'required',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $m = self::MODEL;

        if ($request->exists('category_id')) {
            $data = $m::where('category_id', $category_id)->get();
        } else {
            $data = $data->get();
        }

        return $this->listResponse($data);
    }

    public function show(Request $request, $id)
    {
        $m = self::MODEL;
        if ($data = $m::find($id)) {
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
    public function store(Request $request)
    {

// make sure event is there
        $category_id = $request->input('category_id', null);
        $category    = \App\Models\Pagecategory::find($category_id);
        if (!($category)) {
            // Oops.
            return $this->clientErrorResponse('Could not save: [category_id] not found');
        }

        $m = self::MODEL;

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
    public function update(Request $request, $id)
    {
        $request['event_id'] = $event_id;
        $m                   = self::MODEL;

        $category_id = $request->input('category_id', null);
        $category    = \App\Models\Pagecategory::find($category_id);
        if (!($category)) {
            // Oops.
            return $this->clientErrorResponse('Could not save: [category_id] not found');
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
    public function destroy(Request $request, $id)
    {
        $m = self::MODEL;
        if (!$data = $m::find($id)) {
            return $this->notFoundResponse();
        }
        $data->delete();
        return $this->deletedResponse();
    }

}
