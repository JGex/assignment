<?php

namespace App\Domain;

abstract class AbstractApiClient
{
    abstract protected function fetch(string $path): array;
}
