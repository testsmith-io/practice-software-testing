<?php

namespace database\seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payment_details_id_1 = Str::ulid()->toBase32();
        $payment_details_id_2 = Str::ulid()->toBase32();
        $payment_details_id_3 = Str::ulid()->toBase32();
        $payment_details_id_4 = Str::ulid()->toBase32();
        $payment_details_id_5 = Str::ulid()->toBase32();
        $payment_details_id_6 = Str::ulid()->toBase32();
        $payment_details_id_7 = Str::ulid()->toBase32();

        DB::table('payment_bank_transfer_details')->insert([[
            'id' => $payment_details_id_1,
            'bank_name' => 'My Bank',
            'account_name'=> 'John Doe',
            'account_number' => '987654321'
        ],[
            'id' => $payment_details_id_2,
            'bank_name' => 'New Bank',
            'account_name'=> 'John Doe',
            'account_number' => '345678'
        ]]);

        DB::table('payment_credit_card_details')->insert([[
            'id' => $payment_details_id_3,
            'credit_card_number' => '0001-0002-0003-0004',
            'expiration_date'=> '02/2024',
            'cvv' => '045',
            'card_holder_name' => 'John Doe'
        ],[
            'id' => $payment_details_id_4,
            'credit_card_number' => '1000-2000-3000-4000',
            'expiration_date'=> '05/2025',
            'cvv' => '031',
            'card_holder_name' => 'John Doe'
        ]]);

        DB::table('payment_gift_card_details')->insert([[
            'id' => $payment_details_id_5,
            'gift_card_number' => '23ded3d',
            'validation_code'=> 'ed3d'
        ]]);

        DB::table('payment_cash_on_delivery_details')->insert([[
            'id' => $payment_details_id_6
        ]]);

        DB::table('payment_bnpl_details')->insert([[
            'id' => $payment_details_id_7,
            'monthly_installments' => '6'
        ]]);

        DB::table('payments')->insert([[
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2019000001')->first()->id,
            'payment_method'=> 'Bank Transfer',
            'payment_details_id' => $payment_details_id_1,
            'payment_details_type' => 'App\\Models\\PaymentBankTransferDetails'
        ],[
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2020000001')->first()->id,
            'payment_method'=> 'Credit Card',
            'payment_details_id' => $payment_details_id_3,
            'payment_details_type' => 'App\\Models\\PaymentCreditCardDetails'
        ],[
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2020000002')->first()->id,
            'payment_method'=> 'Cash on Delivery',
            'payment_details_id' => $payment_details_id_6,
            'payment_details_type' => 'App\\Models\\PaymentCashOnDeliveryDetails'
        ],[
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2021000001')->first()->id,
            'payment_method'=> 'Buy Now Pay Later',
            'payment_details_id' => $payment_details_id_7,
            'payment_details_type' => 'App\\Models\\PaymentBnplDetails'
        ],[
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2021000002')->first()->id,
            'payment_method'=> 'Gift Card',
            'payment_details_id' => $payment_details_id_5,
            'payment_details_type' => 'App\\Models\\PaymentGiftCardDetails'
        ],[
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2023000001')->first()->id,
            'payment_method'=> 'Bank Transfer',
            'payment_details_id' => $payment_details_id_2,
            'payment_details_type' => 'App\\Models\\PaymentBankTransferDetails'
        ],[
            'id' => Str::ulid()->toBase32(),
            'invoice_id' => DB::table('invoices')->where('invoice_number', '=', 'INV-2023000002')->first()->id,
            'payment_method'=> 'Credit Card',
            'payment_details_id' => $payment_details_id_4,
            'payment_details_type' => 'App\\Models\\PaymentCreditCardDetails'
        ]]);

    }

}
