<?php

namespace App\Importer\Exception;

interface ProductImporterException extends \Throwable
{
    public function getDetails(): array;
}
