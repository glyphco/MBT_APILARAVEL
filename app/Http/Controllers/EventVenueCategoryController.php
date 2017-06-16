<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class EventVenueCategoryController extends BaseController
{
    use HasRolesAndAbilities;

    const MODEL                = 'App\Models\EventVenueCategory';
    protected $validationRules = [
        'category_id' => 'required',
    ];

    public function index(Request $request, $eventvenue_id)
    {

        $m    = self::MODEL;
        $data = $m::where('eventvenue_id', $eventvenue_id)->with(['category', 'subcategory']);
        $data = $data->get();

        return $this->listResponse($data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $eventvenue_id)
    {
        $m = self::MODEL;

        $category_id              = $request->input('category_id', null);
        $subcategory_id           = $request->input('subcategory_id', null);
        $request['eventvenue_id'] = $eventvenue_id;
        if (!\App\Models\EventVenue::find($eventvenue_id)) {
            return $this->clientErrorResponse('Could not save: [eventvenue_id] not found');
        }
        if (!$category_id) {
            return $this->clientErrorResponse('Could not save: [category_id] not found');
        }

        if (!\App\Models\Category::find($category_id)) {
            return $this->clientErrorResponse('Could not save: [category_id] not found');
        }

        if ($subcategory_id) {
            if (!\App\Models\Subcategory::where('category_id', $category_id)->find($subcategory_id)) {
                return $this->clientErrorResponse('Could not save: [category_id] [subcategory_id] pair not found');
            }
        }

        try
        {
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->validationRules);

            if ($v->fails()) {
                throw new \Exception("ValidationException");
            }
            $request->request->add(['location' => implode(', ', $request->only('lat', 'lng'))]);
            $data = $m::create($request->all());
            return $this->createdResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }

    public function destroy($id)
    {
        $m = self::MODEL;
        if (!$data = $m::find($id)) {
            return $this->notFoundResponse();
        }
        $data->delete();
        return $this->deletedResponse();
    }

}
