<?php

namespace Database\Seeders;

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
        mt_srand(12345); // Fixed seed for reproducibility

        $products = [
            ['name' => 'Combination Pliers', 'price' => 14.15],
            ['name' => 'Pliers', 'price' => 12.01],
            ['name' => 'Bolt Cutters', 'price' => 48.41],
            ['name' => 'Long Nose Pliers', 'price' => 14.24],
            ['name' => 'Slip Joint Pliers', 'price' => 9.17],
            ['name' => 'Claw Hammer with Shock Reduction Grip', 'price' => 13.41],
            ['name' => 'Hammer', 'price' => 12.58],
            ['name' => 'Claw Hammer', 'price' => 11.48],
            ['name' => 'Thor Hammer', 'price' => 11.14],
            ['name' => 'Sledgehammer', 'price' => 17.75],
            ['name' => 'Claw Hammer with Fiberglass Handle', 'price' => 20.14],
            ['name' => 'Court Hammer', 'price' => 18.63],
            ['name' => 'Wood Saw', 'price' => 12.18],
            ['name' => 'Adjustable Wrench', 'price' => 20.33],
            ['name' => 'Angled Spanner', 'price' => 14.14],
            ['name' => 'Open-end Spanners (Set)', 'price' => 38.51],
            ['name' => 'Phillips Screwdriver', 'price' => 4.92],
            ['name' => 'Mini Screwdriver', 'price' => 13.96],
            ['name' => 'Chisels Set', 'price' => 12.96],
            ['name' => 'Wood Carving Chisels', 'price' => 45.23],
            ['name' => 'Swiss Woodcarving Chisels', 'price' => 22.96],
            ['name' => 'Tape Measure 7.5m', 'price' => 7.23],
            ['name' => 'Measuring Tape', 'price' => 10.07],
            ['name' => 'Tape Measure 5m', 'price' => 12.91],
            ['name' => 'Square Ruler', 'price' => 15.75],
            ['name' => 'Safety Goggles', 'price' => 24.26],
            ['name' => 'Safety Helmet Face Shield', 'price' => 35.62],
            ['name' => 'Protective Gloves', 'price' => 21.42],
            ['name' => 'Super-thin Protection Gloves', 'price' => 38.45],
            ['name' => 'Construction Helmet', 'price' => 41.29],
            ['name' => 'Ear Protection', 'price' => 18.58],
            ['name' => 'Screws', 'price' => 6.25],
            ['name' => 'Nuts and bolts', 'price' => 5.55],
            ['name' => 'Cross-head screws', 'price' => 7.99],
            ['name' => 'Flat-Head Wood Screws', 'price' => 3.95],
            ['name' => 'M4 Nuts', 'price' => 4.65],
            ['name' => 'Washers', 'price' => 3.55],
            ['name' => 'Drawer Tool Cabinet', 'price' => 89.55],
            ['name' => 'Tool Cabinet', 'price' => 86.71],
            ['name' => 'Workbench with Drawers', 'price' => 178.20],
            ['name' => 'Wooden Workbench', 'price' => 172.52],
            ['name' => 'Leather toolbelt', 'price' => 61.16],
            ['name' => 'Sheet Sander', 'price' => 58.48],
            ['name' => 'Belt Sander', 'price' => 73.59],
            ['name' => 'Circular Saw', 'price' => 80.19],
            ['name' => 'Random Orbit Sander', 'price' => 100.79],
            ['name' => 'Cordless Drill 20V', 'price' => 125.23],
            ['name' => 'Cordless Drill 24V', 'price' => 66.54],
            ['name' => 'Cordless Drill 18V', 'price' => 119.24],
            ['name' => 'Cordless Drill 12V', 'price' => 46.50]
        ];

        $invoices = DB::table('invoices')->get();

        foreach ($invoices as $invoice) {
            $total = 0;

            // Deterministic random number of items
            $itemsCount = mt_rand(1, 6);
// Shuffle and pick unique products
            $shuffledProducts = $products;
            shuffle($shuffledProducts);
            $selectedProducts = array_slice($shuffledProducts, 0, $itemsCount);

            $invoiceItems = [];

            foreach ($selectedProducts as $product) {
                $quantity = mt_rand(1, 3);
                $lineTotal = $product['price'] * $quantity;

                // Insert into invoice_items
                DB::table('invoice_items')->insert([[
                    'id' => Str::ulid()->toBase32(),
                    'invoice_id' => $invoice->id,
                    'product_id' => DB::table('products')->where('name', '=', $product['name'])->first()->id,
                    'unit_price' => $product['price'],
                    'quantity' => $quantity
                ]]);

                $total += $lineTotal;
            }

            DB::table('invoices')->where('id', $invoice->id)->update(['total' => $total]);
        }
    }
}
