<?php

namespace App\Models\Repository;

use App\DTO\ProductDTO;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

class ProductRepository
{
    /**
     * @param Collection<ProductDTO> $products
     *
     * @throws \JsonException
     */
    public function createFromCollection(Collection $products): void
    {
        Product::upsert(
            [
                ...$products
                    ->map(fn (ProductDTO $product) => $this->transformFromDTO($product))
                    ->toArray(),
            ],
            ['fakeStoreId'],
            [
                'title',
                'price',
                'description',
                'category_id',
                'image',
                'rating',
            ]
        );
    }

    /**
     * @throws \JsonException
     */
    private function transformFromDTO(ProductDTO $product): array
    {
        return [
            'id' => $product->id,
            'title' => $product->title,
            'price' => $product->price,
            'description' => $product->description,
            'category_id' => Category::where('name', '=', $product->categoryName)->firstOrFail()->id,
            'image' => $product->image,
            'rating' => json_encode($product->rating, JSON_THROW_ON_ERROR),
            'fake_store_id' => $product->fakeStoreId,
        ];
    }
}
