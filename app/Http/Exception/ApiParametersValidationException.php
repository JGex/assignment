<?php

namespace App\Http\Exception;

use Illuminate\Http\Response;

class ApiParametersValidationException extends AbstractApiRequestException
{
    /**
     * @throws ApiParametersValidationException
     */
    public static function productParametersValidationException(array $validationError): self
    {
        throw new self(
            message: 'Error with the parameters',
            code: self::PARAMETERS_VALIDATION_EXCEPTION,
            details: $validationError
        );
    }
}
