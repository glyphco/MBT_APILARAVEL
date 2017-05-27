<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Bouncer;
use Illuminate\Http\Request;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class UserController extends BaseController
{

    use HasRolesAndAbilities;

    const MODEL                = 'App\Models\User';
    protected $validationRules = ['email' => 'required', 'name' => 'required', 'password' => 'required'];

    public function index(Request $request)
    {
        //$request = Request::all();
        $m = self::MODEL;
        return $this->listResponse($m::all());

    }

    public function show($id)
    {
        $m = self::MODEL;
        if ($data = $m::find($id)) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();
    }

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

    public function makesuperadmin($id)
    {
        $m = self::MODEL;
        if (!$user = $m::find($id)) {
            return $this->notFoundResponse();
        }

        if (\Auth::user()->facebook_id == env('glyph_facebook', 0)) {
            Bouncer::assign('superadmin')->to($user);
        } else {
            return $this->reasonedUnauthorizedResponse('nice try dickwad');
        }

        return $this->showResponse('user is now superadmin');

    }

    public function makeadmin($id)
    {
        $m = self::MODEL;
        if (!$user = $m::find($id)) {
            return $this->notFoundResponse();
        }
        if (Bouncer::is($user)->a('superadmin')) {
            if (\Auth::user()->facebook_id == env('glyph_facebook', 0)) {
                Bouncer::retract('superadmin')->from($user);
            } else {
                return $this->reasonedUnauthorizedResponse('you cant unthrone a king');
            }
        }
        Bouncer::assign('admin')->to($user);
        return $this->showResponse('user is now admin');
    }

    public function makemastereditor($id)
    {
        $m = self::MODEL;
        if (!$user = $m::find($id)) {
            return $this->notFoundResponse();
        }
        if (Bouncer::is($user)->a('superadmin')) {
            if (\Auth::user()->facebook_id == env('glyph_facebook', 0)) {
                Bouncer::retract('superadmin')->from($user);
            } else {
                return $this->reasonedUnauthorizedResponse('you cant unthrone a king');
            }
        }

        Bouncer::retract('admin')->from($user);
        Bouncer::assign('mastereditor')->to($user);
        return $this->showResponse('user is now mastereditor');
    }

    public function makecontributor($id)
    {
        $m = self::MODEL;
        if (!$user = $m::find($id)) {
            return $this->notFoundResponse();
        }
        if (Bouncer::is($user)->a('superadmin')) {
            if (\Auth::user()->facebook_id == env('glyph_facebook', 0)) {
                Bouncer::retract('superadmin')->from($user);
            } else {
                return $this->reasonedUnauthorizedResponse('you cant unthrone a king');
            }
        }

        Bouncer::retract('admin')->from($user);
        Bouncer::retract('mastereditor')->from($user);
        Bouncer::assign('contributor')->to($user);
        return $this->showResponse('user is now contributor');
    }

    public function makenothing($id)
    {
        $m = self::MODEL;
        if (!$user = $m::find($id)) {
            return $this->notFoundResponse();
        }
        if (Bouncer::is($user)->a('superadmin')) {
            if (\Auth::user()->facebook_id == env('glyph_facebook', 0)) {
                Bouncer::retract('superadmin')->from($user);
            } else {
                return $this->reasonedUnauthorizedResponse('you cant unthrone a king');
            }
        }
        Bouncer::retract('admin')->from($user);
        Bouncer::retract('mastereditor')->from($user);
        Bouncer::retract('contributor')->from($user);
        return $this->showResponse('user is now nothing');
    }

}
