<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $instance = $this;

        Response::macro('ok', function ($data = [], $message = 'Request successfully processed') {
            $response = [
                'message' => $message,
                'data' => $data
            ];
            return Response::json($response, 200);
        });

        Response::macro('created', function ($data = [], $message = 'Request successfully processed') {
            $response = [
                'message' => $message,
                'data' => $data
            ];
            return Response::json($response, 201);
        });

        Response::macro('unprocessableContent', function ($errors = [], $message = 'Unable to process content') use ($instance) {
            return $instance->handleErrorResponse($message, $errors, 422);
        });

        Response::macro('noContent', function () {
            return Response::json([], 204);
        });

        Response::macro('badRequest', function ($errors = [], $message = 'Failed Validation') use ($instance) {
            return $instance->handleErrorResponse($message, $errors, 400);
        });

        Response::macro('unauthorized', function ($errors = [], $message = 'Unauthorized User') use ($instance) {
            return $instance->handleErrorResponse($message, $errors, 401);
        });

        Response::macro('forbidden', function ($errors = [], $message = 'Denied access') use ($instance) {
            return $instance->handleErrorResponse($message, $errors, 403);
        });

        Response::macro('notFound', function ($errors = [], $message = 'Resource not found') use ($instance) {
            return $instance->handleErrorResponse($message, $errors, 404);
        });

        Response::macro('internalServerError', function ($errors = [], $message = 'Internal server error') use ($instance) {
            return $instance->handleErrorResponse($message, $errors, 500);
        });
    }

    function handleErrorResponse($message, $errors, $status)
    {
        $response = ['message' => $message];

        if (count($errors)) {
            $response['errors'] = $errors;
        }

        return Response::json($response, $status);
    }
}
