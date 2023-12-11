<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('invoices')->insert([[
            'id' => Str::ulid()->toBase32(),
            'user_id' => DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first()->id,
            'invoice_date' => '2019-04-05 22:04:47',
            'invoice_number' => 'INV-2019000001',
            'billing_address' => 'Test street 123',
            'billing_city' => 'Utrecht',
            'billing_state' => 'Utrecht',
            'billing_country' => 'The Netherlands',
            'billing_postcode' => '1122AB',
            'total' => '74.04'
        ], [
            'id' => Str::ulid()->toBase32(),
            'user_id' => DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first()->id,
            'invoice_date' => '2020-07-16 22:04:47',
            'invoice_number' => 'INV-2020000001',
            'billing_address' => 'Test street 123',
            'billing_city' => 'Utrecht',
            'billing_state' => 'Utrecht',
            'billing_country' => 'The Netherlands',
            'billing_postcode' => '1122AB',
            'total' => '34.47'
        ], [
            'id' => Str::ulid()->toBase32(),
            'user_id' => DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first()->id,
            'invoice_date' => '2020-03-20 22:04:47',
            'invoice_number' => 'INV-2020000002',
            'billing_address' => 'Test street 123',
            'billing_city' => 'Utrecht',
            'billing_state' => 'Utrecht',
            'billing_country' => 'The Netherlands',
            'billing_postcode' => '1122AB',
            'total' => '26.14'
        ], [
            'id' => Str::ulid()->toBase32(),
            'user_id' => DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first()->id,
            'invoice_date' => '2021-09-25 22:04:47',
            'invoice_number' => 'INV-2021000001',
            'billing_address' => 'Test street 123',
            'billing_city' => 'Brussels',
            'billing_state' => 'Brussels',
            'billing_country' => 'Belgium',
            'billing_postcode' => '1122AB',
            'total' => '119.24'
        ], [
            'id' => Str::ulid()->toBase32(),
            'user_id' => DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first()->id,
            'invoice_date' => '2021-10-30 22:04:47',
            'invoice_number' => 'INV-2021000002',
            'billing_address' => 'Test street 123',
            'billing_city' => 'Brussels',
            'billing_state' => 'Brussels',
            'billing_country' => 'Belgium',
            'billing_postcode' => '1122AB',
            'total' => '107.11'
        ], [
            'id' => Str::ulid()->toBase32(),
            'user_id' => DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first()->id,
            'invoice_date' => date('Y-m-d H:i:s', strtotime('-3 day', time())),
            'invoice_number' => 'INV-2023000001',
            'billing_address' => 'Test street 123',
            'billing_city' => 'Utrecht',
            'billing_state' => 'Utrecht',
            'billing_country' => 'The Netherlands',
            'billing_postcode' => '1122AB',
            'total' => '20.14'
        ], [
            'id' => Str::ulid()->toBase32(),
            'user_id' => DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first()->id,
            'invoice_date' => date('Y-m-d H:i:s', strtotime('-1 day', time())),
            'invoice_number' => 'INV-2023000002',
            'billing_address' => 'Test street 123',
            'billing_city' => 'Utrecht',
            'billing_state' => 'Utrecht',
            'billing_country' => 'The Netherlands',
            'billing_postcode' => '1122AB',
            'total' => '52.66'
        ]]);
    }

}
