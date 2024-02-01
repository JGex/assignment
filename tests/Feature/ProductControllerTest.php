<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private ?string $token = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(ProductSeeder::class);

        $user = User::all()->first();
        $this->token = $user->createToken('Token Name')->plainTextToken;
    }

    public function test_routes_need_bearer(): void
    {
        $this->get('/api/product')
            ->assertUnauthorized();

        $this->get('/api/product/1')
            ->assertUnauthorized();

        $this->put('/api/product/1')
            ->assertUnauthorized();
    }

    public function test_routes_with_bearer(): void
    {
        $product = Product::inRandomOrder()->first();

        $this->get('/api/product', $this->getHeaders())
            ->assertJson(fn (AssertableJson $json) => $json->first(fn ($json) => $json
                ->has('id')
                ->has('title')
                ->has('price')
                ->has('description')
                ->has('category_id')
                ->has('image')
                ->has('rating')
                ->has('fake_store_id')
                ->has('created_at')
                ->has('updated_at')
            ))
            ->assertOk();

        $this->get(sprintf('/api/product/%d', $product->id), $this->getHeaders())
            ->assertOk();

        $this->put(
            sprintf('/api/product/%d', $product->id),
            [
                'title' => 'Test new title',
                'price' => 0.01,
                'description' => 'Test new description',
                'image' => 'http://proxify.io/',
            ],
            $this->getHeaders()
        )->assertNoContent();

        $this->get(sprintf('/api/product/%d', $product->id), $this->getHeaders())
            ->assertJsonPath('title', 'Test new title')
            ->assertJsonPath('price', 0.01)
            ->assertJsonPath('description', 'Test new description')
            ->assertJsonPath('image', 'http://proxify.io/');
    }

    public function test_routes_update_with_errors(): void
    {
        $product = Product::inRandomOrder()->first();

        $this->put(
            sprintf('/api/product/%d', $product->id),
            [
                'price' => 0,
                'image' => 'http://this.is.a.bad.url/',
            ],
            $this->getHeaders()
        )
            ->assertJsonPath('errors.price.0', 'The price field must be greater than 0.')
            ->assertJsonPath('errors.image.0', 'validation.active_u_r_l');

        $this->put(
            sprintf('/api/product/%d', $product->id),
            [
                'price' => 9.222,
            ],
            $this->getHeaders()
        )
            ->assertJsonPath('errors.price.0', 'The price field must have 0-2 decimal places.');
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => sprintf('Bearer %s', $this->token),
        ];
    }
}
