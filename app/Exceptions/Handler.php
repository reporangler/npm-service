<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
    {
        return new \Illuminate\Http\JsonResponse([
            'code' => 401,
            'message' => $exception->getMessage(),
        ], 401);
    }
    public function register(): void
    {
        $this->renderable(function (Throwable $exception, $request) {
            $response = [
                'code' => 500,
                'exception' => get_class($exception),
            ];

            switch (true) {
                case $exception instanceof HttpException:
                    $response['code'] = $exception->getStatusCode();
                    break;

                case $exception instanceof ModelNotFoundException:
                    $response['code'] = 404;
                    break;

                case $exception instanceof QueryException:
                case $exception instanceof \PDOException:
                    $response['code'] = 500;
                    $response['db-code'] = $exception->getCode();
                    break;

                default:
                    error_log(get_class($exception));
                    break;
            }

            $message = $exception->getMessage();

            if (empty($message)) {
                $arr = explode('\\', get_class($exception));
                $response['message'] = trim(implode(" ", preg_split('/(?=[A-Z])/', array_pop($arr))));
            } else {
                $response['message'] = $message;
            }

            if (config('app.debug') === true) {
                $response["stack"] = explode("\n", $exception->getTraceAsString());
            } else {
                $response["stack"] = "Disabled: Production Mode";
            }

            return new JsonResponse($response, $response['code']);
        });
    }
}
