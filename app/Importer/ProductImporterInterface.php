<?php

namespace App\Importer;

use App\DTO\CategoryDTO;
use App\DTO\Exceptions\CategoryValidationException;
use App\DTO\Exceptions\ProductValidationException;
use App\DTO\ProductDTO;
use App\Importer\Exception\ProductImporterException;
use Illuminate\Support\Collection;

interface ProductImporterInterface
{
    public function getSourceName(): string;

    /**
     * Use to retrieve a Collection of Products parsed and validated
     *
     * @return Collection<ProductDTO>
     *
     * @throws ProductImporterException
     * @throws ProductValidationException
     */
    public function getProducts(): Collection;

    /**
     * Use to retrieve a Collection of Categories parsed and validated
     *
     * @return Collection<CategoryDTO>
     *
     * @throws ProductImporterException
     * @throws CategoryValidationException
     */
    public function getCategories(): Collection;
}
