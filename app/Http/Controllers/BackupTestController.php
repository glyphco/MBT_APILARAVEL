<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Aws\S3\PostObjectV4;
use Illuminate\Http\Request;

class TestController extends BaseController
{

    /**
     * Generate a presigned upload request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $s3     = \Storage::disk('s3');
        $client = $s3->getDriver()->getAdapter()->getClient();

        $options = [
            ['bucket' => config('services.s3.bucket')],
            ['starts-with', '$key', ''],
        ];

        $postObject = new PostObjectV4(
            $client,
            config('services.s3.bucket'),
            [],
            $options,
            '+1 minute'
        );

        return response()->json([
            'attributes'     => $postObject->getFormAttributes(),
            'additionalData' => $postObject->getFormInputs(),
        ]);
    }

    public function fuhindex(Request $request)
    {
        $s3     = \Storage::disk('s3');
        $client = $s3->getDriver()->getAdapter()->getClient();

        $bucket = \Config::get('filesystems.disks.s3.bucket');

// Set some defaults for form input fields
        $formInputs = ['acl' => 'public-read'];

// Construct an array of conditions for policy
        $options = [
            ['acl' => 'public-read'],
            ['bucket' => $bucket],
            ['starts-with', '$key', 'event/1/'],
        ];

// Optional: configure expiration time string
        $expires = '+2 hours';

        $postObject = new \Aws\S3\PostObjectV4(
            $client,
            $bucket,
            $formInputs,
            $options,
            $expires
        );

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
