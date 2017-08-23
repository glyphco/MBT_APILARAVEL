<?php
namespace App\Traits;

use Bouncer;
use Carbon\Carbon;
use Illuminate\Http\Request;

trait UserBanableTrait
{

    public function ban(Request $request, $id)
    {
        $m = self::MODEL;

        if (!$data = $m::BannedAndNotBanned()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if (!(Bouncer::allows($this->banitems))) {
            return $this->unauthorizedResponse();
        }

        $banned_until = Carbon::now()->addDays(10)->toDateString();
        if ($request->exists('banned_until')) {
            $banned_until = $request->input('banned_until');
        }

        try
        {
            $data->is_banned    = 1;
            $data->banned_until = $banned_until;
            $data->save();
            return $this->showResponse($data);
        } catch (\Exception $ex) {
            $data = ['exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

    }

    public function unban($id)
    {
        $m = self::MODEL;

        if (!$data = $m::BannedAndNotBanned()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if (!(Bouncer::allows($this->banitems))) {
            return $this->unauthorizedResponse();
        }

        try
        {
            $data->is_banned    = 0;
            $data->banned_until = null;
            $data->save();
            return $this->showResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }

}
