<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class PageShowController extends BaseController
{
    use HasRolesAndAbilities;
    const MODEL                = 'App\Models\Show';
    protected $validationRules = [
        'name' => 'required',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        $m    = self::MODEL;
        $data = $m::where('event_id', $id);

        // $fields         = $this->fields
        //     ->whereHas('form', function ($q) use ($currentcompany, $id) {
        //         $q->where('id', $id)->where('company_id', $currentcompany);
        //     }
        //     )
        //     ->orderBy('order')->get();

        // if ($request->exists('Unconfirmed')) {
        //     $data = $data->Unconfirmed();
        // }
        // if ($request->exists('ConfirmedAndUnconfirmed')) {
        //     $data = $data->ConfirmedAndUnconfirmed();
        // }
        // if ($request->exists('Private')) {
        //     $data = $data->Private();
        // }
        // if ($request->exists('PublicAndPrivate')) {
        //     $data = $data->PublicAndPrivate();
        // }
        // $data = $data->get();
        $data = $data->get();
        dd($data->toArray());
        return $data->listResponse($data);
    }

}
