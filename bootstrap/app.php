<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Return JSON for unauthenticated requests (instead of HTML redirect)
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid token.',
            ], 401);
        });

        // Return JSON for validation errors
        $exceptions->renderable(function (ValidationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        });

        // Return JSON for 404 Not Found
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'The requested resource was not found.',
            ], 404);
        });

        // Return JSON for 405 Method Not Allowed
        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'The HTTP method is not allowed for this route.',
            ], 405);
        });
    })->create();
