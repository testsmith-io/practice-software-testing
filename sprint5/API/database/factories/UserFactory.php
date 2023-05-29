<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->name,
            'country' => $this->faker->country,
            'postcode' => $this->faker->postcode,
            'phone' => $this->faker->phoneNumber,
            'dob' => '2000-01-01',
            'email' => $this->faker->email,
            'password' => '$2y$10$2BcSndh1CE29QpWRUer7Bu15OHNzb3qM2D8sRSJ6P1u3kDa7H2bkK',
            'role' => 'user'
        ];
    }
}
