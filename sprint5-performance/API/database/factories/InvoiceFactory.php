<?php

namespace database\factories;

use App\Models\Invoice;
use App\Models\Invoiceline;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(), // Assuming User model has its own factory
            'invoice_date' => now(),
            'invoice_number' => Str::random(10),
            'additional_discount_percentage' => $this->faker->randomFloat(2, 0, 100),
            'additional_discount_amount' => $this->faker->randomFloat(2, 0, 1000),
            'billing_street' => $this->faker->streetAddress(),
            'billing_city' => $this->faker->city(),
            'billing_state' => $this->faker->state(),
            'billing_country' => $this->faker->country(),
            'billing_postal_code' => $this->faker->postcode(),
            'subtotal' => $this->faker->randomFloat(2, 100, 1000),
            'total' => $this->faker->randomFloat(2, 100, 1000),
            'status' => 'COMPLETED'
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Invoice $invoice) {
            $numberOfLines = random_int(1, 5);
            for ($i = 0; $i < $numberOfLines; $i++) {
                Invoiceline::factory()->create([
                    'invoice_id' => $invoice->id
                ]);
            }
        });
    }
}
