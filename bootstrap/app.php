<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/web.php',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'cors' => \App\Http\Middleware\Cors::class,
        ]);
    })
    ->withProviders([
        \App\Providers\AppServiceProvider::class,
        \App\Providers\AuthServiceProvider::class,
        \RepoRangler\Providers\AppServiceProvider::class,
        \RepoRangler\Providers\TokenServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e) {
            return new JsonResponse(['code' => 401, 'message' => $e->getMessage()], 401);
        });

        $exceptions->render(function (\Throwable $e) {
            $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : ($e->getCode() ?: 500);

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $code = 404;
            } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
                return new JsonResponse([
                    'code' => $e->status,
                    'message' => $e->getMessage(),
                    'validation' => $e->validator->errors(),
                ], $e->status);
            }

            $response = [
                'code' => $code,
                'message' => $e->getMessage(),
            ];

            if (config('app.debug') === true) {
                $response['exception'] = get_class($e);
                $response['stack'] = explode("\n", $e->getTraceAsString());
            }

            return new JsonResponse($response, is_int($code) && $code >= 100 && $code < 600 ? $code : 500);
        });
    })->create();
