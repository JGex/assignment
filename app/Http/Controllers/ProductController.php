<?php

namespace App\Http\Controllers;

use App\DTO\ProductDTO;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Product;
use App\Models\Repository\ProductRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[
    OA\Info(version: "1.0.0", description: "Product API", title: "product-api Documentation"),
    OA\Server(url: 'http://localhost', description: "local server"),
    OA\SecurityScheme(
        securityScheme: "bearerAuth",
        type: "http",
        scheme: "bearer"
    )
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
        security: [
            ['bearerAuth' => []],
        ],
        responses: [
            new OA\Response(
                response: 200,
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
        security: [
            ['bearerAuth' => []],
        ],
        parameters: [new OA\Parameter(
            name: "id",
            description: "id of the product",
            in: 'path',
            required: true,
        )],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation"
            ),
            new OA\Response(
                response: 404,
                description: "Product not found"
            ),
        ]
    )]
    /**
     * @throws ModelNotFoundException<Model>
     */
    public function show($product): array
    {
        return ProductDTO::fromModel(
            Product::findOrFail($product)
        )->toArray();
    }

    #[OA\Put(
        path: '/api/product/{id}',
        summary: 'Update a product',
        security: [
            ['bearerAuth' => []],
        ],
        requestBody: new OA\RequestBody(required: true,
            content: new OA\MediaType(mediaType: "x-www-form-urlencoded",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'title', description: "Product title", type: "string"),
                        new OA\Property(property: 'price', description: "Product description", type: "float"),
                        new OA\Property(property: 'description', description: "Product price", type: "string"),
                        new OA\Property(property: 'image', description: "image of the product", type: "string"),
                    ]
                )
            )
        ),
        parameters: [new OA\Parameter(
            name: "id",
            description: "id of the product",
            in: 'path',
            required: true,
        )],
        responses: [
            new OA\Response(
                response: 204,
                description: "Product successfully updated"
            ),
            new OA\Response(
                response: 404,
                description: "Product not found"
            ),
            new OA\Response(
                response: 500,
                description: "When an error occure"
            ),
        ]
    )]
    /**
     * @throws ModelNotFoundException<Model>
     * @throws \JsonException
     */
    public function update(ProductUpdateRequest $request, $product): Response
    {
        $validated = $request->validated();
        $this->productRepository->updateFromAPI($product, $validated);

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
