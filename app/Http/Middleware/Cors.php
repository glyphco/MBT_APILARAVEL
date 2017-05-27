<?php

namespace App\Http\Middleware;

use Closure;
use Response;

class CORS
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $incoming  = 'unknown';
        $okorigin  = env('CORS_DEFAULT', "https://myboringtown.com");
        $okorigins = array_map('trim', explode(',', env('CORS_ALLOWED')));

        if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
            $incoming = $_SERVER['HTTP_ORIGIN'];
        }

        if (in_array($incoming, $okorigins)) {
            header("Access-Control-Allow-Origin: " . $incoming);
        } else {
            header("Access-Control-Allow-Origin: " . $okorigin);
        }

        // ALLOW OPTIONS METHOD
        $headers = [
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, Origin, Authorization, X-Requested-With',
        ];
        if ($request->getMethod() == "OPTIONS") {
            // The client-side application can set only headers allowed in Access-Control-Allow-Headers
            return Response::make('OK', 200, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }

}
