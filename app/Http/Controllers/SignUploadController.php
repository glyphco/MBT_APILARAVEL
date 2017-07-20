<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Aws\S3\PostObjectV4;
use Aws\S3\S3Client;
use Bouncer;
use Illuminate\Http\Request;

class SignUploadController extends BaseController
{

    //http://cwhite.me/avoiding-the-burden-of-file-uploads/

    protected $client;

    protected $validitems = [
        'event' => ['edit-events', 'App\Models\Event', 'for' => ['main']],
        'mve'   => ['edit-events', 'App\Models\Mve', 'for' => ['main']],
        'page'  => ['edit-pages', 'App\Models\Page', 'for' => ['main']],
        'show'  => ['edit-shows', 'App\Models\Show', 'for' => ['main']],
        'venue' => ['edit-venues', 'App\Models\Venue', 'for' => ['main']],
        'user'  => ['edit-users', 'App\Models\User', 'for' => ['main']],
    ];

    public function __construct(S3Client $client)
    {
        $this->client = $client;
    }

    /**
     * Generate a presigned upload request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sign(Request $request, $item, $id, $for)
    {

        $data = $this->checkpermissions($item, $id, $for);

        $head = $item . '/' . $id . '/main/';

        $options = [
            ['bucket' => config('services.s3.bucket')],
            ['starts-with', '$key', $head],
        ];

        $postObject = new PostObjectV4(
            $this->client,
            config('services.s3.bucket'),
            [],
            $options,
            '+10 minutes'
        );

        // foreach ($postObject->getFormInputs() as $key => $item) {
        //     echo ($key . " : " . $item);
        //     echo "\r\n";
        // }

        return response()->json([
            'I_WILL_REMOVE_THIS' => $head,
            'attributes'         => $postObject->getFormAttributes(),
            'additionalData'     => $postObject->getFormInputs(),
        ]);
    }

    public function checkpermissions($item, $id, $for)
    {

        if (!array_key_exists($item, $this->validitems)) {
            $this->clientErrorResponse('Could not save: [' . $item . '] not found')->send();
        }

// check that item "for" can be used:

        if (!in_array($for, $this->validitems[$item]['for'])) {
            $this->clientErrorResponse('Could not save: [' . $for . '] not allowed')->send();
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

//Unused for MBT
    public function presignedforafile(Request $request)
    {
        $s3     = \Storage::disk('s3');
        $client = $s3->getDriver()->getAdapter()->getClient();

        $command = $client->getCommand('GetObject', [
            // 'Bucket' => env('AWS_BUCKET', 'xxx'),
            // 'Key'    => env('AWS_KEY', 'xxx'),
            'Bucket' => \Config::get('filesystems.disks.s3.bucket'),
            'Key'    => 'hello.txt',
        ]);
        //dump($command->toarray());
        //$request = $s3Client->createPresignedRequest($cmd, '+20 minutes');

        $request = $client->createPresignedRequest($command, '+10 minutes');

        //dump($request);

// Get the actual presigned-url
        $presignedUrl = (string) $request->getUri();

        $replace = [
            'X-Amz-Content-Sha256',
            'X-Amz-Algorithm',
            'X-Amz-Credential',
            'X-Amz-Date',
            'X-Amz-SignedHeaders',
            'X-Amz-Expires',
            'X-Amz-Signature'];
        $new = [
            'x-amz-content-sha256',
            'x-amz-algorithm',
            'x-amz-credential',
            'x-amz-date',
            'x-amz-signedheaders',
            'x-amz-expires',
            'x-amz-signature'];
        //$presignedUrl = str_replace($replace, $new, $presignedUrl);

        dd($presignedUrl);

    }

}
