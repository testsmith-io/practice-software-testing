<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        mt_srand(12345); // Fixed seed for reproducibility

        $user1 = DB::table('users')->where('email', '=', 'customer@practicesoftwaretesting.com')->first();
        $user2 = DB::table('users')->where('email', '=', 'customer2@practicesoftwaretesting.com')->first();

        $startDate = Carbon::now()->subYears(5);
        $dates = [];

        for ($i = 1; $i <= 150; $i++) {
            $dates[] = $startDate->copy()->addDays(mt_rand(1, 1800))->toDateTimeString();
        }
        sort($dates);

        $invoiceCounters = [];

        foreach ($dates as $invoiceDate) {
            $year = Carbon::parse($invoiceDate)->year;

            if (!isset($invoiceCounters[$year])) {
                $invoiceCounters[$year] = 1;
            }

            $invoiceNumber = 'INV-' . $year . str_pad($invoiceCounters[$year], 7, '0', STR_PAD_LEFT);
            $invoiceCounters[$year]++;

            $user = ($invoiceCounters[$year] % 5 == 0) ? $user2 : $user1;

            DB::table('invoices')->insert([[
                'id' => Str::ulid()->toBase32(),
                'user_id' => $user->id,
                'invoice_date' => $invoiceDate,
                'invoice_number' => $invoiceNumber,
                'billing_address' => 'Test street ' . ($invoiceCounters[$year] % 100),
                'billing_city' => 'Utrecht',
                'billing_state' => 'Utrecht',
                'billing_country' => 'The Netherlands',
                'billing_postcode' => '1122AB',
                'total' => 0,
                'status' => 'AWAITING_FULFILLMENT'
            ]]);
        }
    }
}
