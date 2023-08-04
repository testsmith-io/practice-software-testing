<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('users')->insert([[
            'id' => Str::ulid()->toBase32(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => 'Test street 123',
            'city' => 'Utrecht',
            'state' => null,
            'country' => 'The Netherlands',
            'postcode' => null,
            'phone' => null,
            'dob' => '1980-01-01',
            'email' => 'admin@practicesoftwaretesting.com',
            'password' => '$2y$10$pvW9Ixi7okDIJC98Vte6e.iAMD6IZAxAR2V.SjW.m1.u5guoq1wxW',
            'role' => 'admin'
        ],
            [
                'id' => Str::ulid()->toBase32(),
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'address' => 'Test street 98',
                'city' => 'Vienna',
                'state' => null,
                'country' => 'Austria',
                'postcode' => null,
                'phone' => null,
                'dob' => '1980-02-02',
                'email' => 'customer@practicesoftwaretesting.com',
                'password' => '$2y$10$pvW9Ixi7okDIJC98Vte6e.iAMD6IZAxAR2V.SjW.m1.u5guoq1wxW',
                'role' => 'user'
            ],
            [
                'id' => Str::ulid()->toBase32(),
                'first_name' => 'Jack',
                'last_name' => 'Howe',
                'address' => 'Test street 654',
                'city' => 'Frankfurt',
                'state' => null,
                'country' => 'Germany',
                'postcode' => null,
                'phone' => null,
                'dob' => '1980-03-03',
                'email' => 'customer2@practicesoftwaretesting.com',
                'password' => '$2y$10$pvW9Ixi7okDIJC98Vte6e.iAMD6IZAxAR2V.SjW.m1.u5guoq1wxW',
                'role' => 'user'
            ]]);
    }
}
