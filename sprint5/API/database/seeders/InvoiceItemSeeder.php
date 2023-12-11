<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('invoice_items')->insert([[
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2019000001')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Bolt Cutters')->first()->id,
            'unit_price' => '48.41',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2019000001')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Combination Pliers')->first()->id,
            'unit_price' => '14.15',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2019000001')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Claw Hammer')->first()->id,
            'unit_price' => '11.48',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2020000001')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Adjustable Wrench')->first()->id,
            'unit_price' => '20.33',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2020000001')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Angled Spanner')->first()->id,
            'unit_price' => '14.14',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2020000002')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Mini Screwdriver')->first()->id,
            'unit_price' => '13.96',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2020000002')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Wood Saw')->first()->id,
            'unit_price' => '12.18',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2021000001')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Cordless Drill 18V')->first()->id,
            'unit_price' => '119.24',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2021000002')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Circular Saw')->first()->id,
            'unit_price' => '80.19',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2021000002')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Sledgehammer')->first()->id,
            'unit_price' => '17.75',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2021000002')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Slip Joint Pliers')->first()->id,
            'unit_price' => '9.17',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2023000001')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Claw Hammer with Fiberglass Handle')->first()->id,
            'unit_price' => '20.14',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2023000002')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Combination Pliers')->first()->id,
            'unit_price' => '14.15',
            'quantity' => 1
        ], [
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2023000002')->first()->id,
            'product_id' => DB::table('products')->where('name', '=', 'Open-end Spanners (Set)')->first()->id,
            'unit_price' => '38.51',
            'quantity' => 1
        ]]);
    }

}
