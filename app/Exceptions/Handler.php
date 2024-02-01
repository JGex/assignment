<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): JsonResponse
    {
        Log::error($e->getMessage(), [
            'context' => 'API',
            'domain' => 'HANDLER',
            'exception' => $e,
        ]);

        return new JsonResponse(
            $this->getDataFromException($e),
            $this->getStatusFromException($e)
        );
    }

    private function getDataFromException(Throwable $e): array
    {
        $data = [
            'success' => false,
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        ];

        if (env('APP_ENV') !== 'production') {
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = $e->getTrace();
            $data['previous'] = $e->getPrevious();
        }

        return $data;
    }

    private function getStatusFromException(Throwable $e): int
    {
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
        } elseif ($e instanceof ModelNotFoundException) {
            $status = Response::HTTP_NOT_FOUND;
        } elseif ($e instanceof AuthenticationException) {
            $status = Response::HTTP_UNAUTHORIZED;
        }

        return $status;
    }
}
