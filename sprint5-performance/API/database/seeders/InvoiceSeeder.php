<?php

namespace Database\Seeders;

use Carbon\Carbon;
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
        mt_srand(12345); // Fixed seed for reproducibility

        $user1 = DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first();
        $user2 = DB::table('users')->where('email', '=', 'customer2@practicesoftwaretesting.com')->first();

        $startDate = Carbon::now()->subYears(5);
        $endDate = Carbon::now(); // Ensures invoices are created up to today
        $dates = [];

        // Generate 170 random invoice dates over the last 5 years, including current year
        for ($i = 1; $i <= 200; $i++) {
            $dates[] = $startDate->copy()->addDays(mt_rand(0, $startDate->diffInDays($endDate)))->toDateTimeString();
        }
        sort($dates);

        $invoiceCounters = [];

        foreach ($dates as $invoiceDate) {
            $year = Carbon::parse($invoiceDate)->year;

            if (!isset($invoiceCounters[$year])) {
                $invoiceCounters[$year] = 1;
            }

            // Generate Invoice Number
            $invoiceNumber = 'INV-' . $year . str_pad($invoiceCounters[$year], 7, '0', STR_PAD_LEFT);
            $invoiceCounters[$year]++;

            // Assign alternating users
            $user = ($invoiceCounters[$year] % 5 == 0) ? $user2 : $user1;

            // Insert Invoice
            DB::table('invoices')->insert([[
                'id' => Str::ulid()->toBase32(),
                'user_id' => $user->id,
                'invoice_date' => $invoiceDate,
                'invoice_number' => $invoiceNumber,
                'billing_street' => 'Test street ' . ($invoiceCounters[$year] % 100),
                'billing_city' => 'Utrecht',
                'billing_state' => 'Utrecht',
                'billing_country' => 'The Netherlands',
                'billing_postal_code' => '1122AB',
                'total' => 0,
                'status' => 'AWAITING_FULFILLMENT'
            ]]);
        }
    }
}
