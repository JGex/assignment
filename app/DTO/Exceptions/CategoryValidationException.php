<?php

namespace App\DTO\Exceptions;

class CategoryValidationException extends AbstractValidationException
{
    const int CATEGORY_VALIDATION_ERROR = 20000;
    const string IDENTIFIER_NAME = 'Category name';

    public function __construct(
        private readonly string $categoryName,
        array $details = [],
    ) {
        parent::__construct(code: self::CATEGORY_VALIDATION_ERROR);

        $this->details = $details;
    }

    protected function getIdentifier(): string
    {
        return $this->categoryName;
    }
}
