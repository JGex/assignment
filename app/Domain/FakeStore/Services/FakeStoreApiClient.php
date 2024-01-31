<?php

namespace App\Domain\FakeStore\Services;

use App\Domain\AbstractApiClient;
use App\Domain\FakeStore\Exception\FakeStoreApiClientException;
use App\DTO\CategoryDTO;
use App\DTO\Exceptions\CategoryValidationException;
use App\DTO\Exceptions\ProductValidationException;
use App\DTO\ProductDTO;
use App\Importer\ProductImporterInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class FakeStoreApiClient extends AbstractApiClient implements ProductImporterInterface
{
    private const string PRODUCTS = '/products';
    private const string CATEGORIES = '/products/categories';
    private const string SOURCE_NAME = 'FakeStore';

    public function __construct(
        private readonly string $url
    ) {
    }

    public function getSourceName(): string
    {
        return self::SOURCE_NAME;
    }

    /**
     * @return Collection<ProductDTO>
     *
     * @throws FakeStoreApiClientException
     * @throws ProductValidationException
     */
    public function getProducts(): Collection
    {
        return collect($this->fetch(self::PRODUCTS))
            ->map(function ($product) {
                return ProductDTO::fromFakeAPI($product);
            });
    }

    /**
     * @return Collection<CategoryDTO>
     *
     * @throws FakeStoreApiClientException
     * @throws CategoryValidationException
     */
    public function getCategories(): Collection
    {
        return collect($this->fetch(self::CATEGORIES))
            ->map(function ($category) {
                return CategoryDTO::fromFakeAPI($category);
            });
    }

    /**
     * @throws FakeStoreApiClientException
     */
    protected function fetch(string $path): array
    {
        $url = $this->buildURL($path);

        try {
            $response = Http::retry(3, 300, throw: false)->get($url);

            if ($response->failed()) {
                FakeStoreApiClientException::gatewayResponseError([
                    'url' => $url,
                    'response' => [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ],
                ]);
            }
        } catch (ConnectionException $e) {
            // If the error is not a FakeStoreApiClientException, transform it
            $details = [
                'url' => $url,
            ];

            if (isset($response)) {
                $details['response'] = [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ];
            }

            FakeStoreApiClientException::gatewayFetchError($details);
        }

        return $response->json();
    }

    private function buildURL(string $path): string
    {
        return $this->url.$path;
    }
}
