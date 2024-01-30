<?php

namespace App\DTO\Exceptions;

class ProductValidationException extends AbstractValidationException
{
    const int PRODUCT_VALIDATION_ERROR = 20000;

    public function __construct(
        private readonly int $productId,
        array $details = [],
    ) {
        parent::__construct(code: self::PRODUCT_VALIDATION_ERROR);

        $this->details = $details;
    }

    protected function getIdentifier(): string
    {
        return (string) $this->productId;
    }
}
