<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Plista\StatsdClient\StatsdClient;
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
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
    {
        $response = [
            'code' => 500,
            'exception' => get_class($exception),
        ];

        switch(true){
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

        if(empty($message)){
            $arr = explode('\\', get_class($exception));
            $response['message'] = trim(implode(" ",preg_split('/(?=[A-Z])/',array_pop($arr))));
        }else{
            $response['message'] = $message;
        }

        if(config('app.debug') == "true"){
            $response["stack"] = explode("\n",$exception->getTraceAsString());
        }else{
            $response["stack"] = "Disabled: Production Mode";
        }

        return new JsonResponse($response, $response['code']);
    }
}
