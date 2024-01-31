<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Command\Command;
use Tests\TestCase;

class ProductImporterTest extends TestCase
{
    use RefreshDatabase;

    private ?string $fakeStoreApiProductsURL = null;
    private ?string $fakeStoreApiCategoriesURL = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->fakeStoreApiProductsURL = sprintf(
            '%s/products',
            env('FAKE_STORE_API_URL')
        );
        $this->fakeStoreApiCategoriesURL = sprintf(
            '%s/products/categories',
            env('FAKE_STORE_API_URL')
        );
    }

    public function test_import_command_arguments(): void
    {
        $this->artisan('product:import')
            ->expectsOutput('The source must be in the following : FakeStore')
            ->assertExitCode(Command::FAILURE);

        $this->artisan('product:import test')
            ->expectsOutput('The source must be in the following : FakeStore')
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_import_category_importer_exception(): void
    {
        Http::fake([
            $this->fakeStoreApiCategoriesURL => Http::response(['error' => 'test'], 404),
        ]);

        $this
            ->artisan('product:import FakeStore')
            ->expectsOutputToContain(sprintf(
                '{"url":%s,"response":{"status":404,"body":"{\"error\":\"test\"}"}}',
                json_encode($this->fakeStoreApiCategoriesURL)
            ))
            ->assertExitCode(Command::FAILURE);

        Http::assertSentCount(3);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_import_product_importer_exception(): void
    {
        Http::fake([
            $this->fakeStoreApiCategoriesURL => Http::response([
                'test cat',
            ]),
            $this->fakeStoreApiProductsURL => Http::response(['error' => 'test'], 500),
        ]);

        $this
            ->artisan('product:import FakeStore')
            ->expectsOutputToContain(sprintf(
                '{"url":%s,"response":{"status":500,"body":"{\"error\":\"test\"}"}}',
                json_encode($this->fakeStoreApiProductsURL)
            ))
            ->assertExitCode(Command::FAILURE);

        Http::assertSentCount(4);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_import_category_validation_exception(): void
    {
        Http::fake([
            $this->fakeStoreApiCategoriesURL => Http::response([
                '',
            ]),
        ]);

        $this
            ->artisan('product:import FakeStore')
            ->expectsOutput('An error occurred, process has been stopped')
            ->expectsOutputToContain('The name field is required.')
            ->assertExitCode(Command::FAILURE);

        Http::assertSentCount(1);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_import_product_validation_exception(): void
    {
        Http::fake([
            $this->fakeStoreApiCategoriesURL => Http::response([
                'test cat',
            ]),
            $this->fakeStoreApiProductsURL => Http::response([
                $this->createRandomProductDTO(['category' => 'test cat', 'price' => 0]),
            ]),
        ]);

        $this
            ->artisan('product:import FakeStore')
            ->expectsOutput('An error occurred, process has been stopped')
            ->expectsOutputToContain('The price field must be greater than 0.')
            ->assertExitCode(Command::FAILURE);

        Http::assertSentCount(2);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_import_product_and_update(): void
    {
        Http::fake([
            $this->fakeStoreApiCategoriesURL => Http::response([
                'test cat 1',
                'test cat 2',
                'test cat 3',
            ]),
            $this->fakeStoreApiProductsURL => Http::response([
                $this->createRandomProductDTO(['category' => 'test cat 1']),
                $this->createRandomProductDTO(['category' => 'test cat 1']),
                $this->createRandomProductDTO(['category' => 'test cat 1']),
                $this->createRandomProductDTO(['category' => 'test cat 2']),
                $this->createRandomProductDTO(['category' => 'test cat 2']),
                $this->createRandomProductDTO(['category' => 'test cat 2']),
                $this->createRandomProductDTO(['category' => 'test cat 3']),
                $this->createRandomProductDTO(['category' => 'test cat 3']),
                $this->createRandomProductDTO(['category' => 'test cat 3']),
            ]),
        ]);

        $this
            ->artisan('product:import FakeStore')
            ->expectsOutput('Process terminated with success')
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseCount('products', 9);
        $this->assertDatabaseCount('categories', 3);

        $this
            ->artisan('product:import FakeStore')
            ->expectsOutput('Process terminated with success')
            ->assertExitCode(Command::SUCCESS);

        Http::assertSentCount(4);
        $this->assertDatabaseCount('products', 9);
        $this->assertDatabaseCount('categories', 3);
    }

    private function createRandomProductDTO(array $attributes = []): array
    {
        return [
            'id' => $attributes['id'] ?? fake()->numberBetween(),
            'title' => $attributes['title'] ?? fake()->name(),
            'price' => $attributes['price'] ?? fake()->randomDigitNotZero(),
            'description' => $attributes['description'] ?? fake()->text(),
            'category' => $attributes['category'] ?? fake()->name(),
            'image' => $attributes['image'] ?? fake()->imageUrl(),
            'rating' => [
                'rate' => $attributes['rating']['score'] ?? fake()->numberBetween(0, 5),
                'count' => $attributes['rating']['count'] ?? fake()->numberBetween(0, 500),
            ],
        ];
    }
}
