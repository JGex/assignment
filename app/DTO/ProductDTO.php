<?php

namespace App\DTO;

use App\DTO\Exceptions\ProductValidationException;
use Illuminate\Support\Facades\Validator;

class ProductDTO
{
    private const array VALIDATION_RULES = [
        'id' => 'numeric|nullable',
        'title' => 'required|string',
        'price' => 'required|decimal:0,2|gt:0',
        'description' => 'required|string',
        'categoryName' => 'required|string',
        'image' => 'required|active_URL',
        'rating' => 'array',
        'rating.rate' => 'required|decimal:0,1|gte:0|lte:5',
        'rating.count' => 'required|decimal:0,0|gte:0',
        'fakeStoreId' => 'numeric|nullable',
    ];
    public ?int $id;
    public ?string $title;
    public ?float $price;
    public ?string $description;
    public ?string $categoryName;
    public ?string $image;
    public ?array $rating;
    public ?int $fakeStoreId;

    public function __construct(array $product)
    {
        $this->id = $product['id'] ?? null;
        $this->title = $product['title'] ?? null;
        $this->price = $product['price'] ?? null;
        $this->description = $product['description'] ?? null;
        $this->categoryName = $product['categoryName'] ?? null;
        $this->image = $product['image'] ?? null;
        $this->rating = $product['rating'] ?? null;
        $this->fakeStoreId = $product['fakeStoreId'] ?? null;
    }

    /**
     * @throws ProductValidationException
     */
    public static function fromFakeAPI(array $product): self
    {
        $productDTO = new self([
            'title' => $product['title'],
            'price' => $product['price'],
            'description' => $product['description'],
            'categoryName' => $product['category'],
            'image' => $product['image'],
            'rating' => $product['rating'],
            'fakeStoreId' => $product['id'],
        ]);

        $validation = Validator::make($productDTO->toArray(), self::VALIDATION_RULES);

        if (!$validation->errors()->isEmpty()) {
            throw new ProductValidationException($product['id'] ?? 0, $validation->errors()->all());
        }

        return $productDTO;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'description' => $this->description,
            'categoryName' => $this->categoryName,
            'image' => $this->image,
            'rating' => $this->rating,
            'fakeStoreId' => $this->fakeStoreId,
        ];
    }
}
