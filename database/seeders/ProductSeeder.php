<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Category::factory(5)->create();

        Product::factory(20)
            ->make()->each(function (Product $product) {
                $product->category()->associate(Category::inRandomOrder()->first());
                $product->save();
            });
    }
}
