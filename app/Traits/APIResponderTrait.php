<?php
namespace App\Traits;

trait APIResponderTrait
{

    protected function createdResponse($data)
    {
        $response = [
            'code'   => 201,
            'status' => 'succcess',
            'data'   => $data,
        ];
        return response()->json($response, $response['code']);
    }

    protected function showResponse($data)
    {
        $response = [
            'code'   => 200,
            'status' => 'succcess',
            'data'   => $data,
        ];
        return response()->json($response, $response['code']);
    }

    protected function listResponse($data)
    {
        $response = [
            'code'   => 200,
            'status' => 'succcess',
            'data'   => $data,
        ];
        return response()->json($response, $response['code']);
    }

    protected function pureResponse($data)
    {
        return response()->json($data, 200);
    }

    protected function notFoundResponse()
    {
        $response = [
            'code'    => 404,
            'status'  => 'error',
            'data'    => 'Resource Not Found',
            'message' => 'Not Found',
        ];
        return response()->json($response, $response['code']);
    }

    protected function reasonedNotFoundResponse($reason)
    {
        $response = [
            'code'    => 404,
            'status'  => 'error',
            'data'    => $reason,
            'message' => 'Not Found',
        ];
        return response()->json($response, $response['code']);
    }

    protected function deletedResponse()
    {
        $response = [
            'code'    => 200,
            'status'  => 'success',
            'data'    => 'Item was Deleted',
            'message' => 'Deleted',
        ];
        return response()->json($response, $response['code']);
    }

    protected function clientErrorResponse($data)
    {
        $response = [
            'code'    => 422,
            'status'  => 'error',
            'data'    => $data,
            'message' => 'Unprocessable entity',
        ];
        return response()->json($response, $response['code']);
    }

    protected function unauthorizedResponse()
    {
        $response = [
            'code'    => 401,
            'status'  => 'error',
            'data'    => 'You dont have permission',
            'message' => 'Unauthorized',
        ];
        return response()->json($response, $response['code']);
    }

    protected function reasonedUnauthorizedResponse($reason)
    {
        $response = [
            'code'    => 401,
            'status'  => 'error',
            'data'    => $reason,
            'message' => 'Unauthorized',
        ];
        return response()->json($response, $response['code']);
    }

}
