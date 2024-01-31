<?php

namespace App\Providers;

use App\Console\Commands\ImportProduct;
use App\Domain\FakeStore\Services\FakeStoreApiClient;
use App\Http\RequestDecorator;
use App\Models\Repository\CategoryRepository;
use App\Models\Repository\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Decorate the Illuminate Request to be sur will only receive json response
        $this->app->bind(
            Request::class,
            fn () => $this->app->make(RequestDecorator::class)
        );

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
