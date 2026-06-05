<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BrandSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductImageSeeder::class,
            ProductSeeder::class,
            ProductSpecSeeder::class,
            FavoriteSeeder::class,
            InvoiceSeeder::class,
            InvoiceItemSeeder::class,
            PaymentSeeder::class
        ]);
    }
}
