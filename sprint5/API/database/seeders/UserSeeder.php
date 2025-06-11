<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([[
            'id' => Str::ulid()->toBase32(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'street' => 'Test street 123',
            'city' => 'Utrecht',
            'state' => null,
            'country' => 'The Netherlands',
            'postal_code' => null,
            'phone' => null,
            'dob' => '1980-01-01',
            'email' => 'admin@practicesoftwaretesting.com',
            'password' => app('hash')->make('welcome01'),
            'role' => 'admin'
        ],
            [
                'id' => Str::ulid()->toBase32(),
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'street' => 'Test street 98',
                'city' => 'Vienna',
                'state' => null,
                'country' => 'Austria',
                'postal_code' => null,
                'phone' => null,
                'dob' => '1980-02-02',
                'email' => 'customer@practicesoftwaretesting.com',
                'password' => app('hash')->make('welcome01'),
                'role' => 'user'
            ],
            [
                'id' => Str::ulid()->toBase32(),
                'first_name' => 'Jack',
                'last_name' => 'Howe',
                'street' => 'Test street 654',
                'city' => 'Frankfurt',
                'state' => null,
                'country' => 'Germany',
                'postal_code' => null,
                'phone' => null,
                'dob' => '1980-03-03',
                'email' => 'customer2@practicesoftwaretesting.com',
                'password' => app('hash')->make('welcome01'),
                'role' => 'user'
            ],
            [
                'id' => Str::ulid()->toBase32(),
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'street' => 'Test street 200',
                'city' => 'London',
                'state' => null,
                'country' => 'United Kingdom',
                'postal_code' => null,
                'phone' => null,
                'dob' => '1985-05-05',
                'email' => 'customer3@practicesoftwaretesting.com',
                'password' => app('hash')->make('pass123'),
                'role' => 'user'
            ]]);
    }
}
