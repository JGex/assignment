<?php

namespace Tests\Unit;

use App\DTO\CategoryDTO;
use App\DTO\Exceptions\CategoryValidationException;
use Tests\TestCase;

class CategoryValidationTest extends TestCase
{
    /**
     * @dataProvider categoryValidationExceptionProvider
     */
    public function test_category_validation_exception(?string $categoryName, array $expected): void
    {
        $this->expectException(CategoryValidationException::class);
        $this->expectExceptionObject(new CategoryValidationException($categoryName ?? '', $expected));

        CategoryDTO::fromFakeAPI($categoryName);
    }

    public static function categoryValidationExceptionProvider(): \Generator
    {
        yield 'Product with empty name' => [
            '',
            [
                'Category name is missing',
            ],
        ];

        yield 'Product with null name' => [
            null,
            [
                'Category name is missing',
            ],
        ];
    }

    /**
     * @dataProvider categoryValidationNoExceptionProvider
     */
    public function test_category_validation_no_exception(?string $categoryName): void
    {
        $categoryDTO = CategoryDTO::fromFakeAPI($categoryName);
        $this->assertSame($categoryDTO->toArray(), ['name' => $categoryName]);
    }

    public static function categoryValidationNoExceptionProvider(): \Generator
    {
        yield 'Product with string name' => [
            'Test name',
        ];

        yield 'Product with numeric name' => [
            '2143',
        ];

        yield 'Product with mixed name' => [
            'test 123 test',
        ];
    }
}
