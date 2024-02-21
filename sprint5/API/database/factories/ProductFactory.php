<?php

namespace database\factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->text,
            'brand_id' => Brand::factory(),
            'category_id' => Category::factory(),
            'price' => $this->faker->numberBetween(0,9),
            'is_location_offer' => false,
            'is_rental' => false,
            'product_image_id' => ProductImage::factory()
        ];
    }

}
