<?php
namespace App\Traits;

use Bouncer;
use Illuminate\Http\Request;

trait ItemHasEditorsTrait
{

    public function giveedit(Request $request, $id, $userid)
    {
        $m = self::MODEL;
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }
        if (!$otheruser = \app\Models\User::find($userid)) {
            return $this->notFoundResponse();
        }

        if (!(
            (Bouncer::allows('administer', $data)) or
            (Bouncer::allows($this->adminitems))
        )) {
            return $this->unauthorizedResponse();
        }

        Bouncer::allow($otheruser)->to('edit', $data);
        return $this->showResponse('');

    }

    public function revokeedit(Request $request, $id, $userid)
    {
        $m = self::MODEL;
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }
        if (!$otheruser = \app\Models\User::find($userid)) {
            return $this->notFoundResponse();
        }

        if (!(
            (Bouncer::allows('administer', $data)) or
            (Bouncer::allows($this->adminitems))
        )) {
            return $this->unauthorizedResponse();
        }

        Bouncer::disallow($otheruser)->to('edit', $data);
        return $this->showResponse('');

    }

    public function getEditors(Request $request, $id)
    {
        $m = self::MODEL;
        if (!$data = $m::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if (!(
            (Bouncer::allows('administer', $data)) or
            (Bouncer::allows($this->adminitems))
        )) {
            return $this->unauthorizedResponse();
        }
        $users = \app\Models\User::WhereCan('edit', $data)->select('id', 'name', 'avatar')->get();
        return $this->showResponse($users);

    }

}
