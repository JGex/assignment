<?php

namespace Tests\Unit;

use App\DTO\Exceptions\ProductValidationException;
use App\DTO\ProductDTO;
use Tests\TestCase;

class ProductValidationTest extends TestCase
{
    /**
     * @dataProvider productValidationExceptionProvider
     */
    public function test_product_validation_exception(array $product, array $expected): void
    {
        try {
            ProductDTO::fromFakeAPI($product);
            $this->fail('ProductValidationException was not thrown');
        } catch (ProductValidationException $e) {
            foreach ($e->getDetails() as $i => $detail) {
                $this->assertSame($expected[$i], $detail[1]);
            }
        }
    }

    public static function productValidationExceptionProvider(): \Generator
    {
        yield 'Product with full empty properties' => [
            [
                'id' => null,
                'title' => null,
                'price' => null,
                'description' => null,
                'category' => null,
                'image' => null,
                'rating' => null,
            ],
            [
                'The title field is required.',
                'The price field is required.',
                'The description field is required.',
                'The category name field is required.',
                'The image field is required.',
                'The rating field must be an array.',
                'The rating.rate field is required.',
                'The rating.count field is required.',
            ],
        ];

        yield 'Product with bad and missing properties' => [
            [
                'id' => 1,
                'title' => 'test',
                'price' => 0,
                'description' => 'This is a description !',
                'category' => 'test',
                'image' => 'http://not_valid_url.test',
                'rating' => [
                    'rate' => null,
                    'count' => null,
                ],
            ],
            [
                'The price field must be greater than 0.',
                'validation.active_u_r_l',
                'The rating.rate field is required.',
                'The rating.count field is required.',
            ],
        ];

        yield 'Product with bad properties' => [
            [
                'id' => 1,
                'title' => 'test',
                'price' => 0.111,
                'description' => 'This is a description !',
                'category' => 'test',
                'image' => 'http://fakestoreapi.com/img/71-3HjGNDUL._AC_SY879._SX._UX._SY._UY_.jpg',
                'rating' => [
                    'rate' => -1,
                    'count' => -1,
                ],
            ],
            [
                'The price field must have 0-2 decimal places.',
                'The rating.rate field must be greater than or equal to 0.',
                'The rating.count field must be greater than or equal to 0.',
            ],
        ];

        yield 'Product with bad rating' => [
            [
                'id' => 1,
                'title' => 'test',
                'price' => 13.37,
                'description' => 'This is a description !',
                'category' => 'test',
                'image' => 'https://proxify.io',
                'rating' => [
                    'rate' => 6,
                    'count' => 4.2,
                ],
            ],
            [
                'The rating.rate field must be less than or equal to 5.',
                'The rating.count field must have 0-0 decimal places.',
            ],
        ];
    }

    /**
     * @dataProvider productValidationNoExceptionProvider
     */
    public function test_product_validation_no_exception(array $product): void
    {
        $productDTO = ProductDTO::fromFakeAPI($product);
        $this->assertSame([
            'id' => null,
            'title' => $product['title'],
            'price' => $product['price'],
            'description' => $product['description'],
            'categoryName' => $product['category'],
            'image' => $product['image'],
            'rating' => $product['rating'],
            'fakeStoreId' => $product['id'],
        ], $productDTO->toArray());
    }

    public static function productValidationNoExceptionProvider(): \Generator
    {
        yield 'Valid product with edge cases' => [
            [
                'id' => 1,
                'title' => 'test',
                'price' => 0.01,
                'description' => 'This is a description !',
                'category' => 'test',
                'image' => 'https://proxify.io',
                'rating' => [
                    'rate' => 0,
                    'count' => 0,
                ],
            ],
        ];

        yield 'valide product' => [
            [
                'id' => 1_000,
                'title' => '5357 test in 1337 !',
                'price' => 0.01,
                'description' => 'This is a description !',
                'category' => 'test',
                'image' => 'https://proxify.io',
                'rating' => [
                    'rate' => 5,
                    'count' => 9_000,
                ],
            ],
        ];
    }
}
