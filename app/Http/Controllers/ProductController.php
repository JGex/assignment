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
use OpenApi\Attributes as OA;

#[
    OA\Info(version: "1.0.0", description: "Product API", title: "product-api Documentation"),
    OA\Server(url: 'http://localhost', description: "local server"),
]
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    #[OA\Get(
        path: '/api/product',
        summary: 'Get a list of products',
        responses: [
            new OA\Response(
                response:200,
                description: "Successful operation"
            ),
        ]
    )]
    public function index(): Collection
    {
        return Product::all();
    }

    #[OA\Get(
        path: '/api/product/{id}',
        summary: 'Get a product',
        parameters:[ new OA\Parameter(
            name: "id",
            description: "id of the product",
            in: 'path',
            required: true,
        )],
        responses: [
            new OA\Response(
                response:200,
                description: "Successful operation"
            ),
            new OA\Response(
                response:404,
                description: "Product not found"
            ),
        ]
    )]
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

    #[OA\Put(
        path: '/api/product/{id}',
        summary: 'Update a product',
        requestBody: new OA\RequestBody(required: true,
            content: new OA\MediaType(mediaType: "application/json",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'title', description: "Product title", type: "string"),
                        new OA\Property(property: 'price', description: "Product description", type: "float"),
                        new OA\Property(property: 'description', description: "Product price", type: "string"),
                        new OA\Property(property: 'image', description: "image of the product", type: "string")
                    ]
                )
            )
        ),
        parameters:[ new OA\Parameter(
            name: "id",
            description: "id of the product",
            in: 'path',
            required: true,
        )],
        responses: [
            new OA\Response(
                response:201,
                description: "Product successfully updated"
            ),
            new OA\Response(
                response:404,
                description: "Product not found"
            ),
            new OA\Response(
                response:500,
                description: "When an error occure"
            ),
        ]
    )]
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
            $content,
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
