<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

class TestController extends BaseController
{

    public function index(Request $request)
    {
        $s3     = \Storage::disk('s3');
        $client = $s3->getDriver()->getAdapter()->getClient();

        $command = $client->getCommand('GetObject', [
            // 'Bucket' => env('AWS_BUCKET', 'xxx'),
            // 'Key'    => env('AWS_KEY', 'xxx'),
            'Bucket' => \Config::get('filesystems.disks.s3.bucket'),
            'Key'    => '10128129_2.jpg',
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
        $presignedUrl = str_replace($replace, $new, $presignedUrl);

        dd($presignedUrl);

    }

}
