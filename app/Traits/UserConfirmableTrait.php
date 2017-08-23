<?php
namespace App\Traits;

use Bouncer;

trait UserConfirmableTrait
{

    public function confirm($id)
    {
        $m = self::MODEL;

        if (!$data = $m::BannedAndNotBanned()->ConfirmedAndUnconfirmed()->find($id)) {
            return $this->notFoundResponse();
        }

        if (!(Bouncer::allows($this->confirmitems))) {
            return $this->unauthorizedResponse();
        }

        try
        {
            $data->confirmed = 1;
            $data->save();
            return $this->showResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }

    }
    public function unconfirm($id)
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
            $data->confirmed = 0;
            $data->save();
            return $this->showResponse($data);
        } catch (\Exception $ex) {
            $data = ['form_validations' => $v->errors(), 'exception' => $ex->getMessage()];
            return $this->clientErrorResponse($data);
        }
    }

}
