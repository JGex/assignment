<?php

namespace App\Domain\FakeStore\Exception;

use App\Importer\Exception\ProductImporterException;

class FakeStoreApiClientException extends \Exception implements ProductImporterException
{
    const int GATEWAY_RESPONSE_ERROR = 10000;
    const int GATEWAY_FETCH_ERROR = 10100;

    public function __construct(
        string $message,
        int $code,
        private readonly array $details = [],
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @throws FakeStoreApiClientException
     */
    public static function gatewayResponseError(array $details = [], \Throwable $previous = null): void
    {
        throw new self(
            'Error when receiving the response from Fake Store API',
            self::GATEWAY_RESPONSE_ERROR,
            $details,
            $previous
        );
    }

    /**
     * @throws FakeStoreApiClientException
     */
    public static function unhandledException(array $details = [], \Throwable $previous = null): void
    {
        throw new self(
            'Error when fetching the Fake Store API',
            self::GATEWAY_FETCH_ERROR,
            $details,
            $previous
        );
    }
}
