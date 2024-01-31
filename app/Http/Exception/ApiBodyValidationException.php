<?php

namespace App\Http\Exception;

use Illuminate\Http\Response;

class ApiBodyValidationException extends AbstractApiRequestException
{
    /**
     * @throws ApiBodyValidationException
     */
    public static function productValidationException(array $validationError): self
    {
        throw new self(
            message: 'The body is malformed',
            code: self::PRODUCT_VALIDATION_EXCEPTION,
            details: $validationError
        );
    }
}
