<?php

namespace App\DTO\Exceptions;

abstract class AbstractValidationException extends \Exception
{
    protected array $details = [];

    public function getDetails(): array
    {
        return array_map(function ($detail) {
            return [$this->getIdentifier(), $detail];
        }, $this->details);
    }

    abstract protected function getIdentifier(): string;
}
