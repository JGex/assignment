<?php

namespace App\Providers;

use App\Console\Commands\ImportProduct;
use App\Domain\FakeStore\Services\FakeStoreApiClient;
use App\Models\Repository\CategoryRepository;
use App\Models\Repository\ProductRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->tag(
            [FakeStoreApiClient::class],
            'import.product.source'
        );

        $this->app->bind(
            FakeStoreApiClient::class,
            function () {
                return new FakeStoreApiClient(
                    env('FAKE_STORE_API_URL')
                );
            }
        );

        $this->app->bind(
            ImportProduct::class,
            function () {
                $sources = [];
                collect($this->app->tagged('import.product.source'))->map(function ($tagged) use (&$sources) {
                    $sources[$tagged->getSourceName()] = $tagged;
                });

                return new ImportProduct(
                    $sources,
                    $this->app->make(ProductRepository::class),
                    $this->app->make(CategoryRepository::class),
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
