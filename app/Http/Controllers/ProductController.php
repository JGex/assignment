<?php

namespace App\Http\Controllers;

use App\DTO\ProductDTO;
use App\Http\Exception\ApiBodyValidationException;
use App\Http\Exception\ApiParametersValidationException;
use App\Models\Product;
use App\Models\Repository\ProductRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    public function index(): Collection
    {
        return Product::all();
    }

    /**
     * @throws ApiParametersValidationException
     * @throws ModelNotFoundException<Model>
     */
    public function show($product): array
    {
        $this->validateIdParameters();

        return ProductDTO::fromModel(
            Product::findOrFail($product)
        )->toArray();
    }

    /**
     * @throws ApiParametersValidationException
     * @throws ApiBodyValidationException
     * @throws ModelNotFoundException<Model>
     * @throws \JsonException
     */
    public function update(Request $request, $product): Response
    {
        $this->validateIdParameters();

        $this->productRepository->updateFromAPI($product, $this->validateContent($request));

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @throws ApiParametersValidationException
     */
    private function validateIdParameters(): void
    {
        $validation = Validator::make(
            request()?->route()?->parameters(),
            ['product' => 'required|integer'],
            ['product' => 'Bad formatted id, it must be a number']
        );

        if ($validation->fails()) {
            ApiParametersValidationException::productParametersValidationException($validation->errors()->all());
        }
    }

    /**
     * @throws ApiBodyValidationException
     * @throws \JsonException
     */
    private function validateContent(Request $request): array
    {
        $content = json_decode(
            json: $request->getContent(),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );

        $validation = Validator::make(
            request()?->route()?->parameters(),
            [
                'title' => 'string',
                'price' => 'decimal:0,2|gt:0',
                'description' => 'string',
                'image' => 'active_URL',
            ],
        );

        if ($validation->fails()) {
            ApiBodyValidationException::productValidationException($validation->errors()->all());
        }

        return $content;
    }
}
