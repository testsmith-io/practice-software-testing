<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('favorites')->insert([[
            'id' => Str::ulid()->toBase32(),
            'user_id' => DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Combination Pliers')->first()->id
        ], [
            'id' => Str::ulid()->toBase32(),
            'user_id' => DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Slip Joint Pliers')->first()->id
        ],[
            'id' => Str::ulid()->toBase32(),
            'user_id' => DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Belt Sander')->first()->id
        ]]);
    }

}
