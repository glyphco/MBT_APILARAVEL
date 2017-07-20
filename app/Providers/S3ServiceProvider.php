<?php

namespace App\Providers;

use Aws\S3\S3Client;
use Illuminate\Support\ServiceProvider;

class S3ServiceProvider extends ServiceProvider
{
    /**
     * Bind the S3Client to the service container.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(S3Client::class, function () {

            return new S3Client([
                'credentials' => [
                    'key'    => config('services.s3.key'),
                    'secret' => config('services.s3.secret'),
                ],
                'region'      => config('services.s3.region'),
                'version'     => 'latest',
            ]);
        });
    }

    public function register()
    {}
}
