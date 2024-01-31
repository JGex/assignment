<?php

namespace App\Http;

use Illuminate\Http\Request as BaseRequest;

class RequestDecorator extends BaseRequest
{
    /**
     * {@inheritDoc}
     */
    public function expectsJson(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function wantsJson(): bool
    {
        return true;
    }
}
