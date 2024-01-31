<?php

namespace App\Http\Exception;

use Illuminate\Http\Response;

class AbstractApiRequestException extends \Exception
{
    public const int PRODUCT_VALIDATION_EXCEPTION = 40000;
    public const int PARAMETERS_VALIDATION_EXCEPTION = 40100;
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        private readonly int $status = Response::HTTP_BAD_REQUEST,
        private readonly array $details = []
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
