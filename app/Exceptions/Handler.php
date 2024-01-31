<?php

namespace App\Exceptions;

use App\Http\Exception\AbstractApiRequestException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
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
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $data = [
            'success' => false,
            'code' => $e->getCode(),
            'message' => [
                $e->getMessage(),
            ],
        ];

        if (env('APP_ENV') !== 'production') {
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = $e->getTrace();
            $data['previous'] = $e->getPrevious();
        }

        if ($e instanceof BadRequestException) {
            $status = Response::HTTP_BAD_REQUEST;
        } elseif ($e instanceof AbstractApiRequestException) {
            $status = $e->getStatus();
            $data['details'] = $e->getDetails();
        }

        return new JsonResponse($data, $status);
    }
}
