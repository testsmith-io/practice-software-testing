<?php

namespace database\factories;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductImage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'by_name' => $this->faker->name,
            'by_url' => $this->faker->url,
            'source_name' => $this->faker->name,
            'source_url' => $this->faker->url,
            'file_name' => $this->faker->name,
            'title' => $this->faker->name
        ];
    }
}
