<?php
namespace App\Http\Controllers;

use App\Http\Controllers as BaseController;

class UserController extends BaseController
{
    use RestControllerTrait;
    use APIResponderTrait;
    const MODEL                = 'App\Models\User';
    protected $validationRules = ['email' => 'required', 'name' => 'required', 'password' => 'required'];

//Eventually lockdown the user model and remove the "REST" controller trait.

    // public function index() {
    //     $m = self::MODEL;
    //     return $this->listResponse($m::all());
    // }

    // public function show($id) {
    //     $m = self::MODEL;
    //     if ($data = $m::find($id)) {
    //         return $this->showResponse($data);
    //     }
    //     return $this->notFoundResponse();
    // }

    // public function update(Request $request, $id) {
    //     $m = self::MODEL;

    //     if (!$data = $m::find($id)) {
    //         return $this->notFoundResponse();
    //     }

    //     try
    //     {
    //         $v = \Illuminate\Support\Facades\Validator::make($request->all(), $this->validationRules);

    //         if ($v->fails()) {
    //             throw new \Exception("ValidationException");
    //         }
    //         $data->fill($request->all());
    //         $data->save();
    //         return $this->showResponse($data);
    //     } catch (\Exception $ex) {
    //         $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
    //         return $this->clientErrorResponse($data);
    //     }
    // }
}
