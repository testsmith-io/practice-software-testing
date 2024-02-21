<?php

namespace database\factories;

use App\Models\Invoiceline;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoicelineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoiceline::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'unit_price' => 2,
            'quantity' => 1,
            'discount_percentage' => $this->faker->randomFloat(2, 0, 30), // Example discount percentage between 0 and 30
            'discounted_price' => function (array $attributes) {
                return $attributes['unit_price'] * (1 - ($attributes['discount_percentage'] / 100));
            }
        ];
    }
}
