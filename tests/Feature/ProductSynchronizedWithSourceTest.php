<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\Console\Command\Command;
use Tests\TestCase;

class ProductSynchronizedWithSourceTest extends TestCase
{
    use RefreshDatabase;

    private ?string $token = null;
    private ?string $fakeStoreApiProductsURL = null;
    private ?string $fakeStoreApiCategoriesURL = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);

        $user = User::all()->first();
        $this->token = $user->createToken('Token Name')->plainTextToken;

        $this->fakeStoreApiProductsURL = sprintf(
            '%s/products',
            env('FAKE_STORE_API_URL')
        );
        $this->fakeStoreApiCategoriesURL = sprintf(
            '%s/products/categories',
            env('FAKE_STORE_API_URL')
        );
    }

    public function test_product_synchronized_with_source(): void
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

        $product = Product::inRandomOrder()->first();

        $originalProduct = $this->get(sprintf('/api/product/%d', $product->id), $this->getHeaders());

        $this->put(
            sprintf('/api/product/%d', $product->id),
            [
                'title' => 'Title has changed',
                'price' => 0.01,
                'description' => 'Description has changed',
                'image' => 'http://proxify.io/',
            ],
            $this->getHeaders()
        )->assertNoContent();

        $updatedProduct = $this->get(sprintf('/api/product/%d', $product->id), $this->getHeaders());

        $this->assertNotEquals(
            [
                $originalProduct['title'],
                $originalProduct['price'],
                $originalProduct['description'],
                $originalProduct['image'],
            ],
            [
                $updatedProduct['title'],
                $updatedProduct['price'],
                $updatedProduct['description'],
                $updatedProduct['image'],
            ]
        );
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => sprintf('Bearer %s', $this->token),
        ];
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
