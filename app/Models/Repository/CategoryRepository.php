<?php

namespace App\Models\Repository;

use App\DTO\CategoryDTO;
use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryRepository
{
    public function createFromDTO(CategoryDTO $category): Category
    {
        return Category::firstOrCreate($category->toArray());
    }

    /**
     * @param Collection<CategoryDTO> $categories
     */
    public function createFromCollection(Collection $categories): void
    {
        $categories->map(fn (CategoryDTO $category) => $this->createFromDTO($category));
    }
}
