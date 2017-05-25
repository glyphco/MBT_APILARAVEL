<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {

        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            $response = [
                'code'    => 403,
                'status'  => 'error',
                'data'    => 'Token Is Expired (Code#exception12)',
                'message' => 'Unprocessable entity',
            ];
            return response()->json($response, $response['code']);
        }

        if ($exception instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
            $response = [
                'code'    => 401,
                'status'  => 'error',
                'data'    => 'Token Is Invalid (Code#exception22)',
                'message' => 'Unprocessable entity',
            ];
            return response()->json($response, $response['code']);
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\BadRequestHttpException) {
            if ($exception->getMessage() == 'Token not provided') {
                $message  = $exception->getMessage();
                $response = [
                    'code'    => 401,
                    'status'  => 'error',
                    'data'    => 'Token Not Provided (Code#exception32)',
                    'message' => $message,
                ];
                return response()->json($response, $response['code']);
            }
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
            if ($request->expectsJson()) {
                $code    = (string) 400;
                $message = $exception->getMessage();

                $error[] = [
                    'status' => '401',
                    'code'   => 'exception232',
                    'source' => ["pointer" => "unauthenticated login handler"],
                    'title'  => $message,
                    'detail' => 'Must supply a valid token',
                ];
                $response = [
                    'errors' => $error,
                ];
                $contenttype = 'application/vnd.api+json';
                return response()->json($response, $code)->header('Content-Type', $contenttype);
            }

            $message  = $exception->getMessage();
            $response = [
                'code'    => 401,
                'status'  => 'error',
                'data'    => 'Must supply a valid token (Code#exception33)',
                'message' => $message,
            ];
            return response()->json($response, $response['code']);
        }

        if ($exception instanceof Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json(['token_expired'], $exception->getStatusCode());
        } else if ($exception instanceof Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json(['token_invalid'], $exception->getStatusCode());
        }

        return parent::render($request, $exception);
    }

    // /**
    //  * Convert an authentication exception into an unauthenticated response.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \Illuminate\Auth\AuthenticationException  $exception
    //  * @return \Illuminate\Http\Response
    //  */
    // protected function unauthenticated($request, AuthenticationException $exception)
    // {
    //     if ($request->expectsJson()) {
    //         return response()->json(['error' => 'Unauthenticated.'], 401);
    //     }

    //     return redirect()->guest(route('login'));
    // }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            $code    = (string) 400;
            $message = $exception->getMessage();

            $error[] = [
                'status' => '401',
                'code'   => 'exception232',
                'source' => ["pointer" => "unauthenticated login handler"],
                'title'  => $message,
                'detail' => 'Must supply a valid token',
            ];
            $response = [
                'errors' => $error,
            ];
            $contenttype = 'application/vnd.api+json';
            return response()->json($response, $code)->header('Content-Type', $contenttype);
        }

        return redirect()->guest(route('login'));
    }

}
