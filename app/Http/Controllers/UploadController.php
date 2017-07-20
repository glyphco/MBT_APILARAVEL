<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Bouncer;
use Illuminate\Http\Request;

class UploadController extends BaseController
{

    //This is unused, but can be used for direct file upoloads to the server.

    protected $validitems = [
        'event' => ['edit-events', 'App\Models\Event'],
        'mve'   => ['edit-events', 'App\Models\Mve'],
        'page'  => ['edit-pages', 'App\Models\Page'],
        'show'  => ['edit-shows', 'App\Models\Show'],
        'user'  => ['edit-users', 'App\Models\User'],
    ];

    public function uploadheadimage(Request $request, $item, $id)
    {
//         validate($request, [
        // ‘image’ => ‘required|image|mimes:jpeg,png,jpg,gif|max:2048’,
        // ]);
        $imagename = time() . $item . $id . '.' . $request->image->getClientOriginalExtension();
        //dd($imagename);

//Check Permissions
        if (!$data = $this->checkpermissions($item, $id)) {
            return $this->clientErrorResponse('Could not save: [' . $item . '] not found');
        }

        $image      = $request->file('image');
        $t          = \Storage::disk('s3')->put($imagename, file_get_contents($image), 'public');
        $S3filename = \Storage::disk('s3')->url($imagename);

        $data->imageurl = $S3filename;
        $data->save();

        dd($data->toarray());
        return $this->showResponse($data);
    }

    public function checkpermissions($item, $id)
    {

        if (!array_key_exists($item, $this->validitems)) {
            $this->clientErrorResponse('Could not save: [' . $item . '] not found')->send();
        }

//Find the item
        if ($item == 'user') {
            if (!$data = $this->validitems[$item][1]::find($id)) {
                $this->notFoundResponse()->send();
            }
        } else {
            if (!$data = $this->validitems[$item][1]::PublicAndPrivate()->ConfirmedAndUnconfirmed()->find($id)) {
                $this->notFoundResponse()->send();
            }
        }

        if (!((Bouncer::allows($this->validitems[$item][0])) or (Bouncer::allows('edit', $data)))) {
            $this->unauthorizedResponse()->send();
        }
        return $data;
    }
}
