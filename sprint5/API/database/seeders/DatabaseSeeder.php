<?php

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
            FavoriteSeeder::class,
            InvoiceSeeder::class,
            InvoiceItemSeeder::class,
            PaymentSeeder::class
        ]);
    }
}
