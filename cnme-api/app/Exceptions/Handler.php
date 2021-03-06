<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;



class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        //parent::report($exception);

        Log::channel('single')->error('message: '.$exception->getMessage().
                "\nFile: ".$exception->getFile().":".$exception->getLine());

        Log::channel('daily')->info('message: '.$exception->getMessage().
                "\nFile: ".$exception->getFile().":".$exception->getLine()."\n".
                $exception->getTraceAsString()
        );
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

        
        // Not found exception handler
        if($exception instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'URL inválida.'
            ], 404);
        }

        if($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'message' => 'Método HTTP não implementado.'
            ], 405);
        }

        if ($exception instanceof UnauthorizedHttpException) {
            $preException = $exception->getPrevious();
            if ($preException instanceof
                          \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['message' => 'TOKEN_EXPIRED'],401);
            } else if ($preException instanceof
                          \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['message' => 'TOKEN_INVALID'], 401);
            } else if ($preException instanceof
                     \Tymon\JWTAuth\Exceptions\TokenBlacklistedException) {
                 return response()->json(['message' => 'TOKEN_BLACKLISTED'],401);
           }
           if ($exception->getMessage() === 'Token not provided') {
               return response()->json(['message' => 'Token não foi enviado'],401);
           }
        }
        
        return response()->json([
                'message' => $exception->getMessage(),
                'file' => $exception->getFile().":".$exception->getLine()
            ],500);        
        

        //return parent::render($request, $exception);
    }
}
